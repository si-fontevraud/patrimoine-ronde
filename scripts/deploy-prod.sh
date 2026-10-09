#!/usr/bin/env bash

set -Eeuo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "${SCRIPT_DIR}/.." && pwd)"

ENV_FILE=".env.docker.prod"
REF="main"
RUN_MIGRATIONS=1
RUN_BACKUP=1
RESTORE_DB=0
REF_EXPLICIT=0
STATE_DIR="${PROJECT_ROOT}/var/deploy-prod"
BACKUP_DIR="${STATE_DIR}/backups"
ROLLBACK_STATE_FILE="${STATE_DIR}/rollback.env"
DEPLOY_STATE_FILE="${STATE_DIR}/deploy.env"

usage() {
    cat <<'EOF'
Usage:
  scripts/deploy-prod.sh install [--env-file FILE] [--ref REF] [--no-migrate]
  scripts/deploy-prod.sh upgrade [--env-file FILE] [--ref REF] [--no-migrate] [--no-backup]
  scripts/deploy-prod.sh rollback [--env-file FILE] [--ref REF] [--no-migrate] [--restore-db]

Commands:
  install   Build and start the production stack from the selected Git ref.
  upgrade   Fetch the selected Git ref, back up the database, then rebuild/restart.
  rollback  Redeploy the previous commit (or --ref) and optionally restore the latest backup.

Options:
  --env-file FILE  Docker Compose env file to use (default: .env.docker.prod)
  --ref REF        Git branch or tag to deploy (default: main)
  --no-migrate     Skip doctrine migrations
  --no-backup      Skip the automatic database backup before upgrade
  --restore-db     During rollback, restore the last backup captured by upgrade
  -h, --help       Show this help

Examples:
  scripts/deploy-prod.sh install
  scripts/deploy-prod.sh upgrade --ref main
  scripts/deploy-prod.sh upgrade --env-file .env.docker.prod --ref v1.2.3
  scripts/deploy-prod.sh rollback
  scripts/deploy-prod.sh rollback --restore-db
EOF
}

log() {
    printf '\n[%s] %s\n' "$(date '+%Y-%m-%d %H:%M:%S')" "$*" >&2
}

require_command() {
    if ! command -v "$1" >/dev/null 2>&1; then
        printf 'Error: required command not found: %s\n' "$1" >&2
        exit 1
    fi
}

compose() {
    docker compose --env-file "${ENV_FILE}" -f compose.yaml -f compose.prod.yaml "$@"
}

ensure_prerequisites() {
    require_command git
    require_command docker

    if [[ ! -f "${PROJECT_ROOT}/${ENV_FILE}" ]]; then
        printf 'Error: env file not found: %s/%s\n' "${PROJECT_ROOT}" "${ENV_FILE}" >&2
        printf 'Copy .env.docker.prod.example to %s and fill in the production secrets first.\n' "${ENV_FILE}" >&2
        exit 1
    fi

    mkdir -p "${BACKUP_DIR}"
}

validate_compose_config() {
    log "Validating Docker Compose production configuration"
    compose config >/dev/null
}

save_deploy_state() {
    local deployed_ref="$1"
    local deployed_commit="$2"

    cat > "${DEPLOY_STATE_FILE}" <<EOF
DEPLOYED_AT=$(date -Is)
DEPLOYED_REF=${deployed_ref}
DEPLOYED_COMMIT=${deployed_commit}
ENV_FILE=${ENV_FILE}
EOF
}

save_rollback_state() {
    local previous_commit="$1"
    local backup_file="$2"

    cat > "${ROLLBACK_STATE_FILE}" <<EOF
SAVED_AT=$(date -Is)
PREVIOUS_COMMIT=${previous_commit}
BACKUP_FILE=${backup_file}
EOF
}

load_rollback_state() {
    if [[ ! -f "${ROLLBACK_STATE_FILE}" ]]; then
        printf 'Error: rollback state file not found: %s\n' "${ROLLBACK_STATE_FILE}" >&2
        printf 'Run an upgrade first or specify --ref explicitly for rollback.\n' >&2
        exit 1
    fi

    # shellcheck disable=SC1090
    source "${ROLLBACK_STATE_FILE}"
}

checkout_ref() {
    local ref="$1"

    log "Fetching Git references"
    git fetch --all --prune

    if git show-ref --verify --quiet "refs/tags/${ref}"; then
        log "Checking out tag ${ref}"
        git checkout --detach "${ref}"
        return
    fi

    log "Checking out branch ${ref}"
    git checkout "${ref}"

    if git ls-remote --exit-code --heads origin "${ref}" >/dev/null 2>&1; then
        log "Fast-forwarding branch ${ref}"
        git pull --ff-only origin "${ref}"
    else
        log "No matching branch on origin for ${ref}; using local branch state"
    fi
}

start_database() {
    log "Starting database"
    compose up -d database
}

backup_database() {
    local timestamp backup_file

    timestamp="$(date '+%Y%m%d-%H%M%S')"
    backup_file="${BACKUP_DIR}/prod-db-${timestamp}.sql.gz"

    log "Creating PostgreSQL backup at ${backup_file}"
    compose exec -T database sh -lc 'pg_dump -U "$POSTGRES_USER" -d "$POSTGRES_DB"' | gzip -c > "${backup_file}"

    printf '%s\n' "${backup_file}"
}

build_images() {
    log "Building production images"
    compose build app messenger nginx
}

restart_application() {
    log "Recreating production application containers"
    compose up -d --force-recreate app messenger nginx
}

run_migrations() {
    if [[ "${RUN_MIGRATIONS}" -ne 1 ]]; then
        log "Skipping Doctrine migrations"
        return
    fi

    log "Running Doctrine migrations"
    compose exec -T app php bin/console doctrine:migrations:migrate --env=prod --no-interaction
}

restore_database_backup() {
    local backup_file="$1"

    if [[ ! -f "${backup_file}" ]]; then
        printf 'Error: backup file not found: %s\n' "${backup_file}" >&2
        exit 1
    fi

    log "Restoring PostgreSQL backup from ${backup_file}"
    compose exec -T database sh -lc 'psql -U "$POSTGRES_USER" -d postgres -c "SELECT pg_terminate_backend(pid) FROM pg_stat_activity WHERE datname = '\''$POSTGRES_DB'\'' AND pid <> pg_backend_pid();" >/dev/null'
    compose exec -T database sh -lc 'dropdb -U "$POSTGRES_USER" --if-exists "$POSTGRES_DB"'
    compose exec -T database sh -lc 'createdb -U "$POSTGRES_USER" "$POSTGRES_DB"'
    gunzip -c "${backup_file}" | compose exec -T database sh -lc 'psql -U "$POSTGRES_USER" -d "$POSTGRES_DB"'
}

show_status() {
    log "Current container status"
    compose ps
}

run_install_or_upgrade() {
    local command="$1"
    local previous_commit=""
    local backup_file=""

    if [[ "${command}" == "upgrade" ]]; then
        previous_commit="$(git rev-parse HEAD)"
    fi

    start_database

    if [[ "${command}" == "upgrade" && "${RUN_BACKUP}" -eq 1 ]]; then
        backup_file="$(backup_database)"
        save_rollback_state "${previous_commit}" "${backup_file}"
    fi

    checkout_ref "${REF}"
    validate_compose_config
    build_images
    restart_application
    run_migrations
    save_deploy_state "${REF}" "$(git rev-parse HEAD)"
    show_status
}

run_rollback() {
    local target_ref backup_file

    load_rollback_state

    target_ref="${REF}"
    backup_file="${BACKUP_FILE:-}"

    if [[ "${REF_EXPLICIT}" -eq 0 && -n "${PREVIOUS_COMMIT:-}" ]]; then
        target_ref="${PREVIOUS_COMMIT}"
    fi

    start_database
    checkout_ref "${target_ref}"
    validate_compose_config
    build_images
    restart_application

    if [[ "${RESTORE_DB}" -eq 1 ]]; then
        if [[ -z "${backup_file}" ]]; then
            printf 'Error: no backup file recorded in %s\n' "${ROLLBACK_STATE_FILE}" >&2
            exit 1
        fi

        restore_database_backup "${backup_file}"
    fi

    run_migrations
    save_deploy_state "${target_ref}" "$(git rev-parse HEAD)"
    show_status
}

main() {
    if [[ $# -lt 1 ]]; then
        usage
        exit 1
    fi

    if [[ "$1" == "-h" || "$1" == "--help" ]]; then
        usage
        exit 0
    fi

    local command="$1"
    shift

    while [[ $# -gt 0 ]]; do
        case "$1" in
            --env-file)
                [[ $# -ge 2 ]] || { printf 'Error: --env-file requires a value\n' >&2; exit 1; }
                ENV_FILE="$2"
                shift 2
                ;;
            --ref)
                [[ $# -ge 2 ]] || { printf 'Error: --ref requires a value\n' >&2; exit 1; }
                REF="$2"
                REF_EXPLICIT=1
                shift 2
                ;;
            --no-migrate)
                RUN_MIGRATIONS=0
                shift
                ;;
            --no-backup)
                RUN_BACKUP=0
                shift
                ;;
            --restore-db)
                RESTORE_DB=1
                shift
                ;;
            -h|--help)
                usage
                exit 0
                ;;
            *)
                printf 'Error: unknown option or command: %s\n' "$1" >&2
                usage
                exit 1
                ;;
        esac
    done

    case "${command}" in
        install|upgrade|rollback)
            ;;
        *)
            printf 'Error: unknown command: %s\n' "${command}" >&2
            usage
            exit 1
            ;;
    esac

    cd "${PROJECT_ROOT}"
    ensure_prerequisites

    case "${command}" in
        install|upgrade)
            run_install_or_upgrade "${command}"
            ;;
        rollback)
            run_rollback
            ;;
    esac
}

main "$@"
