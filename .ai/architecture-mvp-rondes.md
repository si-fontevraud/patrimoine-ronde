# Architecture MVP - Application de suivi des rondes

## 1. Recommandation d'architecture applicative

### Décision principale

Je recommande une **application Symfony monolithique modulaire**, avec :

- **Twig** pour l'interface web
- **EasyAdmin** pour l'administration
- **Doctrine ORM + PostgreSQL**
- **Symfony Mailer** pour les e-mails
- **Symfony Messenger** pour découpler les notifications
- **Stimulus** pour les interactions JS légères
- **responsive mobile-first**
- **préparation PWA progressive**, sans basculer dès le départ sur une SPA

### Pourquoi c'est le bon choix

Cette approche répond exactement aux priorités :

- **simple à construire**
- **rapide à livrer**
- **robuste**
- **peu de dette technique initiale**
- **évolutive vers hors ligne / GLPI / stats**

### Architecture logique proposée

Je découperais le monolithe en modules fonctionnels :

1. **Identity**
   - utilisateurs
   - rôles
   - authentification

2. **Catalog**
   - zones
   - équipements

3. **Reporting**
   - signalements
   - photos
   - commentaires / historique
   - priorités / statuts

4. **Notification**
   - règles de destinataires
   - templates d'e-mails
   - journal des envois
   - relances

5. **Integration**
   - future intégration GLPI
   - external references
   - jobs d'export

6. **Admin**
   - back-office EasyAdmin
   - filtres
   - tableaux de bord

## 2. Choix entre Symfony Twig classique, PWA, ou API/front séparé

### Recommandation explicite

**Choisir un Symfony Twig classique mobile-first, préparé pour évoluer en PWA.**  
**Ne pas choisir une séparation API/front pour le MVP.**

### Comparatif

#### Option A - Symfony Twig classique

**Recommandée pour le MVP**

**Avantages :**

- développement très rapide
- une seule base de code
- sécurité et auth simples
- EasyAdmin s'intègre naturellement
- maintenance plus simple
- moins de complexité infra
- excellent pour un besoin mono-site

**Limites :**

- hors ligne limité au départ
- UX moins "app native" qu'une SPA/PWA avancée

#### Option B - PWA complète dès le départ

**Trop tôt pour le MVP**

**Avantages :**

- meilleure base offline
- sensation plus "app"

**Inconvénients :**

- complexifie la synchro, le cache, les conflits, les médias
- demande plus de conception
- risque de sur-ingénierie

#### Option C - Front séparé + API

**Non recommandé au démarrage**

**Avantages :**

- flexibilité maximale
- API réutilisable

**Inconvénients :**

- double complexité front/back
- auth plus complexe
- coût de développement supérieur
- inutile pour un seul site et un MVP centré sur formulaires + admin

### Conclusion

#### Choix cible

- **MVP** : Symfony + Twig + EasyAdmin + Stimulus
- **Évolution** : PWA progressive
- **Plus tard si nécessaire** : exposer quelques endpoints JSON pour synchro/mobile avancé

## 3. Modèle de données détaillé

Je propose un modèle simple mais propre, couvrant dès le départ :

- métier terrain
- administration
- notifications e-mail
- future intégration GLPI
- future synchro offline

### Entités principales

#### User

Représente un agent ou un administrateur.

Champs :

- `id` (ULID)
- `email` (unique)
- `password`
- `firstName`
- `lastName`
- `roles`
- `isActive`
- `phone` nullable
- `createdAt`
- `updatedAt`
- `lastLoginAt` nullable

#### Zone

Zone fonctionnelle ou géographique du site.

Champs :

- `id` (ULID)
- `code` unique
- `name`
- `description` nullable
- `isActive`
- `createdAt`
- `updatedAt`

#### Equipment

Équipement rattaché à une zone.

Champs :

- `id` (ULID)
- `zone` (ManyToOne Zone)
- `code` unique
- `name`
- `type` nullable
- `description` nullable
- `isActive`
- `createdAt`
- `updatedAt`

#### Report

Le signalement.

Champs :

- `id` (ULID)
- `reference` unique lisible métier
- `zone` (ManyToOne Zone)
- `equipment` nullable (ManyToOne Equipment)
- `author` (ManyToOne User)
- `title`
- `description`
- `priority` enum
- `status` enum
- `source` enum (`web`, `mobile`, `sync`)
- `observedAt`
- `createdAt`
- `updatedAt`
- `resolvedAt` nullable
- `closedAt` nullable
- `assignedTo` nullable (ManyToOne User)
- `isEmailAlertSent` bool
- `syncStatus` enum nullable
- `clientRequestId` nullable, unique pour synchro future
- `glpiExportStatus` enum nullable
- `glpiExternalId` nullable
- `lastNotifiedAt` nullable

#### ReportPhoto

Photo jointe à un signalement.

Champs :

- `id` (ULID)
- `report` (ManyToOne Report)
- `path`
- `originalFilename`
- `mimeType`
- `size`
- `width` nullable
- `height` nullable
- `checksum` nullable
- `takenAt` nullable
- `createdAt`

#### ReportStatusHistory

Historique métier.

Champs :

- `id` (ULID)
- `report` (ManyToOne Report)
- `changedBy` nullable (ManyToOne User)
- `fromStatus` nullable
- `toStatus`
- `comment` nullable
- `createdAt`

#### ReportComment

Commentaires de suivi.

Champs :

- `id` (ULID)
- `report` (ManyToOne Report)
- `author` (ManyToOne User)
- `message`
- `isInternal` bool
- `createdAt`

#### NotificationRule

Règle de routage des notifications.

Champs :

- `id` (ULID)
- `name`
- `eventType` enum
- `zone` nullable
- `equipment` nullable
- `priority` nullable
- `status` nullable
- `toRecipients` JSON ou relation dédiée
- `ccRecipients` JSON ou relation dédiée
- `isActive`
- `createdAt`
- `updatedAt`

#### EmailTemplate

Template d'e-mail configurable.

Champs :

- `id` (ULID)
- `code` unique
- `name`
- `subjectTemplate`
- `htmlTemplatePath`
- `textTemplatePath`
- `isActive`
- `createdAt`
- `updatedAt`

#### EmailLog

Historique d'envoi.

Champs :

- `id` (ULID)
- `eventType`
- `templateCode`
- `report` nullable
- `recipient`
- `cc` nullable JSON
- `subject`
- `status` enum (`pending`, `sent`, `failed`, `skipped`)
- `providerMessageId` nullable
- `errorMessage` nullable
- `deduplicationKey` nullable
- `sentAt` nullable
- `createdAt`
- `payloadSnapshot` JSON nullable

#### ReminderPolicy

Règle de relance.

Champs :

- `id` (ULID)
- `name`
- `targetStatus` enum
- `delayHours`
- `priorityFilter` nullable
- `zone` nullable
- `isActive`
- `createdAt`
- `updatedAt`

#### ExternalTicketLink

Préparation GLPI / autres systèmes.

Champs :

- `id` (ULID)
- `report` (ManyToOne Report)
- `system` enum (`glpi`)
- `externalId`
- `externalReference` nullable
- `status` enum
- `lastSyncAt` nullable
- `lastError` nullable
- `createdAt`
- `updatedAt`

## 4. Entités Doctrine principales et relations

### Relations coeur métier

- **Zone** 1 --- n **Equipment**
- **Zone** 1 --- n **Report**
- **Equipment** 1 --- n **Report** (optionnel côté report)
- **User** 1 --- n **Report** (author)
- **User** 1 --- n **Report** (assignedTo, optionnel)
- **Report** 1 --- n **ReportPhoto**
- **Report** 1 --- n **ReportStatusHistory**
- **Report** 1 --- n **ReportComment**
- **Report** 1 --- n **EmailLog**
- **Report** 1 --- n **ExternalTicketLink**

### Enums à utiliser

Je recommande des **enums PHP natives**.

#### ReportPriority

- `LOW`
- `MEDIUM`
- `HIGH`
- `CRITICAL`

#### ReportStatus

- `NEW`
- `IN_PROGRESS`
- `RESOLVED`
- `DISMISSED`

#### NotificationEventType

- `REPORT_CREATED`
- `REPORT_STATUS_CHANGED`
- `REPORT_PRIORITY_ALERT`
- `REPORT_REMINDER`

#### EmailLogStatus

- `PENDING`
- `SENT`
- `FAILED`
- `SKIPPED`

#### SyncStatus

- `DRAFT`
- `PENDING_SYNC`
- `SYNCED`
- `SYNC_ERROR`

### Index à prévoir

Sur `report` :

- `created_at`
- `observed_at`
- `status`
- `priority`
- `zone_id`
- `equipment_id`
- `author_id`
- `assigned_to_id`
- `sync_status`
- `glpi_export_status`

Sur `email_log` :

- `event_type`
- `status`
- `created_at`
- `report_id`
- `deduplication_key`

## 5. Arborescence de projet Symfony claire

Je recommande une arborescence orientée modules, pas uniquement technique.

```text
src/
  Domain/
    Catalog/
      Entity/
      Enum/
      Repository/
    Identity/
      Entity/
      Repository/
    Reporting/
      Entity/
      Enum/
      Repository/
    Notification/
      Entity/
      Enum/
      Repository/
    Integration/
      Entity/
      Enum/
      Repository/

  Application/
    Reporting/
      Command/
      DTO/
      Handler/
      Service/
    Notification/
      Message/
      MessageHandler/
      Service/
    Integration/
      GLPI/
    Shared/
      Clock/
      Uuid/

  Infrastructure/
    Doctrine/
    Mail/
    Storage/
    Scheduler/
    Security/
    Symfony/

  UI/
    Http/
      Controller/
        Mobile/
        Admin/
    Form/
      Reporting/
      Admin/
    Twig/
      Extension/

config/
  packages/
  routes/
  services.yaml

templates/
  mobile/
    report/
  admin/
  email/
    report/
    layout/

assets/
  controllers/
  styles/

public/
  uploads/

migrations/
tests/
  Unit/
  Functional/
  Integration/
```

### Variante plus simple acceptable

Si vous voulez rester très Symfony classique au début :

```text
src/
  Controller/
  Entity/
  Repository/
  Form/
  Service/
  Message/
  MessageHandler/
  Enum/
  Security/
```

Mais je conseille au minimum de **séparer Reporting / Notification / Integration** pour éviter le couplage.

## 6. Écrans du parcours mobile et du parcours admin

### Parcours mobile agent

#### 1. Connexion

- e-mail
- mot de passe
- bouton connexion
- éventuellement "rester connecté"

#### 2. Accueil terrain

- bouton **Nouveau signalement**
- accès à **Mes signalements récents**
- état réseau simple
- futur accès brouillons/hors ligne

#### 3. Formulaire nouveau signalement

Champs :

- zone
- équipement optionnel
- titre court
- description
- priorité
- photos
- date/heure constatée préremplie
- bouton enregistrer

### UX mobile recommandée

- gros champs tactiles
- listes courtes
- zone d'abord, équipement filtré ensuite
- `<textarea>` classique pour profiter de la **dictée native Android**
- bouton photo avec :
  - appareil photo
  - galerie
- `accept="image/*"` et si pertinent `capture="environment"`

#### 4. Confirmation

- message succès
- référence du signalement
- lien vers détail

#### 5. Détail du signalement

- statut
- priorité
- zone / équipement
- photos
- historique minimal

### Parcours admin

#### 1. Tableau de bord

- nombre de signalements ouverts
- critiques non traités
- en retard / relances
- activité récente

#### 2. Liste des signalements

Filtres :

- date
- zone
- équipement
- statut
- priorité
- auteur
- assigné à
- texte libre

Colonnes :

- référence
- date
- zone
- équipement
- priorité
- statut
- auteur
- assignation

#### 3. Fiche signalement admin

- détail complet
- galerie photos
- changement de statut
- assignation
- ajout de commentaire
- historique
- historique e-mail
- futur lien GLPI

#### 4. Gestion des zones

- CRUD simple
- activation / désactivation

#### 5. Gestion des équipements

- CRUD
- rattachement zone
- activation / désactivation

#### 6. Gestion utilisateurs

- CRUD
- rôles
- actif/inactif

#### 7. Gestion notifications

- règles de destinataires
- templates
- relances
- historique d'envoi

## 7. Stratégie réaliste de mode hors ligne

Il faut être pragmatique : **ne pas surconcevoir le offline dans le MVP**.

### MVP

Objectif : **tolérance réseau simple**, pas un vrai offline complet.

#### Ce que je recommande

- application web responsive normale
- auto-save local du formulaire en brouillon via `localStorage` ou `IndexedDB`
- message clair si perte réseau
- possibilité de recharger le brouillon local
- aucune synchro complexe
- pas de création définitive sans réseau

#### Résultat

- très rapide à implémenter
- sécurise la saisie terrain
- évite de perdre du texte ou les choix

### V1.5

Objectif : **PWA légère et brouillons persistants**

#### Ajouter

- manifest PWA
- service worker simple
- cache shell/UI
- stockage local des brouillons
- file d'attente locale de soumission
- upload différé si réseau absent
- page "À synchroniser"

#### Conditions

- chaque signalement créé côté client doit avoir un **clientRequestId ULID**
- le serveur doit être **idempotent**

### V2

Objectif : **offline réel partiel**

#### Ajouter

- consultation locale des zones et équipements
- création de signalements complète hors ligne
- synchronisation automatique
- résolution de conflits simple
- reprise sur erreur
- synchro photo robuste

### Important

Le plus difficile en offline sera :

- les photos
- la gestion des doublons
- les conflits de statut
- l'expérience de reprise après échec

## 8. Préparer proprement une future intégration GLPI

La bonne pratique est de **ne jamais coupler directement `Report` à l'API GLPI**.

### Principe recommandé

Créer une couche dédiée :

- `GlpiClientInterface`
- `GlpiExporter`
- `GlpiPayloadFactory`
- `ExternalTicketLink`
- `GlpiExportMessage`

### Approche

#### Dans le domaine métier

Le signalement reste autonome.

#### Dans l'intégration

Un service transforme le signalement en payload GLPI.

#### Dans la persistance

On garde une table `external_ticket_link`.

### Données à préparer dès maintenant

Dans `Report` ou via table liée :

- `glpiExportStatus`
- `glpiExternalId`
- `lastExportAttemptAt`
- `lastExportError`

### Stratégie d'intégration future

1. événement métier "signalement validé pour export"
2. message Messenger
3. handler d'export GLPI
4. journalisation résultat
5. retry si échec technique
6. pas de blocage du flux métier principal

### Bénéfice

Le jour où GLPI arrive, vous n'avez pas à casser le coeur métier.

## 9. Roadmap de développement en itérations

### Itération 0 - Socle technique

- projet Symfony
- sécurité
- PostgreSQL
- Doctrine migrations
- base UI Twig
- EasyAdmin
- uploader photos
- configuration mail dev/test/prod

### Itération 1 - Référentiels et auth

- utilisateurs
- rôles
- zones
- équipements
- CRUD admin de base

### Itération 2 - Signalement mobile MVP

- formulaire mobile-first
- création signalement
- upload multi-photos
- consultation détail
- historique de statut initial

### Itération 3 - Back-office signalements

- liste admin
- filtres
- modification statut
- assignation
- commentaires
- historique

### Itération 4 - Notifications e-mail MVP

- templates Twig
- règles destinataires
- envoi sur création
- envoi sur changement de statut
- alertes priorité haute/critique
- historique EmailLog

### Itération 5 - Robustesse notification

- Messenger async
- retry
- anti-doublon
- relances planifiées
- écrans admin notification

### Itération 6 - Préparation offline

- brouillons locaux
- autosave
- UI état réseau
- manifest PWA

### Itération 7 - Préparation GLPI

- modèle `ExternalTicketLink`
- interfaces d'intégration
- export manuel ou batch

## 10. Bundles Symfony utiles

### Indispensables

- **symfony/security-bundle**
- **symfony/form**
- **symfony/validator**
- **symfony/twig-bundle**
- **symfony/mailer**
- **symfony/messenger**
- **doctrine/orm**
- **doctrine/doctrine-bundle**
- **doctrine/doctrine-migrations-bundle**
- **easycorp/easyadmin-bundle**

### Très utiles

- **symfony/ux-stimulus**
- **symfony/asset-mapper**
- **symfony/scheduler** ou simple commande cron si besoin
- **symfony/monolog-bundle**

### Recommandés selon choix d'upload

#### Option pragmatique

- upload géré par un service maison + validation Symfony

#### Option bundle

- **vich/uploader-bundle** si vous voulez accélérer la gestion des fichiers

### Pour la qualité

- **symfony/phpunit-bridge**
- **zenstruck/foundry** pour fixtures/tests
- **doctrine/doctrine-fixtures-bundle**

### Pour e-mails / preview

- **symfony/web-profiler-bundle**
- éventuellement **mailhog/mailpit** côté environnement local

## 11. Architecture recommandée pour les e-mails

Les e-mails doivent être traités comme un **sous-système produit**, pas comme une simple fonction utilitaire.

### 11.1 Objectif d'architecture

Construire un pipeline :

**événement métier -> résolution des destinataires -> choix template -> envoi -> log -> retry éventuel**

### 11.2 Configuration SMTP / fournisseur externe

Utiliser `MAILER_DSN` par environnement.

Exemples possibles :

- SMTP classique
- SendGrid
- Amazon SES
- Mailgun
- serveur interne

#### Recommandation

Masquer le fournisseur derrière Symfony Mailer, sans coder contre le provider directement.

### 11.3 Composants à prévoir

#### `NotificationOrchestrator`

Point d'entrée applicatif.

Responsabilités :

- reçoit l'événement métier
- choisit le type de notification
- demande les destinataires
- crée le message métier

#### `RecipientResolver`

Responsable de déterminer :

- destinataires principaux
- copies
- destinataires spécifiques selon :
  - zone
  - équipement
  - priorité
  - statut

#### `EmailTemplateRenderer`

- sélectionne le template
- injecte les variables
- prépare sujet + HTML + texte

#### `EmailSender`

- envoie via Symfony Mailer
- ne contient pas la logique métier

#### `EmailLogManager`

- écrit l'historique
- stocke succès/échec
- enregistre `providerMessageId`
- permet les retries et audits

#### `ReminderScheduler`

- détecte les signalements non traités
- produit des notifications de relance

### 11.4 Événements à gérer

Je recommande des événements applicatifs explicites :

- `ReportCreated`
- `ReportStatusChanged`
- `ReportPriorityRaised`
- `ReportReminderDue`

Ces événements ne doivent pas envoyer eux-mêmes l'e-mail ; ils déclenchent l'orchestration.

### 11.5 Templates Twig

Prévoir :

- sujet séparé
- corps HTML
- corps texte

Exemples :

- `templates/email/report/created.html.twig`
- `templates/email/report/created.txt.twig`
- `templates/email/report/status_changed.html.twig`
- `templates/email/report/priority_alert.html.twig`
- `templates/email/report/reminder.html.twig`

### 11.6 Envoi synchrone ou asynchrone ?

### Recommandation explicite

**Prévoir Messenger dès le MVP notification.**

Pourquoi :

- pas bloquer l'utilisateur mobile
- meilleure résilience
- retry facile
- logs plus propres
- prêt pour montée en charge

#### Concrètement

- l'application crée un message `SendNotificationEmailMessage`
- un handler envoie réellement l'e-mail
- transport :
  - **dev** : sync possible
  - **prod** : doctrine transport ou broker si besoin futur

### 11.7 Logs et suivi des envois

Créer `EmailLog` avec :

- type notification
- destinataires
- sujet
- statut
- date
- erreur
- message ID fournisseur
- clé de déduplication
- référence signalement

### 11.8 Retry et anti-doublon

#### Retry

- retry automatique Messenger pour échec technique
- distinction entre :
  - erreur technique temporaire
  - erreur fonctionnelle définitive

#### Anti-doublon

Clé du style :
`{eventType}:{reportId}:{recipient}:{statusOrPriority}:{dateBucket}`

Exemple :

- empêche 3 alertes identiques en quelques minutes
- conserve les relances planifiées légitimes

### 11.9 Relances

Deux options pragmatiques :

- **commande Symfony planifiée via cron**
- **Symfony Scheduler**

Je recommande :

- cron + commande au début
- Scheduler si vous centralisez plus tard d'autres jobs

## 12. Points de vigilance

### 12.1 Sécurité

#### Auth / accès

- rôles clairs : agent / admin
- contrôle d'accès serveur partout
- CSRF sur formulaires
- mots de passe hashés avec l'algo Symfony par défaut
- sessions sécurisées
- rate limiting sur login si exposition publique

#### Validation

- ne jamais faire confiance au client
- validation stricte de zone/équipement
- vérifier qu'un équipement appartient bien à la zone choisie

#### Traçabilité

- historiser changements de statut
- journaliser actions sensibles
- journaliser échecs e-mail et upload

### 12.2 Photos

- validation MIME réelle, pas seulement extension
- taille max raisonnable
- multi-upload borné
- renommage serveur
- stockage hors nom d'origine
- suppression des orphelins
- génération éventuelle de miniature côté admin
- attention aux métadonnées EXIF/GPS si confidentialité requise

### 12.3 UX mobile

- peu de champs
- champs gros et lisibles
- boutons d'action visibles
- description dictée possible via clavier Android natif
- formulaires courts
- retour utilisateur immédiat
- optimisation upload réseau faible
- compression image éventuelle côté serveur

### 12.4 Synchronisation

Si vous allez vers le offline plus tard :

- ULID/UUID côté client
- idempotence serveur
- gestion des conflits
- état visible :
  - brouillon
  - à synchroniser
  - synchronisé
  - erreur

### 12.5 E-mails

- ne jamais envoyer depuis contrôleur
- distinguer création / changement statut / alerte / relance
- fiabiliser les destinataires configurables
- prévoir copies métier
- historiser chaque envoi
- gérer retries
- tester les templates
- éviter les doublons

## Recommandation finale synthétique

### Stack cible

- **Symfony dernière stable**
- **PHP 8.4+**
- **PostgreSQL**
- **Doctrine ORM**
- **Twig**
- **EasyAdmin**
- **Symfony Mailer**
- **Symfony Messenger**
- **Stimulus + AssetMapper**
- **Monolithe modulaire mobile-first**

### Décision produit/architecture

- **MVP en Symfony Twig classique**
- **pas de SPA**
- **pas d'API front séparé**
- **préparation PWA progressive**
- **notifications e-mail conçues comme un module central**
- **intégration GLPI préparée par abstraction, sans couplage direct**

### Ce que je ferais concrètement

Si je devais lancer le projet, je partirais sur :

1. référentiels + auth
2. création signalement mobile
3. admin EasyAdmin + filtres
4. module notification robuste
5. brouillons locaux
6. préparation GLPI
