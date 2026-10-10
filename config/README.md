# HEMIP — fonctionnement du site

## Structure et page active

Le site est en PHP natif, sans étape de compilation. Le dossier `config/` correspond à la racine web lorsqu’il est déployé. La page d’accueil active est `index.php` ; `index.html` est une ancienne copie statique et ne doit pas remplacer la page PHP à la racine.

| Page ou fichier | Fonction |
| --- | --- |
| `index.php` | Accueil : présentation, formations, carrousel photo, partenaires, localisation et footer. Aucun formulaire Contact ni contenu Réalisations n’est intégré à cette page ; le menu mène aux pages autonomes. |
| `realisations.html` | Page autonome organisée par chaînes de valorisation : culture de palmiers et biodiesel, recyclage de plastiques et collecte des hydrocarbures, puis essai documenté sur les os. |
| `contact.php` | Page autonome du formulaire et des coordonnées. Le jeton CSRF et l’état du formulaire dépendent de la session PHP. |
| `contact-submit.php` | Endpoint POST ; valide et transmet le message au destinataire scolaire fixe, puis revient vers `contact.php#contact`. |
| `assets/css/style.css` | Styles communs du site, navigation, sections historiques et règles responsive partagées. |
| `assets/css/hemip-theme.css` | Tokens centralisés de la palette HEMIP, nuances dérivées et canaux RGB pour les transparences. |
| `assets/css/home-sections.css` | Carrousel, états et styles des sections d’accueil ; sur téléphone, les photos du hero utilisent `object-fit: contain`. |
| `assets/css/independent-pages.css` | Mise en page des pages dédiées Contact et Réalisations. |
| `assets/js/main.js` | Menu mobile, dropdowns, états ARIA et apparitions au défilement. |
| `assets/js/home-sections.js` | Rotation et commandes clavier du carrousel ; CTA Formation de l’accueil. |
| `manifest.json`, `sw.js`, `offline.html`, `assets/js/pwa.js` | Installation PWA, cache des ressources publiques et écran hors ligne. |
| `inscription.php`, `traitement.php`, `actualite.php`, `login.php`, `dashboard.php` | Parcours métier PHP existants, qui restent distincts. |
| `mail.php`, `config-mail.php` | Transport e-mail existant et configuration. `config-mail.php` contient une valeur sensible : ne pas l’exposer dans une page, un ticket ou un dépôt public. |

Les menus et appels à l’action relient Accueil, Nos réalisations et Contact à ces routes dédiées. `index.html` est une ancienne copie statique ; `index.php` est la page d’accueil active. Les formulaires d’inscription et d’administration restent sur leurs routes habituelles.

## Palette de couleurs HEMIP

Toutes les pages (vitrine, filières, formulaires, administration et mode hors ligne) partagent `assets/css/hemip-theme.css` :

| Token | Valeur | Usage |
| --- | --- | --- |
| `--hemip-primary` | `#1094D7` | Bleu de marque, surfaces et accents visuels. |
| `--hemip-accent` | `#D12C25` | Accent rouge et erreurs. |
| `--hemip-text` | `#1D1E1E` | Texte principal et surfaces sombres. |
| `--hemip-bg` | `#FDFDFD` | Fond clair et texte inversé. |
| `--hemip-neutral` | `#928D8F` | Bordures et détails neutres. |

Les nuances fonctionnelles (`--hemip-primary-strong`, `--hemip-accent-strong`, surfaces pâles et `--hemip-muted`) restent dérivées de cette palette. Le bleu exact `#1094D7` offre un contraste d’environ 3,31:1 sur le fond clair ; la nuance foncée destinée aux liens et petits textes dépasse 4,5:1, et le texte principal foncé sur le bleu exact atteint environ 4,96:1. Les boutons bleus à texte clair utilisent la nuance foncée pour rester lisibles. Les transparences réutilisent les canaux RGB des tokens.

## Accueil, carrousel et responsive

Le hero conserve son contenu institutionnel. Les quatre photos locales sont `hemip-campus.jpg`, `hemip-ceremonie.jpg`, `hemip-atelier.jpg` et `hemip-informatique.jpg`. Le fondu dure environ 650 ms et la rotation automatique est espacée de 6,5 secondes. Les flèches, repères et le bouton pause/reprise autorisent la navigation manuelle ; `prefers-reduced-motion: reduce` empêche le démarrage automatique.

À une largeur mobile (jusqu’à 640 px), l’image est affichée entière dans un cadre de ratio adapté à l’écran ; le texte et les CTA passent sous la photo pour ne pas la masquer. Des marges de fond peuvent rester visibles, plutôt que de couper la photo. Les styles communs utilisent des tailles fluides et des grilles à colonnes rétractables pour les écrans étroits, tablettes et grands écrans.

La navigation au téléphone utilise un bouton hamburger accessible, un panneau pouvant défiler lorsque sa hauteur dépasse l’écran et des sous-menus adaptés au toucher. Le CSS ne force plus une largeur minimale de 320 px ; les grilles et formulaires se rétractent, tandis que les tableaux de filière conservent une largeur lisible et défilent dans leur propre conteneur sur petit écran. Une matrice visuelle multi-viewport reste à confirmer dans un navigateur local accessible avant publication.

## Page « Nos réalisations »

La page dédiée présente d’abord deux chaînes de valorisation illustrées par quatre vidéos : la culture de palmiers nains et la production de biodiesel, puis le traitement de plastiques et la collecte des hydrocarbures. Elle conserve ensuite un exemple précis du dossier de présentation HEMIP (pages 19–20) : l’étude expérimentale de valorisation d’os de bétail. Celle-ci résume le traitement thermique, le refroidissement et le broyage, et rapporte, pour le lot présenté, 25 kg d’os frais, 10 kg de poudre finale ainsi que le rendement moyen d’environ 56 % indiqué pour la production de CaO à partir de l’hydroxyapatite.

Ces valeurs décrivent le lot expérimental mentionné dans le document. Elles ne signifient ni une production industrielle, ni une adoption commerciale déjà réalisée. L’objectif cité est d’étudier une substitution à la chaux vive pour le génie civil et d’autres applications ; le texte de la page conserve explicitement cette réserve.

Quatre vidéos fournies par HEMIP sont intégrées à la page Réalisations, avec un résumé technique chacune. Elles sont regroupées en deux parcours : « Du palmier nain au biodiesel » et « Des déchets plastiques aux hydrocarbures ». Les lecteurs utilisent directement `https://www.youtube-nocookie.com/embed/` et ne créent pas de lien vers `youtu.be` ou une page YouTube. L’attribut `referrerpolicy="strict-origin-when-cross-origin"` transmet seulement l’origine du site au lecteur ; YouTube indique qu’un Referer HTTP est nécessaire à la lecture (erreur 153 lorsque celui-ci manque). Source : [aide officielle YouTube sur l’intégration](https://support.google.com/youtube/answer/171780?hl=fr). Le lecteur requiert Internet ; une vidéo soumise à une limite d’âge ou dont l’intégration est désactivée peut malgré tout refuser la lecture ou renvoyer vers YouTube.

## Page Contact et traitement du formulaire

`contact.php` détache les coordonnées et le formulaire de l’accueil. Le formulaire conserve les champs métier convenus : prénom, nom, adresse e-mail, téléphone facultatif, objet, message et consentement pour répondre à la demande. Des placeholders donnent des exemples de saisie dans tous les champs textuels visibles ; les libellés restent présents et le formulaire poste à `contact-submit.php`.

L’endpoint refuse les méthodes autres que POST, désactive le cache de sa réponse, vérifie un jeton CSRF lié à la session, valide formats et longueurs, vérifie le champ piège anti-robot et impose 45 secondes entre les tentatives d’une même session. Il envoie un message texte brut uniquement à `hemilaperceeinformation@gmail.com` via la fonction SMTP existante ; le destinataire ne peut pas être fourni par l’utilisateur. Après traitement, le serveur fait une redirection 303 vers `contact.php#contact` avec un état accessible.

Le contenu du formulaire n’est pas enregistré en base ni remis automatiquement dans les champs après redirection. Pour cet endpoint, le journal de mail omet le corps, les coordonnées et les détails SMTP. Aucun formulaire n’est stocké ou envoyé hors connexion ; une soumission nécessite Internet. **Aucun courriel réel n’a été envoyé pendant les vérifications locales.** Le fonctionnement SMTP de l’hébergement devra être contrôlé séparément.

Avant mise en production, transférer la configuration SMTP vers un secret d’environnement et renouveler la valeur d’identification exposée dans la configuration locale. Ne pas publier `config-mail.php` avec un secret dans un dépôt public.

### Configuration SMTP et secrets

Depuis la sécurisation, `config-mail.php` ne contient **plus aucun identifiant** : il ne définit que des valeurs par défaut sans secret et se contente de les surcharger, dans cet ordre de priorité :

1. variables d’environnement `HEMIP_SMTP_*` (`HEMIP_SMTP_HOTE`, `HEMIP_SMTP_PORT`, `HEMIP_SMTP_SECURITE`, `HEMIP_SMTP_UTILISATEUR`, `HEMIP_SMTP_MOT_DE_PASSE`, `HEMIP_SMTP_EXPEDITEUR`, `HEMIP_SMTP_EXPEDITEUR_NOM`, `HEMIP_SMTP_VERIFIER_CERTIFICAT`) ;
2. fichier local **`config-mail.local.php`**, ignoré par Git (voir `.gitignore`), qui contient les identifiants réels.

En local, les identifiants SMTP vivent donc dans `config-mail.local.php` et ne sont jamais versionnés. En production, préférer les variables d’environnement ; à défaut, déposer `config-mail.local.php` à la main sur le serveur. **Ne jamais committer un fichier contenant un mot de passe.**

Le dossier `uploads/` (documents déposés par les candidats) est protégé par un `.htaccess` qui refuse tout accès HTTP direct : les pièces ne sont consultables que via `document.php`, réservé aux administrateurs connectés. Le dossier `logs/` applique la même protection.

## PWA et comportement hors ligne

Le service worker utilise `shell-v12`, `pages-v7` et `assets-v12`. Il précache le shell et les ressources statiques répertoriées, dont `home-sections.css`, `independent-pages.css`, `pwa.css` et `hemip-theme.css`. Les pages HTML publiques autorisées, dont `realisations.html`, sont récupérées réseau d’abord et peuvent être servies depuis le cache après une visite réussie. Les politiques de confidentialité, cookies et d’utilisation s’ouvrent depuis des liens du footer dans des panneaux de lecture, sans créer de routes de page. Les lecteurs YouTube ne sont jamais précachés ; ils ne fonctionnent pas hors connexion.

Toutes les routes PHP, notamment `contact.php`, restent **réseau uniquement** pour éviter de conserver du HTML dépendant d’une session. Les requêtes POST ne sont ni interceptées, ni mises en cache, ni placées en file, ni rejouées à la reconnexion. Hors connexion, le service worker sert `offline.html`. La PWA installable nécessite HTTPS sur le domaine public.

### Consentement, intégrations et installation

`assets/js/pwa.js` affiche un bandeau avec « Tout accepter » et « Tout refuser ». Le choix facultatif est mémorisé 180 jours dans le cookie de première partie `hemip_optional_media`. Le cookie de session PHP nécessaire aux formulaires n’est pas désactivé par le refus. Après « Tout accepter », les quatre vidéos et la carte Google se chargent automatiquement dans leurs cadres intégrés ; il n’y a pas de bouton préalable pour chaque média. Les vidéos utilisent `youtube-nocookie.com`, `strict-origin-when-cross-origin` et un iframe sandbox sans permission de navigation supérieure ni de popup. Cela garde la fenêtre HEMIP sur place ; le contenu reste néanmoins servi par YouTube et HEMIP ne peut garantir contre une erreur produite par Google, le navigateur, un blocage réseau ou une restriction d’intégration. Les politiques s’ouvrent dans un dialogue accessible déclenché depuis les liens du pied de page. Une invitation facultative à installer l’application apparaît après 90 secondes, avec les choix Installer / Plus tard ; un refus masque l’invitation pendant 30 jours.

La note de confidentialité renvoie au texte de la loi congolaise n° 29-2019 du 10 octobre 2019 portant protection des données à caractère personnel, consultable sur [ILO/NATLEX](https://natlex.ilo.org/dyn/natlex2/natlex2/files/download/110224/COG-110224.pdf). Les contenus légaux intégrés sont informatifs et doivent être validés par HEMIP avant publication, en particulier pour les durées de conservation, les responsables/prestataires exacts et les bases juridiques de chaque traitement.

## Vérification locale

Les contrôles doivent être exécutés depuis `config/` avec PHP et Node disponibles. Le test du handler utilise des cas synthétiques dont le chemin s’arrête avant le transport SMTP ; aucun test automatisé ne doit envoyer de courriel réel.

Les contrôles statiques exécutés pour cette mise à jour ont passé : lint des 20 fichiers PHP, syntaxe Node de `pwa.js` et `sw.js`, manifeste JSON, présence unique des quatre identifiants vidéo, absence d’iframe YouTube chargée avant consentement, chargement de `pwa.css` sur chaque page utilisant `pwa.js`, réglages de référent/sandbox, liens de politique au footer, suppression des pages légales autonomes et cohérence des versions de cache. Le badge du hero a été vérifié au niveau de son bloc HTML (le texte de présentation distinct du footer est conservé). **Le contrôle visuel réel des pages et la lecture des vidéos n’ont pas pu être exécutés** : aucun serveur HTTP local ne répondait à `http://localhost/HEMIP-team/config/realisations.html`. L’absence de redirection ou d’erreur dans un navigateur devra donc être confirmée dès qu’une URL locale ou de test sera disponible. Aucun e-mail réel n’a été envoyé.

```powershell
C:\xampp\php\php.exe -l .\index.php
C:\xampp\php\php.exe -l .\contact.php
C:\xampp\php\php.exe -l .\contact-submit.php
C:\xampp\php\php.exe -l .\mail.php
node --check .\assets\js\main.js
node --check .\assets\js\home-sections.js
node --check .\assets\js\pwa.js
node --check .\sw.js
node -e "JSON.parse(require('fs').readFileSync('./manifest.json','utf8')); console.log('manifest.json OK')"
```

Pour répéter la revue lors de futures modifications, utiliser au minimum 280, 320, 360, 390, 430, 768, 820, 1024, 1280, 1440 et 1920 pixels sur Accueil, Réalisations, Contact et des pages de formation représentatives. Contrôler le défilement horizontal, le menu au toucher et que toutes les photos du hero restent intégralement visibles sur mobile.

## Déploiement

L’intégration est locale : `https://hemip.great-site.net/` n’a pas été modifié ni publié. Pour déployer, transférer dans la racine web PHP active les nouveaux fichiers et toutes les ressources modifiées, notamment :

- les pages `index.php`, `realisations.html`, `contact.php` et le handler `contact-submit.php` ;
- les pages de navigation modifiées `actualite.html`, `actualite.php`, les pages de filières HTML, `a-propos.php`, `inscription.php` et `login.php` ;
- `manifest.json`, `sw.js`, `offline.html` et les feuilles/styles/scripts PWA référencés ;
- l’ensemble du répertoire `assets/css/` (22 feuilles, dont le thème commun et les styles des pages publiques, filières, formulaires et administration) ;
- `assets/js/main.js`, `assets/js/home-sections.js`, `assets/js/pwa.js` ;
- les fichiers d’icônes et les huit photos optimisées déjà utilisées par le carrousel.

Ne pas téléverser l’ancien `index.html` à la racine ni remplacer l’accueil PHP. Préserver les pages métier et la configuration SMTP ; ne pas téléverser un secret vers un dépôt public. Après copie, vérifier HTTPS, les routes, les icônes, le manifeste, le scope du service worker et la création des sessions PHP. Une PWA possédant l’ancien worker doit actualiser une fois en ligne afin de recevoir les nouveaux caches. Confirmer le SMTP séparément avec une adresse de test autorisée par l’école.
