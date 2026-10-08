# Plan — Slider, réalisations et contact HEMIP

- **Spécification et Design Read :** [homepage-pwa-contact-spec.md](../homepage-pwa-contact-spec.md)
- **État :** réalisé localement le 7 octobre 2026 ; aucune publication sur l’hébergement.
- **Mode :** extension du site existant, préservation de marque.

## Travaux réalisés

1. **Préparer les images du PDF utilisateur**
   - [x] Recomposer les huit photographies du PDF sans modifier le document original.
   - [x] Produire des dérivés JPEG optimisés dans `assets/img/`.

2. **Étoffer la page d’accueil**
   - [x] Ajouter le carrousel plein cadre et ses commandes accessibles dans `index.php`.
   - [x] Créer `assets/js/home-sections.js` et `assets/css/home-sections.css`.
   - [x] Ajouter « Nos réalisations » et quatre cartes basées sur les photos.
   - [x] Relier le CTA Formation au menu existant sur desktop et mobile.
   - [x] Corriger le hamburger mobile : glyphe système, bouton accessible 44 × 44 px, état ARIA et fermeture après choix d’un lien.
   - [x] Remplacer le favicon cassé par l’icône PWA locale.

3. **Réaliser le formulaire de contact**
   - [x] Ajouter `#contact` avec nom, prénom, e-mail, téléphone facultatif, objet, message et consentement.
   - [x] Ajouter `contact-submit.php` avec POST, CSRF, validation, honeypot, délai anti-abus et destinataire fixe.
   - [x] Ajouter un mode de journalisation qui omet le contenu et les données du contact, sans changer les appels historiques.
   - [x] Garder les formulaires et POST réseau uniquement ; aucune conservation ou reprise hors ligne.

4. **Maintenir le mode PWA**
   - [x] Mettre à jour les caches du service worker en `shell-v4` / `assets-v4` et précacher les ressources statiques utilisées.
   - [x] Vérifier que les routes PHP et les POST ne sont pas mis en cache ni rejoués.

5. **Documenter et valider**
   - [x] Écrire `README.md` sur le fonctionnement, la sécurité, la structure, le déploiement et le mode hors ligne.
   - [x] Vérifier syntaxe PHP et JavaScript, JSON du manifeste et existence des fichiers du précache.
   - [x] Tester les scénarios du handler sans SMTP ni message réel.
   - [x] Inspecter les rendus Edge desktop/mobile et corriger le hamburger mobile découvert à 0 × 0 px.

## Résultats des vérifications locales

- `php -l` sur `index.php`, `contact-submit.php` et `mail.php` : sans erreur.
- `node --check` sur `main.js`, `home-sections.js`, `sw.js` et le harness : sans erreur.
- Manifeste valide ; 20 ressources précachées, deux icônes du manifeste et huit photos attendues présents.
- Edge 1440 × 900 et 390 × 844 : images chargées, zéro erreur JavaScript/ressource locale, zéro débordement horizontal ; carrousel, pause/reprise, CTA Formation et Contact vérifiés.
- Mouvement réduit : l’autoplay reste à l’arrêt.
- Handler : GET 405, CSRF invalide, données invalides, honeypot et délai anti-abus donnent les états attendus. Aucun test ne tente un envoi SMTP.

## Sécurité, confidentialité et limite de déploiement

Le flux local page publique → POST de contact → session/SMTP → adresse publique de l’école est protégé contre CSRF, abus simple, altération d’en-têtes, divulgation dans les journaux et rejeu hors ligne. Aucune pièce jointe, persistance en base ou file d’attente n’est ajoutée.

**Prérequis avant une mise en production :** l’envoi dépend du SMTP déjà configuré. Une valeur d’identification SMTP se trouve dans la configuration locale ; la migrer vers un secret d’environnement et la renouveler côté fournisseur avant de considérer l’installation sûre. Aucun courriel réel n’a été envoyé, et aucun fichier n’a été transféré vers `hemip.great-site.net`.

Le rendu `file://` ne valide pas l’installation PWA, le scope du service worker ou le SMTP de l’hébergement. Ces essais restent à faire sur l’origine HTTPS après déploiement contrôlé.
