# Patrimoine Ronde

Application Symfony de suivi des rondes et des incidents terrain pour un site touristique.

## Stack technique

- PHP 8.4+
- Symfony 8.1
- Doctrine ORM
- PostgreSQL
- Twig
- EasyAdmin
- AssetMapper + Turbo
- Docker Compose pour la production

## Fonctionnalités principales

- authentification par formulaire
- création d'incidents avec priorisation
- ajout de photos sur les incidents
- interface d'administration EasyAdmin
- consommation Messenger en arrière-plan

## Démarrage en local

### Prérequis

- PHP 8.4+
- Composer
- PostgreSQL

### Installation

```bash
composer install
cp .env .env.local
```

Configurer ensuite [`.env.local`](./.env.local) avec les vraies valeurs locales, en particulier `DATABASE_URL`.

### Base de données

```bash
php bin/console doctrine:database:create --if-not-exists
php bin/console doctrine:migrations:migrate --no-interaction
```

### Créer un compte administrateur

```bash
php bin/console app:create-admin admin@example.com 'mot-de-passe-admin'
```

### Lancer l'application

Avec Symfony CLI :

```bash
symfony serve -d
```

Ou avec PHP :

```bash
php -S 127.0.0.1:8000 -t public
```

## Tests et vérifications

### Tests

```bash
php bin/phpunit
```

### Vérification du conteneur Symfony

```bash
php bin/console lint:container --no-interaction
```

## Déploiement production

La production s'appuie sur :

- [compose.yaml](./compose.yaml)
- [compose.prod.yaml](./compose.prod.yaml)
- [scripts/deploy-prod.sh](./scripts/deploy-prod.sh)
- [docs/deploiement-prod-debian-13.md](./docs/deploiement-prod-debian-13.md)

### Fichier d'environnement production

Ne pas mettre les secrets dans [`.env`](./.env).

Le fichier [`.env`](./.env) est versionné et contient seulement des valeurs par défaut de développement.  
La configuration production doit être placée dans [`.env.docker.prod`](./.env.docker.prod), créé à partir de [`.env.docker.prod.example`](./.env.docker.prod.example).

```bash
cp .env.docker.prod.example .env.docker.prod
```

### Installation initiale

```bash
scripts/deploy-prod.sh install
```

### Mise à jour

```bash
scripts/deploy-prod.sh upgrade
```

### Rollback

```bash
scripts/deploy-prod.sh rollback
scripts/deploy-prod.sh rollback --restore-db
```

Le script d'upgrade :

- construit les images de production
- recrée les conteneurs
- exécute les migrations
- effectue un backup PostgreSQL avant mise à jour

## Notes importantes

- En production, utiliser explicitement `--env-file .env.docker.prod`.
- [`.env.docker.prod`](./.env.docker.prod) est ignoré par Git et par le contexte de build Docker.
- le logout Symfony est géré par le firewall sur `/logout` avec POST + CSRF
- le dossier servi par Nginx est uniquement [`public/`](./public/)

## Documentation complémentaire

- [Déploiement production sur Debian 13](./docs/deploiement-prod-debian-13.md)
