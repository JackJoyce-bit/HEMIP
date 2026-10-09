# Spécification — accueil, réalisations et contact HEMIP

**État :** intégration locale vérifiée le 8 octobre 2026 ; pages autonomes et contenu PDF validés ; publication sur le site de production non effectuée.

## Intention

Conserver l’identité et les parcours du site HEMIP, avec un accueil centré sur la présentation et son carrousel, une page Réalisations distincte qui ne retient qu’un exemple documenté dans le PDF fourni, et une page Contact séparée. Rendre l’ensemble lisible et opérable sur mobile, tablette et bureau.

## Architecture des pages

| Route | Contenu et comportement |
| --- | --- |
| `index.php` | Accueil : navigation, carrousel de quatre photographies, présentation/formation, partenaires, localisation et footer. Le formulaire et la galerie Réalisations n’y sont plus intégrés. |
| `realisations.html` | Page statique autonome sur l’essai expérimental de valorisation des os de bétail décrit dans le dossier HEMIP. |
| `contact.php` | Coordonnées et formulaire autonome. Le formulaire réutilise la session PHP et l’endpoint `contact-submit.php`. |
| `contact-submit.php` | POST protégé ; redirection 303 vers `contact.php#contact`. |

Les menus des pages actives et les raccourcis PWA pointent vers les routes dédiées. L’ancien `index.html` demeure une copie historique ; il ne remplace pas l’accueil PHP déployé.

## Accueil et carrousel

- Conserver le titre, le texte institutionnel et les CTA existants, ainsi que les sections partenaires/localisation.
- Le carrousel utilise les photos locales `hemip-campus.jpg`, `hemip-ceremonie.jpg`, `hemip-atelier.jpg` et `hemip-informatique.jpg`, avec fondu discret, rotation lente, précédent/suivant, repères et pause/reprise.
- Commandes accessibles au clavier ; la préférence `prefers-reduced-motion` arrête la lecture automatique.
- Sur téléphone, les photos sont intégralement visibles, ratio original préservé avec `object-fit: contain`. Un fond peut apparaître autour de l’image ; le texte et les CTA sont placés après l’image pour ne pas la recouvrir.

## Page « Nos réalisations » — source documentaire

La page présente un seul exemple, afin de rester concise et de ne pas confondre objectif et résultat de terrain : l’essai expérimental de transformation d’os de bétail décrit dans le dossier de présentation HEMIP fourni (pages 19–20).

Le texte rapporte uniquement les données indiquées pour le lot : `25 kg` d’os frais, `10 kg` de poudre finale et un rendement moyen annoncé d’environ `56 %` pour la production de CaO à partir de l’hydroxyapatite. Il résume le traitement thermique, le refroidissement et le broyage mentionnés dans le dossier.

Le statut reste explicitement **expérimental**. Le dossier exprime un objectif d’étude d’un matériau susceptible de remplacer la chaux vive, notamment pour le génie civil et des applications industrielles ; le site n’affirme ni production industrielle ni adoption commerciale déjà réalisées. Aucune photographie sans lien vérifiable avec l’expérience n’est utilisée comme illustration de ce projet.

## Page Contact et protections

Le formulaire indépendant conserve les champs validés : prénom, nom, e-mail, téléphone facultatif, objet, message et consentement pour répondre à la demande. Le destinataire reste fixe : `hemilaperceeinformation@gmail.com`.

Le handler refuse les méthodes autres que POST, utilise CSRF lié à la session, validation des formats/longueurs, honeypot anti-robot, limitation d’envoi par session, retour PRG accessible et désactivation du cache sur la réponse. Il réutilise le transport SMTP existant ; il ne stocke pas le message, n’accepte pas de destinataire fourni par l’utilisateur et n’envoie rien hors connexion. La journalisation pour le formulaire omet corps et coordonnées. Les tests locaux ne doivent pas joindre SMTP ni envoyer de courriel réel.

## Responsive et accessibilité

- Vérifier les viewports 320, 360, 390, 430, 768, 820, 1024, 1280, 1440 et 1920 px.
- Corriger les débordements horizontaux, conserver les contenus dans des grilles fluides et garder des commandes mobiles accessibles.
- Le panneau de navigation mobile peut défiler en hauteur ; ses états ARIA suivent son ouverture/fermeture.
- Les images du hero utilisent `contain` sur mobile ; elles ne sont pas assombries par le voile desktop et les commandes restent au bord de la photo.
- Tester Accueil, Réalisations, Contact et des pages de formation représentatives.

La matrice locale a produit 50 captures, puis 14 combinaisons ciblées et 3 contrôles critiques ont passé après les ajustements. Aucune largeur contrôlée ne présente de débordement horizontal. La vidéo d’Actualités n’a pas été évaluée en lecture, car la prévisualisation `file://` ne valide pas le streaming du fichier local ; vérifier ce média au moyen du serveur HTTP.

## PWA / hors ligne

Le service worker utilise les versions `shell-v7`, `pages-v2` et `assets-v7`. Il précache ses ressources statiques et autorise `realisations.html` dans les pages anonymes récupérées réseau d’abord, conservées seulement après succès. La page peut alors être disponible hors ligne si elle a été visitée auparavant.

Les routes PHP, dont `contact.php`, restent réseau uniquement, car leur rendu dépend de sessions et d’états de formulaire. Les POST ne sont jamais mis en cache ou rejoués. Hors connexion, le formulaire doit être soumis de nouveau après rétablissement du réseau. La PWA installable exige HTTPS en production.

## Design Read

- **Mode :** extension/préservation ; aucune refonte générale.
- **Préserver :** logo, identité bleue, en-tête et menu, formation, inscription, actualités, administration, localisation, PWA existante et contact public de l’école.
- **Améliorer :** accès explicite aux pages, détail factuel d’un seul essai, pleine visibilité des photos de hero sur mobile et fluidité des mises en page.
- **Écarter :** inventer des réalisations, afficher le lot comme résultat industriel, ajouter un service de formulaire externe, stocker des messages hors ligne ou ajouter des dépendances de slider.
- **Risque à vérifier séparément :** l’envoi de messages dépend du SMTP configuré sur l’hébergement. Le travail local ne prouve pas son fonctionnement et ne change pas le site de production.

## Système visuel

Réemploi des couleurs et de la typographie HEMIP existantes, titres fluides, surfaces sobres, cartes à rayons modérés, grilles qui se replient sans largeur rigide, focus visibles et mouvement réduit respecté. Les images du slider conservent leur format complet sur écran étroit, avec fond letterbox si nécessaire.
