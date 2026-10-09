# Déploiement production sur Debian 13

Cette documentation décrit les étapes pour installer puis mettre à jour l'application sur une **Debian 13 vierge** en utilisant le script [`scripts/deploy-prod.sh`](../scripts/deploy-prod.sh).

## 1. Pré-requis

### 1.1 Mettre à jour le système

```bash
sudo apt update
sudo apt upgrade -y
sudo apt install -y ca-certificates curl git gnupg
```

### 1.2 Installer Docker Engine et le plugin Compose

```bash
sudo install -m 0755 -d /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/debian/gpg | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg
sudo chmod a+r /etc/apt/keyrings/docker.gpg

echo \
  "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/debian \
  $(. /etc/os-release && echo "$VERSION_CODENAME") stable" | \
  sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

sudo apt update
sudo apt install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
```

### 1.3 Autoriser l'utilisateur courant à utiliser Docker

```bash
sudo usermod -aG docker "$USER"
newgrp docker
```

> Si `newgrp docker` ne suffit pas, se déconnecter/reconnecter au serveur.

### 1.4 Vérifier l'installation

```bash
docker --version
docker compose version
git --version
```

## 2. Récupération du projet

Choisir un emplacement, par exemple `/opt/patrimoine-ronde` :

```bash
sudo mkdir -p /opt
sudo chown "$USER":"$USER" /opt
cd /opt
git clone <URL_DU_REPO_GIT> patrimoine-ronde
cd patrimoine-ronde
```

## 3. Préparer la configuration de production

### 3.1 Créer le fichier d'environnement production

```bash
cp .env.docker.prod.example .env.docker.prod
```

Le fichier [`.env.docker.prod.example`](../.env.docker.prod.example) sert de modèle. Le fichier réel [`.env.docker.prod`](../.env.docker.prod) est ignoré par Git.

> Le fichier [`.env`](../.env) reste versionné par le projet et sert uniquement de base de développement. Ne pas y stocker de secrets ni de réglages spécifiques au serveur de production.

### 3.2 Renseigner les variables obligatoires

Éditer [`.env.docker.prod`](../.env.docker.prod) et adapter au serveur :

```dotenv
APP_ENV=prod
APP_DEBUG=0
APP_SECRET=une-valeur-longue-et-aleatoire

POSTGRES_VERSION=16
POSTGRES_DB=app
POSTGRES_USER=app
POSTGRES_PASSWORD=mot-de-passe-fort

DATABASE_URL=postgresql://app:mot-de-passe-fort@database:5432/app?serverVersion=16&charset=utf8

MAILER_DSN=null://null
MESSENGER_TRANSPORT_DSN=doctrine://default?auto_setup=0
DEFAULT_URI=http://IP_OU_DOMAINE:8081
HTTP_PORT=8081

APP_PREPARE_ON_STARTUP=1
AUTO_MIGRATE=0
```

### 3.3 Points d'attention

- `DATABASE_URL` doit pointer vers `@database:5432` si la base PostgreSQL est celle du `docker compose`.
- `APP_SECRET` doit être unique et non triviale.
- `DEFAULT_URI` doit correspondre à l'URL publique réelle.
- `HTTP_PORT` expose Nginx sur l'hôte. Par défaut : `8081`.

## 4. Installation initiale

Depuis la racine du projet :

```bash
cd /opt/patrimoine-ronde
scripts/deploy-prod.sh install
```

### Ce que fait cette commande

- valide la présence de [`.env.docker.prod`](../.env.docker.prod)
- vérifie `git` et `docker`
- récupère le code Git demandé
- valide la configuration Docker Compose de production
- démarre PostgreSQL
- build les images `app`, `messenger` et `nginx`
- démarre/recrée les conteneurs
- exécute les migrations Doctrine
- affiche l'état final des conteneurs

## 5. Vérifications après installation

### 5.1 Vérifier les conteneurs

```bash
docker compose --env-file .env.docker.prod -f compose.yaml -f compose.prod.yaml ps
```

### 5.2 Vérifier les logs applicatifs

```bash
docker compose --env-file .env.docker.prod -f compose.yaml -f compose.prod.yaml logs --tail=100 app nginx
```

### 5.3 Vérifier la valeur de `DATABASE_URL` vue par PHP

```bash
docker compose --env-file .env.docker.prod -f compose.yaml -f compose.prod.yaml exec -T app php -r 'echo getenv("DATABASE_URL"), PHP_EOL;'
```

### 5.4 Créer le premier administrateur

Le projet fournit la commande [`app:create-admin`](../src/Command/CreateAdminCommand.php) :

```bash
docker compose --env-file .env.docker.prod -f compose.yaml -f compose.prod.yaml exec -T app \
  php bin/console app:create-admin admin@example.com 'mot-de-passe-admin'
```

## 6. Upgrade applicatif

### 6.1 Upgrade standard

```bash
cd /opt/patrimoine-ronde
scripts/deploy-prod.sh upgrade
```

### Ce que fait `upgrade`

- lance un backup PostgreSQL compressé avant déploiement
- stocke ce backup dans [`var/deploy-prod/backups/`](../var/)
- mémorise le commit précédent dans [`var/deploy-prod/rollback.env`](../var/)
- met à jour le code
- rebuild les images
- recrée les conteneurs
- relance les migrations

### 6.2 Déployer une branche ou un tag précis

```bash
scripts/deploy-prod.sh upgrade --ref main
scripts/deploy-prod.sh upgrade --ref v1.2.3
```

### 6.3 Upgrade sans migrations

```bash
scripts/deploy-prod.sh upgrade --no-migrate
```

### 6.4 Upgrade sans backup

```bash
scripts/deploy-prod.sh upgrade --no-backup
```

> À éviter en production sauf cas exceptionnel.

## 7. Rollback

### 7.1 Rollback du code uniquement

```bash
cd /opt/patrimoine-ronde
scripts/deploy-prod.sh rollback
```

Le script redéploie le commit précédemment mémorisé lors du dernier `upgrade`.

### 7.2 Rollback code + restauration base

```bash
scripts/deploy-prod.sh rollback --restore-db
```

Cette commande :

- redéploie le commit précédent
- restaure le dernier dump PostgreSQL créé par `upgrade`

> À utiliser avec prudence, car la base revient à l'état du backup précédent.

## 8. Fichiers utiles

- script de déploiement : [`scripts/deploy-prod.sh`](../scripts/deploy-prod.sh)
- fichier d'environnement modèle : [`.env.docker.prod.example`](../.env.docker.prod.example)
- configuration Docker prod : [`compose.prod.yaml`](../compose.prod.yaml)
- état rollback : [`var/deploy-prod/`](../var/)

## 9. Commandes utiles de diagnostic

### Voir l'aide du script

```bash
scripts/deploy-prod.sh --help
```

### Voir les logs en continu

```bash
docker compose --env-file .env.docker.prod -f compose.yaml -f compose.prod.yaml logs -f app nginx messenger
```

### Vérifier les migrations

```bash
docker compose --env-file .env.docker.prod -f compose.yaml -f compose.prod.yaml exec -T app \
  php bin/console doctrine:migrations:status --env=prod --no-interaction
```

### Vérifier que la base répond

```bash
docker compose --env-file .env.docker.prod -f compose.yaml -f compose.prod.yaml exec -T database \
  sh -lc 'psql -U "$POSTGRES_USER" -d "$POSTGRES_DB" -c "\dt"'
```

> La commande passe par `sh -lc` pour utiliser les variables d'environnement déjà présentes dans le conteneur `database`.

## 10. Séquence recommandée

### Première installation

```bash
cp .env.docker.prod.example .env.docker.prod
nano .env.docker.prod
scripts/deploy-prod.sh install
```

### Mise à jour standard

```bash
git fetch --all --prune
scripts/deploy-prod.sh upgrade
```

### Si `git pull` est bloqué par `.env`

Le cas le plus fréquent est une modification locale du fichier [`.env`](../.env) sur le serveur. Comme ce fichier est versionné, Git refuse de l'écraser.

Si la configuration serveur est bien dans [`.env.docker.prod`](../.env.docker.prod), remettre [`.env`](../.env) à l'état du dépôt :

```bash
git restore .env
git pull --ff-only origin main
```

Si tu veux inspecter avant restauration :

```bash
git --no-pager diff -- .env
```

### Retour arrière en cas de problème

```bash
scripts/deploy-prod.sh rollback
```

ou, si une restauration de base est nécessaire :

```bash
scripts/deploy-prod.sh rollback --restore-db
```
