<?php
if (!function_exists('asset')) { function asset($p) { $f = __DIR__ . '/' . $p; return $p . '?v=' . (is_file($f) ? filemtime($f) : '1'); } }

session_start();

require_once "connexion.php";

/* =========================================================
   ADMIN
   ========================================================= */

$adminConnecte =
    isset($_SESSION['admin_connecte']) &&
    $_SESSION['admin_connecte'] === true;

$messageSucces = '';
$messageErreur = '';

$message = "";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="description" content="Découvrir l’essai de valorisation des os de bétail présenté dans le dossier de HEMIP.">
    <meta name="theme-color" content="#1094d7">
    <meta name="application-name" content="HEMIP">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="HEMIP">
    <script>window.addEventListener("beforeinstallprompt",function(e){e.preventDefault();window.__hemipInstallPrompt=e;});</script>
    <link rel="manifest" href="manifest.json" crossorigin="use-credentials">
    <link rel="apple-touch-icon" sizes="180x180" href="assets/icons/hemip-180.png">
    <link rel="icon" type="image/png" sizes="192x192" href="assets/icons/hemip-192.png">
    <link rel="stylesheet" href="<?= asset('assets/vendor/remixicon/remixicon.css') ?>">
    <link rel="stylesheet" href="<?= asset('assets/css/style.css') ?>">
    <link rel="stylesheet" href="<?= asset('assets/css/pwa.css') ?>">
    <link rel="stylesheet" href="<?= asset('assets/css/independent-pages.css') ?>">
    <title>Nos réalisations | HEMIP</title>
  <link rel="stylesheet" href="<?= asset('assets/css/hemip-theme.css') ?>">
    <link rel="stylesheet" href="<?= asset('assets/css/responsive.css') ?>">
    <style>
        /* Augmentation de la taille du texte */
        .achievement-wrap p, .achievement-wrap h3, .achievement-wrap h2 {
            font-size: 1.1rem;
            line-height: 1.8;
        }

        .achievement-wrap h2 {
            font-size: 1.4rem;
        }

        .achievement-wrap h3 {
            font-size: 1.2rem;
        }
    </style>
</head>
<body>
<header class="header">
    <nav class="navbar" aria-label="Navigation principale">
        <a href="index.php" class="logo"><img src="assets/img/logo-hemip.jpg" alt="Logo HEMIP"><span>HEMIP</span></a>
        <button class="menu-btn" id="menu-btn" type="button" aria-label="Ouvrir le menu de navigation" aria-expanded="false" aria-controls="nav-list"><span aria-hidden="true">☰</span></button>
        <ul class="nav-list" id="nav-list">
            <li><a href="index.php">Accueil</a></li>
            <li class="dropdown" id="formations">
                <button class="dropdown-btn" type="button" aria-expanded="false">Formation <i class="ri-arrow-down-s-line" aria-hidden="true"></i></button>
                <ul class="dropdown-menu">
                    <li class="submenu"><button class="submenu-btn" type="button">Pôle technique <i class="ri-arrow-right-s-line" aria-hidden="true"></i></button><ul class="submenu-menu">
                        <li><a href="GP.html">Génie Pétrolier</a></li><li><a href="GL.html">Génie Logiciel</a></li><li><a href="RT.html">Réseaux et Télécommunication</a></li><li><a href="MI.html">Maintenance Industrielle</a></li><li><a href="AII.html">Automatisation et Informatique Industriel</a></li><li><a href="EE.html">Électrotechnique et Électronique</a></li><li><a href="GC.html">Génie Chimique</a></li><li><a href="RP.html">Raffinage et Pétrochimie</a></li><li><a href="ENR.html">Énergies Renouvelables</a></li>
                    </ul></li>
                    <li class="submenu"><button class="submenu-btn" type="button">Pôle Commercial <i class="ri-arrow-right-s-line" aria-hidden="true"></i></button><ul class="submenu-menu">
                        <li><a href="DAE.html">Droit des Affaires et des Entreprises</a></li><li><a href="GLT.html">Gestion Logistique et Transport</a></li><li><a href="BFA.html">Banque et Finance des Assurances</a></li><li><a href="CIT.html">Commerce International et Transit</a></li><li><a href="GFC.html">Gestion Finance et Comptable</a></li><li><a href="MRH.html">Management des Ressources Humaines</a></li>
                    </ul></li>
                </ul>
            </li>
            <li><a href="inscription.php">Inscription</a></li>
            <li><a href="actualite.php">Actualités</a></li>
            <li><a href="realisations.php" aria-current="page">Nos réalisations</a></li>
            <li><a href="contact.php">Contact</a></li>
            <li><a href="faq.php">FAQ</a></li>
            <li><a href="a-propos.php">À propos</a></li>

            <!-- ADMIN -->

            <?php if ($adminConnecte): ?>

            <!-- Accès au tableau de bord -->
            <li>
                <a href="dashboard.php" title="Tableau de bord">
                    <i class="ri-dashboard-line"></i>
                </a>
            </li>

            <!-- Administrateur connecté -->
            <li class="admin-status">
                <i class="ri-admin-line" style="color: var(--hemip-bg);"></i>
                <span style="color: var(--hemip-bg); font-size: 15px;">Connecté</span>
            </li>

           <?php else: ?>

            <!-- Connexion administrateur -->
            <li>
                <a href="login.php" title="Connexion administrateur">
                    <i class="ri-admin-line"></i>
                </a>
            </li>

           <?php endif; ?>
            
        </ul>
    </nav>
</header>
<main class="achievement-page">
    <section class="independent-page-hero" aria-labelledby="achievement-page-title">
        <div class="independent-page-hero-inner">
            <div>
                <p class="independent-page-eyebrow">Recherche · Innovation · Matériaux écologiques</p>
                <h1 id="achievement-page-title">Valorisation expérimentale des os de bétail</h1>
                <p>Un essai présenté dans le dossier HEMIP explore la transformation de résidus osseux en poudre, avec pour objectif la recherche d’un matériau pouvant se substituer à la chaux vive.</p>
            </div>
            <div class="achievement-status"><i class="ri-flask-line" aria-hidden="true"></i><span>Essai expérimental<br><strong>Résultats d’un lot décrits dans le dossier</strong></span></div>
        </div>
    </section>

    <section class="achievement-section" aria-labelledby="achievement-results-title">
        <div class="achievement-wrap">
            <div class="achievement-heading">
                <p class="independent-page-eyebrow">Un résultat mesuré</p>
                <h2 id="achievement-results-title">Du lot d’os à la poudre finale</h2>
                <p>Le bilan expérimental documente le traitement d’un lot d’os frais, puis le refroidissement et le broyage du matériau obtenu.</p>
            </div>
            <div class="achievement-metrics" aria-label="Chiffres du lot expérimental">
                <article class="achievement-metric"><span>Charge de départ</span><strong>25 <small>kg</small></strong><p>Os frais mis en traitement.</p></article>
                <article class="achievement-metric"><span>Produit récupéré</span><strong>10 <small>kg</small></strong><p>Poudre finale après traitement et broyage.</p></article>
                <article class="achievement-metric achievement-metric--accent"><span>Rendement moyen indiqué</span><strong>56<small>%</small></strong><p>Production de CaO à partir de l’hydroxyapatite, selon le dossier.</p></article>
            </div>

            <div class="achievement-process" aria-label="Étapes décrites dans le dossier">
                <article><span class="achievement-step-number">01</span><div><h3>Traitement thermique</h3><p>Le lot d’os est soumis au traitement thermique décrit dans le bilan expérimental.</p></div></article>
                <article><span class="achievement-step-number">02</span><div><h3>Refroidissement</h3><p>Le matériau obtenu est refroidi avant les opérations de finition.</p></div></article>
                <article><span class="achievement-step-number">03</span><div><h3>Broyage et récupération</h3><p>Le matériau est broyé ; le dossier rapporte 10 kg de poudre finale pour ce lot.</p></div></article>
            </div>

            <div class="achievement-figures">
                <div class="achievement-figures-heading">
                    <p class="independent-page-eyebrow" style = "color : #23a8eb">En images</p>
                    <h3>Le processus en images</h3>
                </div>
                <figure class="achievement-figure">
                    <a class="rid-figure" href="assets/img/hemip-calcination.jpg" target="_blank" rel="noopener" title="Toucher pour agrandir">
                        <img src="assets/img/hemip-calcination.jpg" alt="Schéma du processus de calcination des os de vache : four électrique à température contrôlée, intérieur du four, puis os frais, os en cours de traitement thermique (pyrolyse de 450 à 900 °C), os en cours de calcination et os complètement calcinés." width="2100" height="1556" loading="lazy" decoding="async">
                        <span class="rid-zoom"><i class="ri-zoom-in-line" aria-hidden="true"></i> Toucher pour agrandir</span>
                    </a>
                    <figcaption>Calcination : du lot d’os frais aux os complètement calcinés. La pyrolyse se déroule entre 450 et 900 °C dans un four électrique à température contrôlée.</figcaption>
                </figure>
                <figure class="achievement-figure">
                    <a class="rid-figure" href="assets/img/hemip-broyage.jpg" target="_blank" rel="noopener" title="Toucher pour agrandir">
                        <img src="assets/img/hemip-broyage.jpg" alt="Schéma du processus de broyage : broyeur à boulets, poudre obtenue, formule de la loi de Bond et deux courbes d’énergie spécifique comparant les os de vache calcinés et le calcaire." width="2100" height="1434" loading="lazy" decoding="async">
                        <span class="rid-zoom"><i class="ri-zoom-in-line" aria-hidden="true"></i> Toucher pour agrandir</span>
                    </a>
                    <figcaption>Broyage : le matériau calciné est broyé dans un broyeur à boulets. La consommation énergétique est estimée par la loi de Bond, ici comparée à celle du calcaire.</figcaption>
                </figure>
                <figure class="achievement-figure">
                    <a class="rid-figure" href="assets/img/hemip-mortiers.jpg" target="_blank" rel="noopener" title="Toucher pour agrandir">
                        <img src="assets/img/hemip-mortiers.jpg" alt="Schéma de la confection et de la comparaison des mortiers : malaxeur, moules prismatiques, éprouvettes, essai mécanique de compression et tableau de composition avec la résistance à la compression de chaque mélange." width="2100" height="1824" loading="lazy" decoding="async">
                        <span class="rid-zoom"><i class="ri-zoom-in-line" aria-hidden="true"></i> Toucher pour agrandir</span>
                    </a>
                    <figcaption>Mortiers : comparaison de la résistance à la compression (Rc) entre un mortier à base de chaux vive commerciale et des mortiers à base de poudre d’os de vache.</figcaption>
                </figure>
                <figure class="achievement-figure">
                    <a class="rid-figure" href="assets/img/poudre-os-de-vache.jpg" target="_blank" rel="noopener" title="Toucher pour agrandir">
                        <img src="assets/img/poudre-os-de-vache.jpg" alt="Pot en plastique transparent à couvercle rouge contenant la poudre blanche obtenue à partir des os de vache calcinés puis broyés, avec une étiquette manuscrite « Poudre des os de vache »." width="1264" height="1264" loading="lazy" decoding="async">
                        <span class="rid-zoom"><i class="ri-zoom-in-line" aria-hidden="true"></i> Toucher pour agrandir</span>
                    </a>
                    <figcaption>Produit final : le ciment blanc obtenue après calcination et broyage d’os de vache, conditionnée en pot.</figcaption>
                </figure>
            </div>

            <a class="btn btn-primary achievement-contact-link" href="contact.php">Contacter l’école <i class="ri-arrow-right-line" aria-hidden="true"></i></a>
        </div>
    </section>
    <section class="achievement-video-section" id="videos" aria-labelledby="achievement-videos-title">
        <div class="achievement-wrap">
            <div class="achievement-video-heading">
                <p class="independent-page-eyebrow">La vie à HEMIP</p>
                <h2 id="achievement-videos-title">Nos réalisations en vidéo</h2>
                <p>Visionnez ces vidéos directement sur le site, sans ouvrir une page YouTube. Une connexion Internet est nécessaire.</p>
            </div>

            <div class="achievement-video-projects">
                <section class="achievement-video-track" aria-labelledby="palm-project-title">
                    <div class="achievement-video-track-heading">
                        <p class="independent-page-eyebrow">Énergies renouvelables</p>
                        <h3 id="palm-project-title">Du palmier nain au biodiesel</h3>
                        <p>De la culture de la matière première à la production du biocarburant, ces deux séquences illustrent les étapes présentées par HEMIP.</p>
                    </div>
                    <div class="achievement-video-grid">
                        <article class="achievement-video-card">
                            <div class="achievement-video-frame"><iframe src="https://www.youtube-nocookie.com/embed/huLuGVgXtdQ?rel=0&amp;playsinline=1&amp;hl=fr" title="Culture optimisée de palmiers nains pour biocarburant — vidéo HEMIP" loading="lazy" referrerpolicy="strict-origin-when-cross-origin" sandbox="allow-scripts allow-same-origin allow-presentation" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; fullscreen" allowfullscreen></iframe></div>
                            <div class="achievement-video-caption"><span>Biocarburant · matière première</span><h3>Culture optimisée de palmiers nains pour biocarburant</h3><p>Cette vidéo met en lumière une méthode agricole innovante basée sur la culture de palmiers nains (mesurant moins d'un mètre). L'utilisation de biofertilisants permet une croissance et une fructification accélérées, offrant des noix de palme mûres récoltables à même le sol en seulement quelques mois. Ces noix constituent la ressource première indispensable pour l'extraction de l'huile de palme, qui approvisionne ensuite l'usine pilote de l'HEMIP pour la fabrication du biodiesel.</p></div>
                        </article>
                        <article class="achievement-video-card">
                            <div class="achievement-video-frame"><iframe src="https://www.youtube-nocookie.com/embed/zxi7epX-p0s?rel=0&amp;playsinline=1&amp;hl=fr" title="Production semi-industrielle de biodiesel — vidéo HEMIP" loading="lazy" referrerpolicy="strict-origin-when-cross-origin" sandbox="allow-scripts allow-same-origin allow-presentation" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; fullscreen" allowfullscreen></iframe></div>
                            <div class="achievement-video-caption"><span>Énergies renouvelables · production</span><h3>Production semi-industrielle de biodiesel</h3><p>Cette vidéo présente le procédé de production locale de biodiesel en utilisant l'huile de palme comme matière première. Elle s'inscrit directement dans les projets d'énergies renouvelables de l'HEMIP. Ce biocarburant écologique est conçu pour alimenter une mini-centrale thermique destinée à produire de l'électricité, tout en générant des sous-produits industriels valorisables comme la glycérine pour la fabrication de savon.</p></div>
                        </article>
                    </div>
                </section>

                <section class="achievement-video-track" aria-labelledby="plastic-project-title">
                    <div class="achievement-video-track-heading">
                        <p class="independent-page-eyebrow">Recyclage et génie chimique</p>
                        <h3 id="plastic-project-title">Des déchets plastiques aux hydrocarbures</h3>
                        <p>Ces vidéos suivent le traitement thermique des plastiques, de l’obtention du carburant liquide à sa collecte.</p>
                    </div>
                    <div class="achievement-video-grid">
                        <article class="achievement-video-card">
                            <div class="achievement-video-frame"><iframe src="https://www.youtube-nocookie.com/embed/r8PUhZ3Lk7o?rel=0&amp;playsinline=1&amp;hl=fr" title="Extraction de carburant à partir de déchets plastiques — vidéo HEMIP" loading="lazy" referrerpolicy="strict-origin-when-cross-origin" sandbox="allow-scripts allow-same-origin allow-presentation" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; fullscreen" allowfullscreen></iframe></div>
                            <div class="achievement-video-caption"><span>Recyclage · transformation</span><h3>Extraction de carburant à partir de déchets plastiques</h3><p>Cette vidéo montre en direct la phase de recueil d'un carburant liquide obtenu en chauffant des déchets plastiques. Elle illustre concrètement le procédé de recyclage et de transformation développé par l'unité de recherche de l'HEMIP. Grâce au génie chimique, ce système permet de valoriser les plastiques usagés en les transformant en hydrocarbures exploitables tels que l'essence, le diesel ou le gaz GPL, tout en offrant une solution écologique de dépollution.</p></div>
                        </article>
                        <article class="achievement-video-card">
                            <div class="achievement-video-frame"><iframe src="https://www.youtube-nocookie.com/embed/qV1I03v1BJw?rel=0&amp;playsinline=1&amp;hl=fr" title="Collecte des hydrocarbures liquides — vidéo HEMIP" loading="lazy" referrerpolicy="strict-origin-when-cross-origin" sandbox="allow-scripts allow-same-origin allow-presentation" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; fullscreen" allowfullscreen></iframe></div>
                            <div class="achievement-video-caption"><span>Recyclage · collecte</span><h3>Collecte des hydrocarbures liquides</h3><p>Dans la continuité du processus de traitement thermique, cette séquence montre l'écoulement et la collecte minutieuse des hydrocarbures fraîchement extraits. Elle met en évidence la viabilité de la chaîne de valeur industrielle de l'HEMIP, prouvant qu'il est possible d'obtenir un rendement liquide régulier à partir du recyclage des matériaux plastiques pour une production d'énergie semi-industrielle locale.</p></div>
                        </article>
                    </div>
                </section>
            </div>
            <p class="achievement-video-note">Lecteur intégré en mode de confidentialité avancé. Aucune redirection vers YouTube n’est nécessaire pour lancer une lecture autorisée.</p>
        </div>
    </section>
</main>
<footer class="footer reveal">
    <div class="footer-container " id="footer">

        <!-- Présentation -->
        <div class="footer-col footer-about">
            <div class="footer-logo">
                <img src="assets/img/logo-hemip.jpg" alt="Logo HEMIP">
                <span>HEMIP</span>
            </div>

            <p class="footer-slogan">
                « Nous formons des professionnels qualifiés et chevronnés »
            </p>
        </div>

        <!-- Contact -->
        <div class="footer-col">
            <h3>Contactez-nous</h3>

            <ul class="footer-contact">
                <li>
                    <i class="ri-mail-line"></i>
                    <a href="mailto:hemilaperceeinformation@gmail.com">
             hemilaperceeinformation@gmail.com
                    </a>
                </li>

                <li>
                    <i class="ri-phone-line"></i>
                    <a href="tel:+242069149242">
                        +242 06 914 92 42
                    </a>
                </li>

                <li>
                    <i class="ri-phone-line"></i>
                    <a href="tel:+242063560677">
                        +242 06 356 06 77
                    </a>
                </li>

            </ul>
        </div>

        <!-- Réseaux sociaux -->
        <div class="footer-col">
            <h3>Suivez-nous</h3>

            <p class="social-text">
                Retrouvez HEMIP sur nos réseaux sociaux.
            </p>

            <div class="social-links">

                <a href="https://vm.tiktok.com/ZS9kFSarLrbTD-96s9G/" class="social-link" aria-label="TikTok">
                    <i class="ri-tiktok-line"></i>
                </a>

                <a href="https://www.facebook.com/hemipcongo" class="social-link" aria-label="Facebook">
                    <i class="ri-facebook-fill"></i>
                </a>

                <a href="https://wa.me/242055865094"
                   class="social-link"
                   aria-label="WhatsApp"
                   target="_blank">
                    <i class="ri-whatsapp-line"></i>
                </a>

            </div>
        </div>

    </div>

    <!-- Bas du footer -->
    <div class="footer-bottom">
        <p>
            © 2026 HEMIP. Tous droits réservés.
        </p>

        <p>
            Haute École de Management et d'Ingénierie la Percée
        </p>
    </div>
</footer>
<script src="<?= asset('assets/js/main.js') ?>"></script>
<script src="<?= asset('assets/js/pwa.js') ?>" defer></script>
</body>
</html>

