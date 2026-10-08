# Règles de Développement - Version opérationnelle IA

Projet : application Symfony de suivi des rondes patrimoniales et audiovisuelles.

## 1. Objectif

Construire une application web :

- simple à développer
- fiable en production
- mobile-first pour Android
- prête pour le hors ligne / PWA
- extensible vers GLPI plus tard

Priorités, dans cet ordre :

1. justesse métier
2. simplicité
3. maintenabilité
4. sécurité
5. rapidité d'exécution

---

## 2. Stack et principes

- Symfony dernière version stable compatible projet
- PHP récent supporté par Symfony, l'hébergement et la CI
- PostgreSQL
- Twig pour le front serveur
- EasyAdmin pour l'admin
- Symfony Mailer pour les notifications e-mail
- Stimulus pour les interactions légères
- AssetMapper par défaut
- Messenger / Scheduler seulement si besoin réel
- `declare(strict_types=1);` dans les classes PHP

Rester sur un **monolithe Symfony modulaire**.  
Ne pas introduire de SPA complexe, microservices ou sur-architecture sans nécessité démontrée.

---

## 3. Règles absolues

### A TOUJOURS faire

- Mettre la logique métier dans des services ou use cases, pas dans les contrôleurs
- Garder des contrôleurs fins : requête, autorisation, orchestration, réponse
- Utiliser l'injection de dépendances via le constructeur
- Utiliser des types explicites partout
- Préférer `DateTimeImmutable`
- Utiliser des enums pour les statuts, priorités, types et états de synchronisation
- Utiliser ULID ou UUID pour les entités créées côté client ou synchronisées
- Valider toutes les entrées côté serveur
- Vérifier les permissions avec `#[IsGranted]`, `denyAccessUnlessGranted()` ou Voters
- Protéger les formulaires avec CSRF
- Journaliser les actions sensibles et les erreurs importantes
- Utiliser les migrations Doctrine pour toute évolution de schéma
- Ajouter des index utiles sur les colonnes filtrées, triées ou jointes
- Prévoir l'idempotence de la synchronisation hors ligne
- Rendre les erreurs explicites pour l'utilisateur et exploitables pour le support
- Utiliser un service d'email unique (`EmailService`) pour tous les envois, jamais l'envoi direct dans le contrôleur ou le repository
- Centraliser la configuration SMTP / fournisseur externe dans `EmailConfiguration` ou un service dédié
- Rendre les templates d'e-mail dans des fichiers Twig dédiés avec variables strictement typées
- Déclencher les notifications via le domaine ou un événement métier, pas par des appels ad hoc dans différents points du code
- Stocker l'historique des envois dans une table dédiée (`EmailLog`) pour traçabilité, retry et anti-doublon
- Prévoir un mécanisme d'anti-doublon : un déclencheur ne doit pas envoyer un e-mail identique plusieurs fois pour le même destinataire dans une même période
- Envisager Messenger pour l'envoi asynchrone si le volume ou la fiabilité l'exige
- Gérer les erreurs d'envoi avec retry, logs et alertes ciblées

### A NE JAMAIS faire

- Mettre de la logique métier importante dans un contrôleur
- Coupler directement le domaine à GLPI
- Utiliser `doctrine:schema:update --force`
- Laisser un échec de synchronisation sans état visible
- Accepter un upload sans validation stricte
- Stocker un secret dans le code
- Se reposer uniquement sur la validation client
- Charger massivement des listes sans pagination ou filtrage
- Introduire de la complexité technique "pour plus tard"
- Envoyer des e-mails directement depuis un contrôleur, un listener UI ou un repository
- Hardcoder des adresses SMTP, tokens ou clés API dans le code source
- Envoyer plusieurs e-mails identiques dans la même journée pour le même événement sans vérification de duplication
- Utiliser des templates e-mail non validés ou non sécurisés avec des variables non filtrées
- Ne pas journaliser un échec d'envoi ou une notification non délivrée
- Oublier les destinataires en copie, les personnes concernées et les cas de relance

---

## 4. Modèle d'architecture attendu

Le code doit rester lisible selon cette séparation :

- **UI / HTTP** : contrôleurs, formulaires, templates
- **Application** : services, use cases, DTO, commands
- **Domaine** : entités, enums, règles métier
- **Infrastructure** : Doctrine, stockage fichiers, appels externes, scheduler

### Concepts métier principaux

- Utilisateur
- Zone
- Equipement
- Signalement
- Photo
- Commentaire ou historique
- Statut de synchronisation
- Référence externe GLPI plus tard

---

## 5. Règles Symfony / PHP

- Utiliser attributs PHP/Symfony/Doctrine
- Préférer des classes finales quand l'héritage n'apporte rien
- Utiliser readonly quand pertinent
- Préférer DTO / Command pour les créations ou mises à jour importantes
- Créer des exceptions métier seulement si elles améliorent vraiment la lecture
- Commenter peu, seulement le non-évident
- Préférer la clarté à l'astuce

Exemple de responsabilité acceptable d'un contrôleur :

1. lire la requête
2. vérifier accès et CSRF
3. déléguer au service métier
4. renvoyer HTML ou redirect

---

## 6. Règles Doctrine / base de données

- Requêtes métier dans les repositories
- ORM par défaut pour le CRUD
- DBAL ou SQL natif autorisé si justification claire de performance, reporting ou bulk processing
- Eviter les mots réservés SQL pour tables et colonnes
- Nommer les tables et colonnes en `snake_case`
- Préférer des noms explicites : `sync_status`, `observed_at`, `external_id`

Colonnes à surveiller pour index :

- `created_at`
- `status`
- `priority`
- `zone_id`
- `asset_id`
- `sync_status`
- `external_id`

---

## 7. Règles métier pour cette application

### Signalements

- Un signalement appartient à une zone
- Un signalement peut être lié à un équipement
- Le statut métier est distinct du statut de synchronisation
- Toute action importante doit être traçable

### Hors ligne / synchronisation

- La saisie terrain ne doit pas dépendre d'un réseau parfait
- Un brouillon local doit être possible
- La synchronisation doit être idempotente
- Un même signalement ne doit pas créer de doublons
- Une erreur de synchro ne doit jamais supprimer les données locales
- L'utilisateur doit voir clairement l'état : brouillon, à synchroniser, synchronisé, erreur

### Photos

- Vérifier MIME type, extension et taille
- Renommer côté serveur
- Ne jamais faire confiance au nom original
- Nettoyer les fichiers orphelins
- Isoler le stockage des uploads quand possible

### Emails et notifications

- L'envoi d'e-mails doit passer par un service dédié : `EmailService`
- Les paramètres SMTP / fournisseur doivent être gérés via `EmailConfiguration` et variables d'environnement
- Les notifications doivent avoir un type explicite : `signalement_created`, `signalement_status_changed`, `signalement_priority_alert`, `relance`, etc.
- Le code métier doit déclencher un événement ou une commande, puis un service d'envoi se charge de la notification
- Les templates e-mail doivent être des fichiers Twig dédiés, avec variables minimales et données typées
- Tous les destinataires doivent être validés et normalisés avant envoi
- Les e-mails doivent être envoyés avec un sujet clair, un corps lisible, et un `reply-to` ou une signature cohérente selon le contexte
- Les notifications doivent être idempotentes : même événement ne doit pas créer des doublons sur plusieurs envois
- Un historique d'envoi (`EmailLog`) doit enregistrer : destinataire, template, déclencheur, date d'envoi, statut, erreur, message ID
- Les envois critiques doivent être asynchrones via Messenger si la latence utilisateur le justifie
- Les relances doivent être planifiées avec prudence et évitée en boucle si l'utilisateur ou le système a déjà répondu
- Les e-mails doivent être testables en environnement local et en CI sans dépendre d'un vrai serveur SMTP
- Les erreurs d'envoi doivent être visibles dans les logs et peuvent faire l'objet d'une alerte en production

Règles anti-doublon pour les e-mails :

- par défaut, ne pas envoyer plus d'un e-mail identique par destinataire et par événement dans une fenêtre donnée
- utiliser `EmailLog` ou une clé logique comme `event_type + entity_id + user_id + date_scope`
- pour les relances, prévoir un seuil temporel explicite (`1 jour`, `7 jours`, etc.)
- les e-mails déjà envoyés doivent rester traçables en admin pour audit

### Intégration GLPI

- Préparer l'intégration mais ne pas la mélanger au coeur métier
- Isoler les appels API dans des services dédiés
- Prévoir `external_id` et `export_status` sur les signalements
- Les exports doivent être rejouables et traçables

---

## 8. UX attendue

L'application doit être pensée d'abord pour le terrain :

- peu de champs
- gros boutons
- parcours court
- messages simples
- récupération propre après coupure réseau

Le clavier Android gère la dictée vocale ; l'application gère le texte saisi, pas la reconnaissance vocale serveur.

---

## 9. Tests et qualité

Tester en priorité :

- création d'un signalement
- upload photo
- permissions
- changements de statut
- synchronisation hors ligne

Base minimale avant livraison :

```bash
composer validate --strict
./vendor/bin/php-cs-fixer fix --dry-run
./vendor/bin/phpstan analyse
php bin/phpunit
php bin/console lint:yaml config/
php bin/console lint:twig templates/
php bin/console lint:container
composer audit
```

---

## 10. Tâches planifiées et exploitation

- Préférer Scheduler + Messenger si une tâche récurrente est nécessaire
- Utiliser au plus un déclencheur système minimal si besoin
- Logger les exécutions, erreurs et retries
- Mettre des verrous si une tâche doit être unique
- Distinguer logs techniques et audit métier

---

## 11. Règle de décision pour l'IA

Quand plusieurs options sont possibles, choisir celle qui :

1. respecte le métier
2. ajoute le moins de complexité durable
3. suit les conventions Symfony
4. garde une évolution simple vers offline et GLPI

Si une solution est "plus moderne" mais plus lourde sans bénéfice concret, **ne pas la choisir**.

---

## 12. Annexes archivées - ancien contexte projet

Conserver uniquement comme mémoire. Ne pas appliquer par défaut à ce projet.

<!--
Ancien contexte archivé :
- EmailService / EmailConfiguration / EmailLog
- ReportCalculationService / ReportData
- règles de semaines ISO
- contrainte de total à 100%
- Azure AD / AzureAuthenticator
- exemples TimeEntry

**Emails** :
   - Toujours utiliser EmailService (readonly) pour envoi
   - Configuration SMTP dans EmailConfiguration (pas de serveur local)
   - Templates avec variables (render() method)
   - Protection anti-doublon via EmailLog (wasSentToday())
   - Max 1 email/jour/utilisateur pour un même déclencheur
   - Destinataires configurables (user + emails personnalisés)
-->
