Agis comme un architecte logiciel et développeur Symfony senior.

Je veux concevoir une application web simple, maintenable et rapide à développer pour le suivi des rondes sur un site touristique unique.

Contexte métier :
- L’application servira à un seul site touristique.
- Lors des rondes, des agents utiliseront un smartphone Android.
- Ils doivent pouvoir créer rapidement un signalement concernant une zone ou un équipement.
- Ils doivent pouvoir saisir une note au clavier ou via la dictée vocale native Android vers un champ texte.
- Ils doivent pouvoir joindre une ou plusieurs photos directement depuis leur smartphone.
- Depuis un ordinateur, un administrateur doit pouvoir consulter, filtrer, modifier, suivre et traiter les signalements.
- L’application doit également permettre l’envoi d’e-mails de notification pour informer les personnes concernées des nouveaux signalements, changements de statut, relances, ou alertes prioritaires.
- À terme, les tickets pourront être envoyés via API vers GLPI, mais cette intégration n’est pas prioritaire pour le MVP.
- Les e-mails doivent être compatibles avec un usage professionnel, fiable et facilement configurable (SMTP, SendGrid, SES, etc.).

Contraintes techniques :
- Symfony dernière version stable
- PHP 8.5+
- PostgreSQL
- Doctrine ORM
- Twig
- EasyAdmin pour l’interface d’administration
- Symfony Mailer pour l’envoi des e-mails
- Architecture monolithique modulaire
- Application responsive mobile-first
- Simplicité et rapidité de développement prioritaires
- Prévoir une évolution vers PWA avec mode hors ligne
- Prévoir une architecture compatible avec une future intégration GLPI
- Prévoir les mécanismes d’envoi d’e-mails de manière propre, testable et évolutive (templates, queue si besoin, logs, retry)

Exigences fonctionnelles :
1. Authentification utilisateur
2. Création de signalement depuis smartphone
3. Signalement lié à une zone
4. Liaison optionnelle à un équipement
5. Saisie texte manuelle ou via dictée Android
6. Ajout de une à plusieurs photos
7. Priorité : basse, moyenne, haute, critique
8. Statut : nouveau, en cours, résolu, classé sans suite
9. Interface admin sur ordinateur
10. Liste et filtres des signalements par date, zone, équipement, statut, priorité, auteur
11. Gestion admin des zones
12. Gestion admin des équipements
13. Gestion admin des utilisateurs
14. Envoi d’e-mails automatiques de notification :
    - nouveau signalement créé
    - changement de statut
    - signalement critique ou prioritaire
    - notification au responsable / équipe concernée
    - relance si signalement non traité pendant une durée donnée
15. Gestion des destinataires et des modèles d’e-mails
16. Historique des notifications envoyées
17. Possibilité d’ajouter un destinataire de copie ou un destinataire spécifique selon la zone, l’équipement ou le niveau de priorité

Contraintes d’architecture :
- Éviter toute complexité inutile
- Pas de SPA complexe si non indispensable
- Pas d’application mobile native dans un premier temps
- Pas de microservices
- Préserver une base technique propre pour intégrer plus tard :
  - un mode hors ligne plus complet
  - l’API GLPI
  - éventuellement des statistiques
  - un système d’e-mails robuste et centralisé
- Les envois d’e-mails doivent être découplés de la logique métier principale, idéalement via un service dédié ou une file de messages en fonction du volume attendu
- Privilégier des composants standards Symfony et des bonnes pratiques de sécurité

Demandes :
1. Propose l’architecture applicative la plus pragmatique
2. Justifie le choix entre application Symfony Twig classique, PWA, ou séparation API/front
3. Propose un modèle de données détaillé
4. Décris les entités Doctrine principales et leurs relations
5. Propose une arborescence de projet Symfony claire
6. Propose les écrans du parcours mobile et du parcours admin
7. Décris une stratégie réaliste de mode hors ligne :
   - MVP
   - V1.5
   - V2
8. Explique comment préparer proprement une future intégration GLPI
9. Propose une roadmap de développement en itérations
10. Donne les bundles Symfony utiles
11. Propose une architecture pour les e-mails :
   - configuration SMTP ou fournisseur externe
   - templates Twig
   - gestion des événements
   - envoi asynchrone si nécessaire
   - logs et suivi des envois
12. Signale les points de vigilance sécurité, photos, UX mobile, synchronisation, et envoi d’e-mails

Je veux une réponse en français, structurée, concrète, orientée implémentation, avec des recommandations explicites de niveau professionnel.

Le document doit :
- être clair et exploitable pour un développeur Symfony
- proposer un MVP fiable sans sur-ingénierie
- mentionner les technologies recommandées
- détailler les entités, les flux métier et les bonnes pratiques de sécurité
- inclure le besoin d’e-mails comme partie intégrante du produit, sans le traiter comme une fonctionnalité annexe