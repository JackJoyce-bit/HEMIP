# PWA HEMIP — notes d’intégration

Le site conserve son backend PHP actuel. Le manifeste, l’invite d’installation et le service worker n’ajoutent aucun service externe. L’intégration décrite ci-dessous est locale ; `hemip.great-site.net` n’a pas été modifié.

## Pages et cache

`index.php` reste l’accueil PHP. `realisations.html` est une page publique statique ; elle est récupérée réseau d’abord et n’est conservée qu’après une réponse réussie. Elle peut être affichée hors ligne après avoir été visitée avec succès. Les raccourcis du manifeste donnent accès à Inscription, Actualités, Réalisations et Contact.

La page `contact.php` dépend de la session et de son jeton CSRF ; toutes les pages PHP, l’authentification, les pages d’administration, l’inscription et les téléversements restent réseau uniquement. Les requêtes POST ne sont ni interceptées ni stockées. Aucun formulaire n’est mis en file d’attente ou renvoyé automatiquement à la reconnexion. Hors connexion, `offline.html` explique la limite ; un message interrompu doit être soumis de nouveau lorsque la connexion revient.

Le service worker est versionné `shell-v7`, `pages-v2` et `assets-v7`. Il précache les fichiers statiques répertoriés, dont `assets/css/independent-pages.css`, mais pas les pages PHP ni les soumissions. Les pages de filière restent dans la liste explicite de documents HTML publics ; aucune route PHP n’y est ajoutée.

## Responsive

Les styles communs ciblent les largeurs de 320 px à grands écrans. Sur téléphone, les photos du carrousel d’accueil restent entières (`object-fit: contain`) et le texte est situé sous l’image. Le menu mobile possède une taille tactile minimale et peut défiler dans la fenêtre lorsque le menu est ouvert.

## Déploiement et vérification

La publication n’est pas effectuée. Copier les fichiers modifiés dans la racine web HTTPS qui sert l’accueil PHP, sans mettre l’ancien `index.html` à la place de `index.php`. Conserver l’endpoint `contact-submit.php` et ses gardes. Vérifier ensuite le manifeste, les icônes, le scope réel du service worker, la création des sessions PHP et les routes depuis le domaine de production. Un test SMTP réel est séparé des tests de code et doit être autorisé par l’école.

Les vérifications statiques locales confirment le JSON du manifeste, les ressources du shell, les syntaxes et l’exclusion des pages PHP/POST du cache. La matrice navigateur a produit 50 captures ; les tests ciblés ont ensuite passé 14 combinaisons et 3 contrôles critiques, sans débordement horizontal. Sur téléphone, les quatre images du hero sont chargées entières et les commandes restent dans la photo. La lecture de la vidéo de la page Actualités n’est pas validée par le rendu `file://` ; son fichier existe et la lecture reste à vérifier via HTTP. Le rendu local ne valide ni l’installation PWA sur HTTPS ni le SMTP de l’hébergement.

## Référence visuelle de l’icône

Références consultées : [site HEMIP — La Percée](https://hemip-la-percee.vercel.app/), [manifeste](https://hemip-la-percee.vercel.app/manifest.json) et [icône 192 px](https://hemip-la-percee.vercel.app/icons/pwa-icon-192.png). Les icônes locales reprennent le fond gris clair, le disque blanc et le logo HEMIP centré.
