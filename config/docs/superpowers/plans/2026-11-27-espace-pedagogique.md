# Plan d’implémentation — Espace pédagogique HEMIP

> **Pour les agents d’implémentation :** exécuter les tâches une à une, avec vérification indépendante à chaque étape. Ce document constitue un plan, pas une autorisation de modifier le code ou la base.

**Objectif :** ajouter progressivement les espaces SAC et enseignant, leurs affectations pédagogiques, supports, présences, emplois du temps, annonces et notifications, tout en limitant chaque accès aux données autorisées.

**Architecture :** conserver PHP natif, PDO, MariaDB et l’organisation générale du site. Ajouter une migration SQL distincte du dump existant, des helpers ciblés pour l’authentification/CSRF et des routes dédiées ; réutiliser la présentation de l’espace étudiant sans refonte globale. Séparer comptes enseignants, pièces pédagogiques et documents d’inscription. Toutes les lectures et mutations pédagogiques doivent vérifier l’affectation ou l’inscription au niveau serveur.

**Technologies :** PHP et PDO déjà présents, MariaDB/MySQL, HTML/CSS/JavaScript natifs, service worker existant. Ne pas introduire de framework. Une bibliothèque PHP Web Push maintenue sera nécessaire pour les notifications navigateur ; sa sélection et son approbation constituent un point d’arrêt avant ajout de dépendance.

**Spécification :** conception validée par Exaucé Ngoyi dans la conversation du 27 novembre 2026 ; règles détaillées dans les sections « Contraintes globales » et « Points d’arrêt avant implémentation » ci-dessous.

## Contraintes globales

- Comptes formateurs réservés à la création par un SuperAdmin ; aucune inscription publique d’enseignant.
- Rôles administratifs conservés et étendus à `SAC` et `DAAC` ; le rôle choisi à l’écran de connexion doit être vérifié en base et ne confère jamais les droits à lui seul.
- Le SAC maintient les modules par filière/niveau, attribue enseignants, classes et modules, et gère le planning.
- Une classe est la combinaison filière + niveau + année académique ; niveaux : L1, L2, L3, Master 1, Master 2 ; première année : 2026–2027 ; les années futures doivent être ajoutables.
- Une liste d’étudiants ne comprend que les inscriptions validées correspondant exactement à la filière, au niveau et à l’année de la classe.
- Un enseignant ne consulte et ne modifie que les classes/modules qui lui sont attribués ; un étudiant ne reçoit que les contenus destinés à son inscription/classe/module.
- Supports acceptés : `.txt`, `.pptx`, `.doc`, `.docx`, `.xls`, `.xlsx`, `.pdf` ; limite applicative : 25 Mo par fichier. Validation du contenu côté serveur obligatoire ; aucun nom de fichier fourni par l’utilisateur ne devient un chemin de stockage.
- Les supports et annonces sont publiés directement à la classe concernée, sans modération préalable ; les annonces sont à toute la classe, sans messagerie individuelle/réponses privées en v1.
- Présence par séance, états `présent`, `absent`, `retard` ; correction permise à l’enseignant avec journal append-only des modifications.
- Le SAC saisit un emploi du temps structuré (jour, heures, salle, module) et peut y joindre des fichiers consultables/téléchargeables.
- Notifications en application et notifications navigateur après consentement, pour enseignants et étudiants. Les notifications en application restent le canal de référence si le navigateur refuse les push.
- Notes et flux d’autorisation DAAC exclus de la v1. Pour une phase ultérieure seulement : une autorisation DAAC porte sur une note précise et autorise une unique modification.
- Aucun test ne doit envoyer un e-mail réel ou utiliser des données de production. Les tests d’intégration se font sur une base locale de test isolée avec fixtures.
- Limiter les changements aux fichiers listés par tâche ; ne pas refondre l’application entière, ne pas mettre à jour le dump historique `config/tp_php.sql` à la place d’une migration.
- Avant déploiement, le propriétaire doit révoquer/renouveler le secret SMTP exposé dans l’historique Git. Ne pas recopier la valeur dans le plan, un ticket, un log ou un commit.

## Points d’arrêt avant implémentation

1. **Provisionnement des accès enseignants :** décider comment le SuperAdmin remet les premiers identifiants (recommandation : lien de définition de mot de passe à usage unique, sans mot de passe initial en clair). Le SMTP actuellement exposé doit d’abord être renouvelé avant de s’appuyer sur lui.
2. **Web Push :** présenter la bibliothèque PHP maintenue, sa licence, son intégration et les fichiers de dépendances requis ; obtenir l’accord avant d’ajouter cette dépendance ou de créer les clés VAPID.
3. **Configuration PHP des uploads :** vérifier les limites `upload_max_filesize`/`post_max_size` de XAMPP et de l’hébergement ; la limite applicative reste 25 Mo. Demander l’accord avant de modifier une configuration serveur hors dépôt.
4. La phase Notes/DAAC est reportée et exige une spécification complémentaire ; aucun écran ni schéma de notes ne doit être ajouté à la v1.

## Structure de fichiers proposée

| Fichier | Responsabilité |
|---|---|
| `config/migrations/2026_11_27_pedagogie.sql` | Tables, relations, index et extension des rôles administratifs, livrés comme migration incrémentale. |
| `config/includes/role-guard.php` | Fonctions étroites pour exiger un rôle admin, SAC, enseignant ou étudiant et revalider le compte/role en base. |
| `config/includes/csrf.php` | Jeton CSRF réutilisable sur les actions POST. |
| `config/includes/pedagogie.php` | Requêtes ciblées pour classe, affectation, inscription validée et contrôles d’accès pédagogiques. |
| `config/enseignants.php` | Création/activation/désactivation des comptes enseignants, SuperAdmin uniquement. |
| `config/login.php`, `config/deconnexion.php` | Choix du parcours/rôle vérifié et destruction correcte de toutes les sessions concernées. |
| `config/sac.php` | Accueil et navigation SAC. |
| `config/modules.php`, `config/affectations.php` | Gestion SAC des modules et affectations aux enseignants/classes. |
| `config/emploi-du-temps.php` | Saisie et consultation des événements structurés et fichiers de planning. |
| `config/enseignant.php` | Accueil enseignant et navigation reprenant l’espace étudiant. |
| `config/presences.php`, `config/presence-action.php` | Saisie/correction contrôlée des présences et journal des corrections. |
| `config/supports.php`, `config/support-action.php`, `config/fichier-pedagogique.php` | Dépôt, liste et téléchargement autorisé des supports pédagogiques. |
| `config/annonces.php`, `config/annonce-action.php` | Publication et affichage des annonces à la classe. |
| `config/notifications.php`, `config/push-subscription.php` | Centre de notifications et gestion du consentement/abonnement push. |
| `config/etudiant.php` | Ajout ciblé des rubriques Supports, Annonces, Emploi du temps, Notifications et Présences si retenu côté étudiant. |
| `config/assets/css/espace-pedagogique.css` | Styles propres aux nouveaux espaces, cohérents avec la palette existante. |
| `config/assets/js/pedagogie.js`, `config/sw.js` | Interactions de navigation et réception d’événements Push ; pas de logique d’autorisation côté client. |
| `config/uploads/pedagogique/.htaccess` | Interdiction d’accès HTTP direct aux fichiers pédagogiques ; livraison via contrôleur autorisé. |
| `config/tests/pedagogie/` | Fixtures et tests PHP/HTTP sans framework, exécutés seulement sur une base de test isolée. |

Cette structure est une proposition limitée au périmètre pédagogique. Toute déviation qui touche des fichiers supplémentaires devra être justifiée et présentée avant modification.

## Plan par tâches

### Tâche 1 — Modèle de données pédagogique et migration sûre

**Fichiers :** créer `config/migrations/2026_11_27_pedagogie.sql`, `config/tests/pedagogie/fixtures.sql` et un test de migration sous `config/tests/pedagogie/`. Ne pas modifier `config/tp_php.sql`.

**Interfaces produites :** tables relationnelles pour enseignants, classes annuelles, modules filière/niveau, affectations enseignant-classe-module ; contraintes d’unicité/foreign keys permettant plusieurs modules et années. La migration doit étendre le rôle administratif à `SAC` et `DAAC` sans supprimer les rôles existants. Prévoir ensuite les tables de planning, supports, séances/présences/historique, annonces/notifications dans les tâches qui les introduisent, pas une table monolithique.

- [ ] Écrire un test de migration vérifiant, sur une base vide de test, l’installation des nouvelles tables et les contraintes uniques attendues.
- [ ] Importer les fixtures minimales existantes (filières, compte admin synthétique) dans la base isolée et tester que la migration conserve les rôles et données présents.
- [ ] Écrire les tables de base : professeur avec identifiant distinct, classe unique par filière/niveau/année, module par filière/niveau, affectation unique par enseignant/classe/module ; indexer les colonnes de recherche et FK.
- [ ] Ajouter `SAC`/`DAAC` aux valeurs admises par le schéma admin sans accorder ces rôles par défaut.
- [ ] Ajouter des cas négatifs prouvant qu’une affectation ne peut référencer une classe/module incompatibles (contrôle de service), et qu’un doublon de classe ou d’affectation est refusé.
- [ ] Vérifier par relecture que l’export de schéma ne contient ni secret, ni donnée réelle, ni changement aux tables d’inscription.

**Vérification :** appliquer la migration uniquement à la base dédiée de test ; exécuter `C:\xampp\php\php.exe -l .\tests\pedagogie\migration-test.php` si le runner est PHP. Résultat attendu : toutes les assertions de schéma passent ; aucune opération sur la base applicative courante ou distante.

### Tâche 2 — Authentification et administration des rôles/comptes

**Fichiers :** créer `config/includes/role-guard.php`, `config/includes/csrf.php`, `config/enseignants.php` et les tests associés ; modifier de façon ciblée `config/login.php`, `config/deconnexion.php`, `config/ajouter.php`, `config/administrateurs.php`.

**Consommés :** tables et rôles de la tâche 1. **Produits :** connexion enseignant séparée, parcours admin SAC/DAAC/Administrateur/SuperAdmin, gardes d’accès à réutiliser dans toutes les routes.

- [ ] Ajouter les tests d’accès refusé pour rôle sélectionné différent du rôle réel, compte désactivé, session d’un ancien rôle, accès direct par URL et jeton CSRF absent/invalide.
- [ ] Modifier `login.php` avec un sélecteur du parcours admin et de son rôle ; l’authentification vérifie en base l’association email, hash du mot de passe et rôle demandé avant de créer la session.
- [ ] Ajouter un parcours enseignant qui consulte la table dédiée et utilise `password_verify`; régénérer l’identifiant de session à l’ouverture et séparer strictement les clés de session étudiant/admin/enseignant.
- [ ] Ajouter des helpers de garde serveur, de non-cache pour pages privées et de CSRF pour les POST ; les gardes relisent le compte et son rôle en base.
- [ ] Étendre la gestion SuperAdmin pour créer, suspendre et réactiver les enseignants sans inscription publique ; hash obligatoire des mots de passe.
- [ ] Étendre la création/répartition des rôles admin seulement à `SAC` et `DAAC`; empêcher le SuperAdmin de perdre son dernier accès SuperAdmin par erreur.
- [ ] Mettre à jour la déconnexion pour invalider toutes les identités de session et détruire/regénérer proprement le cookie/session.
- [ ] Garder le mécanisme de remise initiale des identifiants en attente du point d’arrêt 1 ; ne pas envoyer de vrais messages pendant les tests.

**Vérification :** tests HTTP avec comptes synthétiques dans la base dédiée ; `C:\xampp\php\php.exe -l` sur chaque PHP modifié. Résultat attendu : les quatre rôles admin atteignent uniquement leur route autorisée, le compte enseignant n’accède pas aux routes admin/SAC et le parcours étudiant existant reste fonctionnel.

### Tâche 3 — Gestion SAC des modules, classes et affectations

**Fichiers :** créer `config/sac.php`, `config/modules.php`, `config/affectations.php`, `config/includes/pedagogie.php`, tests et feuille `config/assets/css/espace-pedagogique.css`.

**Consommés :** garde SAC/CSRF de la tâche 2 et référentiels de la tâche 1. **Produits :** affectations authentifiées utilisables par l’espace enseignant.

- [ ] Tester le refus d’un enseignant/étudiant/Administrateur sans rôle SAC pour chaque route SAC et chaque POST.
- [ ] Tester les niveaux autorisés L1, L2, L3, Master 1, Master 2, l’année `2026-2027` et l’ajout d’une année ultérieure valide.
- [ ] Créer l’interface SAC pour créer/mettre à jour/archiver un module rattaché à une filière et un niveau, avec validation des doublons.
- [ ] Créer ou retrouver la classe correspondant à la combinaison filière, niveau, année académique ; ne jamais dupliquer une classe annuelle.
- [ ] Permettre au SAC d’affecter un enseignant à une ou plusieurs combinaisons classe/module et révoquer une affectation ; vérifier que module et classe ont la même filière/niveau.
- [ ] Ajouter confirmations et retours PRG, échappement des sorties HTML, paramètres PDO et CSRF sur toutes les mutations.
- [ ] Réutiliser palette et principes de navigation existants ; ne pas modifier les styles généraux du site.

**Vérification :** suite d’intégration sur fixtures : affectation multiple permise, incompatibilité module/classe refusée, doublon refusé, utilisateur non-SAC refusé. Lint PHP et contrôle navigateur bureau/mobile des seules nouvelles pages SAC.

### Tâche 4 — Accueil enseignant et listes de classes/étudiants

**Fichiers :** créer `config/enseignant.php`, modifier ciblément `config/etudiant.php`, compléter `config/includes/pedagogie.php` et les tests.

**Consommés :** sessions et affectations des tâches 1–3. **Produits :** classe sélectionnée et liste canonique d’étudiants autorisés.

- [ ] Créer des tests pour comptes sans affectation, plusieurs affectations, plusieurs modules dans une classe, années différentes, inscription en attente/refusée et inscriptions validées de périodes différentes.
- [ ] Construire la page d’arrivée de l’enseignant sous forme « Mes classes » avec filière, niveau, année et modules, en reprenant barre supérieure/panneau profil de l’espace étudiant.
- [ ] Afficher les étudiants seulement quand une inscription est `Validée` et correspond exactement à la filière, au niveau et à l’année de la classe ; éviter les doublons d’étudiants issus de jointures multiples.
- [ ] Protéger chaque route et chaque requête de détail contre l’altération d’un identifiant de classe/module dans l’URL.
- [ ] Ajouter le profil en lecture seule : nom, prénom, e-mail, téléphone, photo, modules attribués ; conserver la possibilité future de changer le mot de passe séparée des données de profil.
- [ ] Ajouter les rubriques étudiant nécessaires aux fonctions livrées dans les tâches suivantes sans retirer les rubriques existantes.

**Vérification :** tests de cloisonnement multi-classe et multi-année ; vérifier par scénario qu’un enseignant ne peut pas lire la liste d’une autre affectation en changeant un identifiant. Lint PHP et parcours visuel mobile/desktop des deux espaces.

### Tâche 5 — Emploi du temps structuré SAC/enseignant/étudiant

**Fichiers :** créer `config/emploi-du-temps.php`, ajouter tables/contraintes de planning à une migration dédiée versionnée, tests ; modifier les vues enseignant/étudiant de façon ciblée.

**Consommés :** référentiels classe/module/enseignant. **Produits :** événements de planning lisibles par les acteurs affectés.

- [ ] Tester dates/jours, heures début/fin, salle, module, classe et affectation ; rejeter heure de fin antérieure ou égale au début.
- [ ] Définir et tester les conflits horaires : chevauchement pour un même enseignant et pour une même classe ; afficher une erreur explicite sans enregistrer l’entrée en conflit.
- [ ] Permettre au SAC de créer, modifier et annuler un élément structuré ; les mutations exigent rôle SAC, POST et CSRF.
- [ ] Permettre l’ajout facultatif de fichiers de planning via le même stockage protégé et le même contrôleur de téléchargement sécurisé que les supports.
- [ ] Afficher le planning applicable à l’enseignant et à la classe étudiant ; fournir une action de téléchargement/impression sans permettre de modification au formateur.
- [ ] Créer un événement de notification lors d’une nouvelle publication ou d’une modification pertinente.

**Vérification :** tests de conflits/visibilité et accès croisés ; revue visuelle du planning mobile et desktop ; PHP lint.

### Tâche 6 — Dépôt et diffusion sécurisés des supports

**Fichiers :** créer `config/supports.php`, `config/support-action.php`, `config/fichier-pedagogique.php`, `config/uploads/pedagogique/.htaccess`, tests et compléter vues enseignant/étudiant.

**Consommés :** affectations et inscriptions validées. **Produits :** supports liés à une classe/module/année, listés aux seuls destinataires admissibles.

- [ ] Tests négatifs : extension interdite, extension double, type MIME/contenu incompatible, taille supérieure à 25 Mo, upload partiel, enseignant non affecté, étudiant autre classe/année/module, chemin manipulé et identifiant d’un autre fichier.
- [ ] Valider taille, extension autorisée, type MIME réel et structure attendue du fichier ; utiliser un nom généré imprévisible et conserver le nom original comme métadonnée échappée.
- [ ] Écrire le fichier dans un emplacement privé non servi directement ; interdire l’exécution et l’accès HTTP direct dans le dossier d’upload.
- [ ] Enregistrer les métadonnées avec une transaction ; supprimer le fichier temporaire si l’écriture SQL échoue.
- [ ] Autoriser le dépôt seulement après vérification serveur que l’enseignant est affecté au module et à la classe choisis.
- [ ] Afficher et servir un support uniquement à l’enseignant propriétaire/affecté et aux étudiants inscrits/validés sur la classe et l’année concernées.
- [ ] Ajouter aux pages étudiants la rubrique Supports avec nom, format, date et téléchargement protégé ; publier directement, sans validation préalable.
- [ ] Vérifier les limites PHP `upload_max_filesize` et `post_max_size` sur l’environnement choisi ; toute modification de `php.ini` ou configuration d’hébergement nécessite une approbation distincte.

**Vérification :** tests d’upload sur fichiers synthétiques et contrôles d’accès via HTTP en base isolée ; aucun accès direct au chemin de stockage ; vérifier que les pièces d’inscription ne sont pas affectées.

### Tâche 7 — Présences par séance avec corrections journalisées

**Fichiers :** créer `config/presences.php`, `config/presence-action.php`, tables de séance/présence/historique dans une migration dédiée, tests.

**Consommés :** affectations, classes, étudiants de la tâche 4. **Produits :** historique de présence et de corrections.

- [ ] Tester une séance assignée, un étudiant hors classe, des états inconnus, la répétition d’une ligne et une séance appartenant à une autre affectation.
- [ ] Permettre à l’enseignant de créer une séance pour un module/classe qui lui est attribué puis de saisir uniquement `présent`, `absent` ou `retard` pour les étudiants validés de l’année.
- [ ] Assurer une ligne unique par séance/étudiant ; utiliser transaction et protections contre double envoi afin d’éviter les états partiels.
- [ ] Permettre à l’enseignant autorisé de corriger une présence ; conserver chaque ancienne/nouvelle valeur, auteur, horodatage et motif facultatif dans un journal append-only.
- [ ] Présenter l’historique des corrections aux rôles autorisés ; ne jamais modifier ou supprimer les lignes historiques précédentes.
- [ ] Faire apparaître aux étudiants leurs présences selon la règle de visibilité qui sera confirmée avant cette sous-étape ; à défaut, ne pas exposer de détail individuel avant décision.

**Vérification :** tests de rôle, de classe, d’année et d’intégrité de l’historique ; tests répétés de correction montrant que chaque changement génère une nouvelle entrée. PHP lint et essai dans l’interface.

### Tâche 8 — Annonces de classe et notifications internes

**Fichiers :** créer `config/annonces.php`, `config/annonce-action.php`, `config/notifications.php`, table d’annonces et notification dans une migration dédiée, tests ; modifier de façon ciblée les rubriques enseignant/étudiant.

**Consommés :** identités et classe/affectations. **Produits :** publication de classe et centre interne de notifications enseignant/étudiant.

- [ ] Tester qu’un enseignant publie seulement pour une classe qui lui est attribuée, qu’un étudiant ne reçoit que les annonces de sa classe/année et qu’aucune route de messagerie privée n’existe.
- [ ] Autoriser publication, modification/suppression selon une règle à valider, et lecture d’annonces destinées à toute la classe ; aucune réponse privée d’étudiant en v1.
- [ ] Créer une notification persistante dans l’application, avec destinataire, type, référence, date et état lu/non lu ; chaque destinataire est calculé côté serveur à partir de l’inscription valide.
- [ ] Produire les notifications pour nouveau support, annonce, affectation et changement de planning, en évitant les destinataires hors classe et les doublons.
- [ ] Ajouter un centre de notifications et un indicateur non lu aux deux espaces ; échapper tout texte utilisateur et éviter l’injection HTML dans les vues JavaScript.
- [ ] Ajouter une politique de conservation/nettoyage et indexer les lectures par utilisateur/date ; conserver seulement les informations nécessaires.

**Vérification :** tests de ciblage avec plusieurs classes et années, tests d’état lu/non lu, vérification qu’un compte non concerné n’obtient pas une notification par manipulation d’identifiant ; PHP/JS lint.

### Tâche 9 — Notifications navigateur (Web Push)

**Fichiers :** uniquement après point d’arrêt 2, ajouter les fichiers de dépendance autorisés, `config/push-subscription.php`, table d’abonnements, adaptation ciblée de `config/sw.js`, tests.

**Consommés :** centre interne de notifications de la tâche 8. **Produits :** abonnements navigateur et livraison Push best-effort, avec le centre interne comme source fiable.

- [ ] Présenter avant codage le paquet PHP retenu, licence, origine, compatibilité PHP et stratégie de mise à jour ; demander approbation de la dépendance et de la création/protection des clés VAPID.
- [ ] Tester refus de permission, navigateur incompatible, abonnement expiré, requête d’abonnement non authentifiée et résiliation.
- [ ] Demander le consentement depuis une action explicite utilisateur ; ne jamais afficher la demande de permission au chargement automatique.
- [ ] Stocker un abonnement avec propriétaire et clés protégées ; permettre révocation ; aucun secret VAPID dans le dépôt.
- [ ] Ajouter le gestionnaire `push` au service worker, avec notification générique ne divulguant pas de données personnelles, et lien interne après clic.
- [ ] Envoyer les Push à partir des événements de notification interne ; traiter les endpoints expirés et limiter la fréquence ; ne jamais considérer l’envoi Push comme preuve de lecture.

**Vérification :** tests unitaires sans sortie réseau réelle, puis essai navigateur sur HTTPS/staging avec abonnements de test consentis ; prouver que notification interne fonctionne lorsque Push est désactivé. `node --check .\sw.js` et `php -l` sur les routes modifiées.

### Tâche 10 — Revue intégrée, sécurité et préparation au déploiement

**Fichiers :** tests des tâches, `config/README.md` pour l’installation/rollback pédagogique et documentation de déploiement dédiée ; ne pas publier/déployer sans autorisation séparée.

- [ ] Exécuter la migration et tous les scénarios de bout en bout sur une base locale dédiée avec fixtures ; vérifier rollback/reprise documentés avant toute utilisation réelle.
- [ ] Tester les matrices de rôles et l’IDOR sur chaque route de consultation, téléchargement et mutation ; CSRF sur chaque POST ; XSS sur champs d’annonce, noms de supports et profil.
- [ ] Vérifier transactions, contraintes uniques, désactivation d’un compte, révocation d’affectation, suppression de classe/module référencés et absence de cascade destructive non intentionnelle.
- [ ] Vérifier tailles/types upload, restrictions du stockage, sécurité des sessions, réponses sans cache privé et absence de secrets dans les réponses/logs.
- [ ] Exécuter le lint PHP sur tous les fichiers PHP non-vendor, `node --check` sur JavaScript/service worker et `JSON.parse` des manifestes ; exécuter les tests HTTP et le parcours responsive à 320/390/768/1280/1920 px.
- [ ] Réconcilier les mises à jour de README, les besoins PHP/Apache et les sauvegardes de base ; rédiger un guide d’installation avec ordre migration, smoke tests et rollback.
- [ ] Vérifier que le diff ne contient que les fichiers autorisés et que `git status` est compris avant toute livraison ; ne pas committer, pousser ou déployer sans demande distincte.

**Critères de sortie :** aucun test sur données réelles ; aucun cas d’accès inter-classe ; aucune mutation POST sans CSRF ; toutes les fonctionnalités v1 passent sur fixture isolée ; les notifications internes restent utilisables si Push indisponible ; aucun fichier de l’espace étudiant ou du site public n’est refondu sans nécessité documentée.

## Hors périmètre v1 — Notes et autorisation DAAC

Ne pas créer de table, route ou écran de notes pendant les tâches ci-dessus. Une phase ultérieure devra spécifier l’échelle, devoirs/examens, coefficients/périodes, visibilité aux étudiants et corrections. La règle déjà validée est : une autorisation DAAC vise une note unique précise et est consommée par une seule modification ; une nouvelle modification requiert une nouvelle demande. Cette phase fera l’objet d’un plan et d’une approbation séparés.

## Revue du plan

- **Couverture :** les exigences validées (rôles, affectations, années/niveaux, étudiants validés, supports privés, planning, présences modifiables avec historique, annonces, notifications deux canaux) sont attribuées aux tâches 1 à 9 ; notes et workflow DAAC sont explicitement hors v1.
- **Séparation :** schéma, rôles, SAC, listes, planning, supports, présences, annonces et Push sont livrables par étapes ; les nouveaux espaces réutilisent l’apparence existante sans refonte globale.
- **Protection des données :** les vérifications multi-classe/année, identité de rôle, téléchargements protégés et fixtures synthétiques sont présentes dans les tâches concernées.
- **Décisions encore requises :** remise initiale des identifiants enseignants, choix/approbation de la bibliothèque Web Push, configuration uploads au-delà des limites PHP actuelles, et visibilité des présences côté étudiant.
- **Aucune tâche d’implémentation n’est autorisée par ce document à elle seule.** Après revue de ce plan, demander l’accord explicite d’Exaucé avant toute modification des sources ou du schéma.
