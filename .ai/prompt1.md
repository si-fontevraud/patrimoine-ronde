Agis comme un architecte logiciel et développeur Symfony senior.

Je veux concevoir une application web simple, maintenable et rapide à développer pour le suivi des rondes sur un site touristique unique.

## 1. Contexte métier

- L’application servira à un seul site touristique.
- Lors des rondes, des agents utiliseront un smartphone Android.
- L’application doit permettre une ronde quotidienne orientée expérience visiteur, par zone et par point de contrôle.
- La saisie doit rester légère : on ne crée un incident que lorsqu’une action est nécessaire, avec possibilité d’en créer à la volée.
- Les agents doivent pouvoir créer rapidement un signalement concernant une zone ou un équipement.
- Ils doivent pouvoir saisir une note au clavier ou via la dictée vocale native Android vers un champ texte.
- Ils doivent pouvoir joindre une ou plusieurs photos directement depuis leur smartphone.
- Depuis un ordinateur, un administrateur doit pouvoir consulter, filtrer, modifier, suivre et traiter les signalements/incidents.
- L’application doit permettre l’orientation/attribution d’un incident à un service pilote et à des services supports.
- Il faut conserver les photos, commentaires, horodatages, changements de statut et éléments de résolution.
- L’application doit permettre l’envoi d’e-mails de notification pour informer les personnes concernées (nouveaux incidents, changements de statut, relances, alertes prioritaires).
- À terme, les tickets pourront être envoyés via API vers GLPI, mais cette intégration n’est pas prioritaire pour le MVP.
- Les e-mails doivent être compatibles avec un usage professionnel, fiable et facilement configurable (SMTP, SendGrid, SES, etc.).

## 2. Objectifs

- Réaliser une ronde quotidienne sur smartphone.
- Contrôler l’expérience visiteur par zone et par point de contrôle, sans imposer un enregistrement lourd pour chaque équipement.
- Créer un incident uniquement lorsqu’une action est nécessaire, tout en gardant la flexibilité d’en créer à la volée.
- Prioriser les interventions sur trois critères notés de 1 à 5 :
  - impact visiteur
  - urgence
  - risque d’aggravation
- Orienter et/ou attribuer chaque incident au bon service pilote et éventuels services supports.
- Conserver les photos, commentaires, horodatages, changements de statut et éléments de résolution.
- Produire des indicateurs de pilotage et un historique exploitable.

## 3. Contraintes techniques

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

## 4. Exigences fonctionnelles

1. Authentification utilisateur (compte professionnel @fontevraud.fr ; SSO Microsoft à cadrer, fallback login local possible en MVP).
2. Ronde quotidienne sur smartphone :
   - sélection/réception du parcours du jour (TBD)
   - démarrage de ronde avec horodatage
   - affichage des zones et points de contrôle dans l’ordre défini ou selon besoin du jour (TBD)
   - choix d’un résultat par point : OK / Observation / Incident / Non contrôlé (nomenclature à confirmer)
   - clôture de ronde avec récapitulatif : contrôles, anomalies, éléments non vérifiés
3. Création de signalement/incident depuis smartphone.
4. Incident lié à une zone.
5. Liaison optionnelle à un équipement LOXYA.
6. Saisie texte manuelle ou via dictée Android.
7. Ajout de une à plusieurs photos.
8. Priorisation par score :
   - impact visiteur (1..5)
   - urgence (1..5)
   - risque aggravation (1..5)
   - score total sur 15
   - niveau de priorité dérivé de seuils paramétrables (sans modification de code)
9. Cycle de vie incident (à implémenter et affiner) :
   - Nouveau → Qualifié → Affecté → En cours → En attente → Résolu → Clos
   - possibilité de motifs/sous-statuts d’attente (IT, technique, prestataire)
10. Interface admin sur ordinateur.
11. Liste et filtres des incidents/signalements par date, zone, équipement, statut, score/niveau, auteur, service pilote, responsable.
12. Gestion admin des zones.
13. Gestion admin des points de contrôle.
14. Gestion admin des équipements.
15. Gestion admin des utilisateurs.
16. Gestion de l’attribution : service pilote, services supports, responsable, échéance.
17. Journalisation de traitement : commentaires, interventions, actions réalisées, durée, pièce remplacée, résultat, pièces jointes.
18. Envoi d’e-mails automatiques de notification :
    - incident créé
    - changement de statut
    - incident critique/prioritaire
    - notification au responsable / équipe concernée
    - relance si incident non traité dans un délai
19. Gestion des destinataires et des modèles d’e-mails.
20. Historique des notifications envoyées.
21. Possibilité d’ajouter un destinataire de copie ou un destinataire spécifique selon zone, équipement, niveau de priorité, service.

## 5. Flux métier détaillés

### 5.1 Ronde quotidienne

1. Ouvrir l’application avec le compte professionnel (ID Microsoft @fontevraud.fr).
2. Sélectionner ou recevoir le parcours du jour (TBD).
3. Démarrer la ronde avec horodatage.
4. Afficher les zones et points de contrôle dans l’ordre défini ou selon besoins du jour (TBD).
5. Pour chaque point, choisir : OK, Observation, Incident ou Non contrôlé (nomenclature à déterminer).
6. En cas d’anomalie, ajouter une note et, si utile, une ou plusieurs photos.
7. Associer l’équipement LOXYA lorsque le constat concerne un matériel précis.
8. Si une action est nécessaire, créer un incident et renseigner les trois notes de priorité.
9. Terminer la ronde et afficher un récapitulatif des contrôles, anomalies et éléments non vérifiés.

### 5.2 Traitement d’un incident

Protocole attendu et à affiner :
Nouveau → Qualifié → Affecté → En cours → En attente → Résolu → Clos.

Les statuts “En attente IT”, “En attente technique” et “En attente prestataire” peuvent être proposés comme motifs ou sous-statuts afin de conserver une trace lisible.

## 6. Données fonctionnelles cibles

Objet | Champs principaux
--- | ---
Zone | ID matériel, nom, ordre, active/inactive
Point de contrôle | ID matériel, zone, libellé, instruction, fréquence, ordre, criticité par défaut, équipement
Ronde | ID, type, agent, début, fin, statut, nombre de points, observations générales
Résultat | Ronde, point de contrôle, résultat, commentaire, photo, équipement, horodatage
Incident | ID, source, description, impact, urgence, risque, score, niveau, service pilote, support, responsable, statut, échéance
Intervention | Incident, date, intervenant, action, durée, pièce remplacée, résultat, pièce jointe
Équipement externe | Identifiant stable, libellé court, URL de fiche si disponible, date de synchronisation

## 7. Priorisation modulable

- Le score initial est calculé sur 15 points : Impact visiteur + Urgence + Risque d’aggravation.
- Chaque critère est noté de 1 à 5.
- Les seuils de niveau (ex : faible / modéré / élevé / critique) doivent être paramétrables sans modification du code.

## 8. Contraintes d’architecture

- Éviter toute complexité inutile.
- Pas de SPA complexe si non indispensable.
- Pas d’application mobile native dans un premier temps.
- Pas de microservices.
- Préserver une base technique propre pour intégrer plus tard :
  - un mode hors ligne plus complet
  - l’API GLPI
  - des statistiques et indicateurs
  - un système d’e-mails robuste et centralisé
- Les envois d’e-mails doivent être découplés de la logique métier principale, idéalement via un service dédié ou une file de messages en fonction du volume attendu.
- Privilégier des composants standards Symfony et des bonnes pratiques de sécurité.

## 9. Demandes à produire

1. Propose l’architecture applicative la plus pragmatique.
2. Justifie le choix entre application Symfony Twig classique, PWA, ou séparation API/front.
3. Propose un modèle de données détaillé.
4. Décris les entités Doctrine principales et leurs relations.
5. Propose une arborescence de projet Symfony claire.
6. Propose les écrans du parcours mobile (ronde) et du parcours admin (pilotage incidents).
7. Décris une stratégie réaliste de mode hors ligne :
   - MVP
   - V1.5
   - V2
8. Explique comment préparer proprement une future intégration GLPI.
9. Propose une roadmap de développement en itérations.
10. Donne les bundles Symfony utiles.
11. Propose une architecture pour les e-mails :
   - configuration SMTP ou fournisseur externe
   - templates Twig
   - gestion des événements
   - envoi asynchrone si nécessaire
   - logs et suivi des envois
12. Signale les points de vigilance : sécurité, photos, UX mobile, synchronisation, pilotage de ronde et envoi d’e-mails.

## 10. Format de réponse attendu

Je veux une réponse en français, structurée, concrète, orientée implémentation, avec des recommandations explicites de niveau professionnel.

Le document doit :
- être clair et exploitable pour un développeur Symfony
- proposer un MVP fiable sans sur-ingénierie
- mentionner les technologies recommandées
- détailler les entités, les flux métier et les bonnes pratiques de sécurité
- inclure le besoin d’e-mails comme partie intégrante du produit, sans le traiter comme une fonctionnalité annexe