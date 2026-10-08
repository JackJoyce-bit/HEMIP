# Spécification — Slider, réalisations et contact HEMIP

**État :** spécification approuvée et intégrée localement le 7 octobre 2026 ; déploiement de production non effectué.

## Intention

Faire évoluer la page d’accueil existante du site HEMIP vers une vitrine plus vivante et exploitable comme PWA, sans la refondre : carrousel photo de héros, section « Nos réalisations », formulaire de contact transmis à l’école, et documentation `README.md`.

## Contexte vérifié

- Le site cible est un site PHP natif dans `config/`, avec son menu partagé, `assets/css/style.css`, `assets/js/main.js`, et une PWA existante (`manifest.json`, `sw.js`, `pwa.js`).
- La page d’accueil contient déjà une section localisation et un pied de page avec les coordonnées de l’établissement. Le lien public de contact est `hemilaperceeinformation@gmail.com`.
- Le répertoire contient une fonction d’envoi SMTP existante (`mail.php`). La nouvelle fonctionnalité la réutilise ; elle ne copie ni n’affiche aucun secret.
- Le PDF utilisateur `Capture d’écran 2026-09-05 213040.pdf` a livré huit photographies. Visuels identifiés : façade et enseigne HEMIP (4160 × 3120), cérémonie, atelier mécanique, cours/pratique informatique, rassemblement d’étudiants, groupe devant l’école et autres moments de vie. Les originaux ont été extraits sans modifier le PDF ; les dérivés JPEG optimisés sont nommés à part.

## Périmètre fonctionnel

### Accueil / carrousel

- Conserver le titre, le texte et les CTA existants ; faire pointer « Nous contacter » vers `#contact` plutôt que vers l’ancre formations inexistante sur cette page.
- Ajouter à l’arrière-plan du héros un diaporama plein cadre avec quatre vraies photos du PDF : façade HEMIP, cérémonie, atelier mécanique, pratique informatique.
- Transition en fondu discrète ; rotation lente (environ 6 s), boutons précédent/suivant, repères par diapositive et commande pause/reprise.
- Fonctionnement clavier, noms accessibles, texte suffisamment contrasté sur les photos, contrôle d’animation et absence d’autodéfilement sous `prefers-reduced-motion`.

### « Nos réalisations »

- Ajouter une section de quatre cartes illustrées, avec des légendes strictement descriptives des scènes visibles : moments de cérémonie, pratique technique, apprentissage en groupe, rencontre institutionnelle/vie de l’école.
- Ne pas inventer de dates, de taux d’insertion, de récompenses ni de résultats non attestés par les images.
- Ajouter un lien de menu vers `#realisations`, sans retirer les entrées existantes.

### Contact

- Ajouter une section `#contact` à la page d’accueil ; le lien Contact y mène, et l’ancre actuelle `#footer` demeure afin de préserver la compatibilité.
- Champs requis : nom, prénom, adresse e-mail, objet, message et accord de traitement pour répondre ; champ téléphone facultatif.
- Destinataire fixe : `hemilaperceeinformation@gmail.com`, repris du contact public existant. Aucun destinataire ne vient des champs soumis.
- Réutiliser le transport SMTP déjà présent, avec corps en texte brut et adresse fournie visible dans le message pour faciliter la réponse.
- Contrôles serveur : POST seulement, CSRF, validation des types et longueurs, adresse vérifiée, piège anti-robot, limitation des soumissions répétées par session et retour accessible du résultat.
- Ne pas stocker le formulaire en base, ne pas mettre les POST en cache ou en file d’attente hors ligne. En mode sans connexion, expliquer qu’il faut se reconnecter avant l’envoi.
- Adapter l’enregistreur mail avec une option de confidentialité qui omet le corps et les données du formulaire des journaux ; conserver le comportement existant pour les autres appels.

### PWA et documentation

- Mettre à jour/versionner le cache du service worker et précacher les nouvelles feuilles JS/CSS et images locales du site.
- Garder les endpoints PHP et les POST hors du cache/service worker ; aucune soumission ne doit être rejouée après reconnexion.
- Créer `README.md` dans `config/` : carte des pages et composants, fonctionnement du menu/carrousel/PWA/formulaire, dépendances mail, déploiement, tests et limites hors ligne.

## Design Read

- **Mode :** extension / préservation ; même identité, même navigation principale et mêmes parcours existants.
- **Préserver :** logo, encadrement bleu, en-tête sombre, texte institutionnel, liens téléphone/WhatsApp, adresse de contact, pages PHP, formulaire d’inscription, comportement PWA déjà en place.
- **Améliorer :** force narrative des photos, démonstration de la vie et des pratiques de l’école, accès au formulaire, robustesse mobile et états de retour.
- **Écarter :** refonte générale, fausses statistiques, dépendances de slider externes, collecte persistante d’informations de contact et stockage hors ligne des messages.
- **Contrats protégés :** routes des pages et du formulaire d’inscription, noms/champs existants, ancres `#footer`, identité graphique et chemins de déploiement.
- **Risque principal :** l’envoi dépend de la configuration SMTP déjà installée sur le serveur. La livraison locale n’envoie aucun courriel réel et ne prouve donc pas à elle seule le SMTP de production.
- **Retour arrière :** retirer le balisage ajouté à `index.php`, les deux nouveaux modules JS/CSS, le handler contact, les images dérivées et leurs entrées du service worker ; les pages et données métier actuelles restent intactes.

## Quatre décisions de composition

1. **Rôle narratif :** le héros accueille et oriente ; réalisations apporte la preuve par l’image ; contact transforme l’intérêt en demande.
2. **Distance de lecture :** téléphone d’abord, avec gros titres, commandes tactiles et disposition une colonne ; ordinateur en secondaire avec zones larges et cartes multi-colonnes.
3. **Température visuelle :** institutionnelle, humaine et énergique ; photographie réelle, voile bleu marine lisible, accents HEMIP plutôt qu’un thème neuf.
4. **Capacité d’information :** un message et deux CTA visibles par héros, quatre cartes de réalisations au maximum, formulaire regroupé et texte de confidentialité concis.

## Système visuel proposé

| Élément | Décision |
| --- | --- |
| Couleurs | Réemploi des tokens existants : `--primary` (#0b4ea2), `--primary-dark` (#073570), `--primary-light` (#eaf3ff), `--dark` (#071a33), `--text` (#4b5563), `--light` (#f5f8fc), `--white`, `--border` (#e5eaf1). |
| Typographie | Arial/sans-serif existante ; pas de police distante ajoutée. Titres au moyen de la même échelle fluide déjà utilisée. |
| Grille et espacement | Conteneur existant 1200 px ; rythme de 8 px ; 4 cartes sur grand écran, 2 sur tablette, 1 sur téléphone. |
| Surfaces | Rayon existant 20 px sur panneaux, champs tactiles de rayon 12 px, bordures discrètes, ombres légères. |
| Images | Photographies fournies, redimensionnées seulement si nécessaire et encodées JPEG optimisé sans altérer les originaux ; cadrage `cover`, texte du héros placé sur le côté le moins chargé et gradient sombre. |
| Mouvement | Fondu ~650 ms et rotation ~6 s, arrêt manuel disponible ; aucun autoplay avec réduction des animations demandée par le navigateur. |

## Curseurs de design (sur 10)

- Fidélité à la marque : **10** — identité et tokens existants conservés.
- Variance visuelle : **2** — évolution ciblée, sans rebranding.
- Intensité du mouvement : **3** — fondu léger, commandes explicites et réduction respectée.
- Densité de contenu : **4** — peu d’éléments, hiérarchie directe.
- Dépendance aux images : **9** — les photos originales fournies constituent la matière principale.
