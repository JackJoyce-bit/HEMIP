# Plan complémentaire — pages Réalisations et Contact, responsive HEMIP

> **Pour l’exécution :** étapes suivies dans cette session ; aucune publication ni aucun commit demandé.

**Objectif :** sortir Réalisations et Contact de l’accueil, présenter sur une page dédiée l’essai expérimental documenté dans le PDF, et rendre la navigation et les contenus utilisables sur les téléphones, tablettes et écrans larges.

**Architecture :** `index.php` reste la page d’accueil avec le carrousel ; `realisations.html` devient une page descriptive statique, précachable après visite ; `contact.php` reprend le formulaire protégé, alimenté par le même endpoint SMTP et laissé hors cache. Les liens des navigations sont harmonisés sur ces routes. Les correctifs responsive sont ajoutés au CSS commun, avec un traitement mobile `object-fit: contain` pour voir les quatre photos du hero en entier.

**Technologies :** PHP existant, HTML/CSS/JavaScript natifs, service worker actuel ; aucune dépendance ou service externe ajouté.

**Références :** `homepage-pwa-contact-spec.md`, `README.md` et notes de lecture PDF dans le workspace `.work/hemip-split-responsive/pdf-source-notes.md`.

## Contraintes globales

- Conserver les champs, méthodes, protections et destinataire fixe du formulaire de contact déjà approuvé.
- Présenter `25 kg → 10 kg` et le rendement moyen indiqué d’environ `56 %` comme données du **lot expérimental** cité dans le PDF, jamais comme résultat industriel ou commercial.
- Garder les pages PHP et les POST réseau uniquement ; ne pas mettre en cache les données de session ou soumissions.
- Préserver marque, logo, identité visuelle, routes métier, menu Formation, inscriptions, actualités et administration.
- Rendre les images du carrousel entières sur téléphone, sans recadrage `cover` ; accepter des marges de fond si le format photo l’exige.

## Points de revue

- Écran étroit (320–360 px) : pas de débordement, champs lisibles, navigation accessible.
- Hero mobile : les quatre photos conservent leur ratio et sont entièrement visibles.
- Page Contact : CSRF, honeypot, délai et état PRG toujours actifs ; aucun test ne contacte le SMTP.
- Service worker : `realisations.html` est la seule nouvelle page publique autorisée au cache ; `contact.php` reste réseau uniquement.
- Pages métier : les formulaires d’inscription, l’authentification et les pages existantes restent inchangés hors leurs liens de navigation.

---

### Étape 1 — Détacher la page Contact

- [x] Créer `contact.php` avec en-tête/menu, coordonnées, formulaire, jeton CSRF et états de retour.
- [x] Retirer le bloc de formulaire de `index.php` et diriger les CTA Contact vers `contact.php`.
- [x] Modifier `contact-submit.php` pour rediriger les retours vers `contact.php#contact`.
- [x] Vérifier les noms/ordre des champs, le destinataire et l’absence de tout envoi pendant les tests.

### Étape 2 — Créer la page Réalisations

- [x] Créer `realisations.html` avec navigation commune et une seule réalisation issue du PDF : valorisation expérimentale d’os de bétail.
- [x] Mettre en évidence le processus (traitement thermique, refroidissement, broyage), le lot de 25 kg, les 10 kg de poudre finale et les ~56 % indiqués, avec la réserve explicite de statut expérimental.
- [x] Retirer la galerie de photos génériques de l’accueil et mettre à jour les légendes/documents concernés.

### Étape 3 — Navigation et PWA

- [x] Remplacer dans les pages actives les liens Contact et Réalisations par les routes dédiées et rendre les deux entrées accessibles depuis les menus.
- [x] Autoriser `realisations.html` dans le cache des pages publiques et versionner le service worker ; maintenir `contact.php` et tous les POST hors cache.
- [x] Mettre à jour le README avec les rôles des pages et les consignes de déploiement.

### Étape 4 — Responsive et images du hero

- [x] Compléter le CSS commun pour les écrans de 320 px à grands écrans, et ajuster les mises en page rigides détectées.
- [x] En mobile, afficher toute la photo du hero avec son ratio d’origine (`contain`) et disposer le texte/CTA de manière à ne pas recouvrir l’image.
- [x] Conserver les points de rupture/tablette, les cibles tactiles et l’indicateur du menu accessible.

### Étape 5 — Vérification finale

- [x] Vérifier `php -l`, `node --check`, manifest JSON, précache et liens de pages.
- [x] Exécuter les tests du handler sans SMTP.
- [x] Faire l’essai navigateur multi-pages/multi-tailles : 50 captures dans la matrice initiale, puis 14 combinaisons ciblées et 3 contrôles critiques ; aucun débordement horizontal détecté et les quatre photos du hero sont entières sur téléphone.
- [x] Vérifier la navigation mobile, les chemins des liens, le rendu du formulaire et les gardes du handler sans soumettre de message ni joindre SMTP.
- [x] Confirmer que le déploiement sur `hemip.great-site.net` n’a pas été effectué ; la lecture de la vidéo d’Actualités reste à confirmer via HTTP, car la prévisualisation `file://` ne valide pas son streaming.
