# HEMIP — fonctionnement du site

## Page réellement servie

La racine publique `https://hemip.great-site.net/` affiche l’accueil PHP de la Haute École de Management et d’Ingénierie la Percée. La racine et `/index.php` ont renvoyé la même page lors de l’inspection ; `/index.html` a renvoyé 404. Dans le dossier local, `index.html` est une ancienne copie statique : **ne pas la téléverser à la racine du site** et ne pas la prendre pour la page active.

Le site est en PHP natif, sans étape de compilation. Le dossier `config/` correspond à la racine web lorsqu’il est déployé.

## Principaux fichiers

| Fichier ou dossier | Rôle |
| --- | --- |
| `index.php` | Accueil dynamique, navigation, carrousel, réalisations, contact et pied de page. |
| `assets/css/style.css` | Styles historiques du site ; inclut désormais la taille minimale du hamburger mobile. |
| `assets/css/home-sections.css` | Styles responsive du carrousel, des cartes de réalisations et du formulaire. |
| `assets/js/main.js` | Menu hamburger sans dépendance à une police d’icônes, états ARIA, dropdowns et apparitions au défilement. |
| `assets/js/home-sections.js` | Carrousel et ouverture du menu Formation par le CTA de l’accueil. |
| `assets/img/hemip-*.jpg` | Huit dérivés JPEG optimisés des photos fournies, sans modification du PDF source. |
| `assets/icons/hemip-180.png`, `hemip-192.png`, `hemip-512.png` | Icônes PWA, dont le favicon local. |
| `contact-submit.php` | Endpoint POST ; valide puis transmet au destinataire scolaire fixe. |
| `mail.php` / `config-mail.php` | Transport e-mail existant et sa configuration. `config-mail.php` contient une valeur sensible : ne pas l’exposer dans Git, une page, un ticket ou un dépôt public. |
| `manifest.json`, `sw.js`, `offline.html`, `assets/js/pwa.js` | Installation, stratégies de cache et page de repli hors ligne. |
| `inscription.php`, `traitement.php`, `actualite.php`, `login.php`, `dashboard.php` | Parcours métier existants, non remplacés par cette évolution. |

## Accueil et carrousel

Le hero conserve ses textes, chiffres et appels à l’action. Le bouton « Découvrir nos formations » ouvre le menu Formation existant sur ordinateur et sur mobile ; sur téléphone, il ouvre aussi le panneau hamburger. Le hamburger reste visible sans le CDN d’icônes, possède une zone tactile de 44 × 44 px et expose son état à la technologie d’assistance. Le menu mobile se referme après le choix d’un lien.

Les quatre photos du carrousel sont `hemip-campus.jpg`, `hemip-ceremonie.jpg`, `hemip-atelier.jpg` et `hemip-informatique.jpg`. Le fondu dure environ 650 ms et la rotation automatique est espacée de 6,5 secondes. Les flèches, repères et la commande pause/reprise permettent la navigation manuelle. Le focus clavier arrête la rotation ; `prefers-reduced-motion: reduce` empêche son démarrage automatique. Les médias du carrousel sont locaux.

La section « Nos réalisations » affiche quatre photos avec des légendes descriptives, sans inventer de date, récompense ni résultat non vérifié.

## Formulaire de contact

Le lien Contact ouvre `#contact`. Les liens Contact des autres pages renvoient vers `index.php#contact` ; l’ancre historique `#footer` reste en place pour assurer la compatibilité.

Le formulaire recueille le prénom, le nom, l’adresse e-mail, un téléphone facultatif, l’objet, le message et le consentement demandé. Il envoie un POST à `contact-submit.php`. Le serveur :

1. refuse les méthodes autres que POST et désactive le cache pour la réponse ;
2. vérifie un jeton CSRF lié à la session ;
3. valide type, format et longueur des champs ;
4. vérifie un champ piège anti-robot et impose 45 secondes entre tentatives d’une même session ;
5. construit un message texte brut à destination fixe `hemilaperceeinformation@gmail.com` ;
6. réutilise `envoyer_email()` dans `mail.php`, puis renvoie l’utilisateur vers `index.php#contact` avec un état accessible.

Le message n’est pas conservé en base ni réinjecté dans le formulaire après redirection. Pour cet endpoint, le journal mail omet le corps, les coordonnées et les détails SMTP ; les autres appels historiques à `envoyer_email()` conservent leur comportement par défaut.

Aucun courriel réel n’a été envoyé pendant les tests locaux. Le succès d’un futur envoi dépend du SMTP configuré sur l’hébergement. Une valeur d’identification SMTP existe dans la configuration locale : avant la production, la transférer vers un secret d’environnement et la renouveler auprès du fournisseur. Cette opération n’est pas comprise dans l’intégration locale.

## PWA et mode hors ligne

Le service worker est en version `shell-v4` / `assets-v4`. Il précache le shell public, les nouvelles feuilles de style, les scripts, les icônes et les photos locales. Les pages HTML publiques autorisées sont récupérées réseau d’abord puis peuvent être relues depuis leur cache après une visite réussie.

Les pages PHP, les sessions, l’administration, l’inscription et les formulaires restent **réseau uniquement**. Les POST ne sont ni interceptés, ni mis en cache, ni conservés dans une file, ni rejoués à la reconnexion. Hors connexion, le navigateur reçoit `offline.html` ; le formulaire doit être soumis de nouveau une fois la connexion rétablie. La PWA nécessite HTTPS sur le domaine de production.

## Déploiement

Le travail est intégré **localement** ; le site `hemip.great-site.net` n’a pas été modifié ni publié. Pour mettre cette version en ligne, transférer les changements dans la racine web PHP active en préservant les fichiers métier existants :

- `index.php`, `contact-submit.php`, `mail.php` et `sw.js` ;
- `manifest.json` et `offline.html` ;
- `assets/css/style.css`, `assets/css/home-sections.css` ;
- `assets/js/main.js`, `assets/js/home-sections.js`, ainsi que les fichiers PWA existants référencés ;
- les huit `assets/img/hemip-*.jpg` référencées ;
- `assets/icons/hemip-180.png`, `hemip-192.png` et `hemip-512.png`.

Ne pas remplacer la page active par `index.html` et ne pas publier le secret de `config-mail.php`. Conserver le propriétaire et les permissions adaptés à l’hébergement. Après transfert, vérifier HTTPS, le manifeste, le scope du service worker et la création de session PHP. Si un service worker plus ancien était installé, actualiser une fois en ligne pour recevoir le nouveau cache. Un test SMTP réel devra être fait séparément, avec une adresse de test autorisée par l’école.

## Vérifications locales effectuées — 7 octobre 2026

- `php -l` : `index.php`, `contact-submit.php` et `mail.php` sans erreur de syntaxe.
- `node --check` : `main.js`, `home-sections.js`, `sw.js` et le harness de rendu sans erreur ; manifeste JSON valide.
- Précache : 20 ressources présentes ; deux icônes du manifeste et les huit photos attendues présentes ; aucune route PHP sensible dans la liste précachée.
- Revue navigateur Edge depuis un rendu local PHP, en 1440 × 900 et 390 × 844 : aucune erreur JavaScript, aucune ressource locale manquante, aucun débordement horizontal ; commandes du carrousel, pause/reprise, CTA Formation, formulaire, ancre Contact et préférence de mouvement réduit vérifiés.
- Cinq scénarios du handler exécutés sans SMTP : GET refusé (405), CSRF invalide, données invalides, honeypot et délai anti-abus. Aucun message réel n’a été envoyé.

Le rendu `file://` sert uniquement à la vérification visuelle : il ne valide ni l’installation PWA, ni le scope réel du service worker, ni le SMTP de l’hébergement. Ces contrôles nécessitent le domaine HTTPS après déploiement.

## Commandes utiles

Depuis la racine `config/`, avec PHP et Node installés :

```powershell
C:\xampp\php\php.exe -l .\index.php
C:\xampp\php\php.exe -l .\contact-submit.php
C:\xampp\php\php.exe -l .\mail.php
node --check .\assets\js\main.js
node --check .\assets\js\home-sections.js
node --check .\sw.js
node -e "JSON.parse(require('fs').readFileSync('./manifest.json','utf8')); console.log('manifest.json OK')"
```

Les tests d’intégration doivent continuer à utiliser uniquement des valeurs synthétiques et des chemins qui s’arrêtent avant `envoyer_email()`. Ne jamais envoyer un courriel réel par un test automatique.
