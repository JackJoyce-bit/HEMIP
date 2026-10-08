# PWA HEMIP — notes d’intégration

Le site utilise son backend PHP actuel ; le manifeste, le bouton d’installation et le service worker n’ajoutent aucun service externe.

## Cache et fonctionnement hors ligne

Le service worker actif dans le code local utilise `hemip-pwa-shell-v4` et `hemip-pwa-assets-v4`. Il précache les ressources publiques statiques, dont les nouvelles feuilles de style, le carrousel, les photos locales et les icônes HEMIP. Les pages HTML publiques explicitement listées sont récupérées réseau d’abord et ne sont conservées qu’après une réponse réussie.

Les routes `.php`, l’authentification, les espaces d’administration, l’inscription, les téléversements et toutes les requêtes autres que GET restent réseau uniquement. Les données de formulaire ne sont ni mises en cache, ni envoyées à la reconnexion. Un formulaire interrompu hors connexion doit être rempli et soumis de nouveau une fois la connexion rétablie ; l’écran `offline.html` explique cette limite.

## Déploiement et vérification

L’intégration est locale : aucun fichier n’a été transféré sur `hemip.great-site.net`. Pour publier, copier les fichiers listés dans `README.md` vers la racine HTTPS PHP qui sert actuellement le site, sans remplacer l’accueil par l’ancien `index.html`. Vérifier ensuite le manifeste, les icônes, le scope et l’installation du service worker depuis le domaine de production. Après une mise à jour d’un service worker déjà installé, actualiser une fois en ligne.

La revue locale a vérifié le JSON du manifeste, les 20 fichiers précachés et les règles excluant les routes PHP/POST. Le rendu `file://` ne permet pas de valider l’installation PWA réelle ni le scope HTTPS. Aucun courriel réel n’a été envoyé ; le SMTP doit être vérifié séparément après migration et renouvellement du secret de configuration.

## Référence visuelle de l’icône

Références consultées : [site HEMIP — La Percée](https://hemip-la-percee.vercel.app/), [manifeste](https://hemip-la-percee.vercel.app/manifest.json) et [icône 192 px](https://hemip-la-percee.vercel.app/icons/pwa-icon-192.png). Les icônes locales reprennent le fond gris clair, le disque blanc et le logo HEMIP centré ; aucun téléchargement ni appel externe n’est requis pour les servir.

