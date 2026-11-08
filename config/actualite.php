<?php
if (!function_exists('asset')) { function asset($p) { $f = __DIR__ . '/' . $p; return $p . '?v=' . (is_file($f) ? filemtime($f) : '1'); } }

session_start();

require_once 'connexion.php';

/* =========================================================
   ADMIN
   ========================================================= */

$adminConnecte =
    isset($_SESSION['admin_connecte']) &&
    $_SESSION['admin_connecte'] === true;

$messageSucces = '';
$messageErreur = '';

/* =========================================================
   PUBLICATION D'UNE ACTUALITÉ PAR L'ADMIN
   ========================================================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $adminConnecte) {

    $titre = trim($_POST['titre'] ?? '');
    $contenu = trim($_POST['contenu'] ?? '');

    if ($titre === '' || $contenu === '') {

        $messageErreur =
            "Veuillez remplir le titre et le contenu.";

    } else {

        $image = null;

        /* ---------------------------------------------
           IMAGE
           --------------------------------------------- */

        if (
            isset($_FILES['image']) &&
            $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE
        ) {

            if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {

                $messageErreur =
                    "Erreur lors de l'envoi de l'image.";

            } else {

                $extensionsAutorisees = [
                    'jpg',
                    'jpeg',
                    'png',
                    'webp'
                ];

                $extension = strtolower(
                    pathinfo(
                        $_FILES['image']['name'],
                        PATHINFO_EXTENSION
                    )
                );

                if (
                    !in_array(
                        $extension,
                        $extensionsAutorisees
                    )
                ) {

                    $messageErreur =
                        "Format d'image non autorisé.";

                } else {

                    $dossier =
                        'uploads/actualites/';

                    if (!is_dir($dossier)) {

                        mkdir(
                            $dossier,
                            0777,
                            true
                        );
                    }

                    $nomImage =
                        'actualite_' .
                        date('Ymd_His') .
                        '_' .
                        bin2hex(random_bytes(3)) .
                        '.' .
                        $extension;

                    $chemin =
                        $dossier . $nomImage;

                    if (
                        move_uploaded_file(
                            $_FILES['image']['tmp_name'],
                            $chemin
                        )
                    ) {

                        $image = $chemin;

                    } else {

                        $messageErreur =
                            "Impossible d'enregistrer l'image.";
                    }
                }
            }
        }

        /* ---------------------------------------------
           INSERTION
           --------------------------------------------- */

        if ($messageErreur === '') {

            try {

                $idAdmin =
                    $_SESSION['idAdmin'] ?? 3;

                $sql = "
                    INSERT INTO actualite
                    (
                        titre,
                        contenu,
                        image,
                        statut,
                        date_publication,
                        idAdmin
                    )
                    VALUES
                    (
                        :titre,
                        :contenu,
                        :image,
                        'Publiée',
                        NOW(),
                        :idAdmin
                    )
                ";

                $stmt =
                    $connexion->prepare($sql);

                $stmt->execute([
                    ':titre' => $titre,
                    ':contenu' => $contenu,
                    ':image' => $image,
                    ':idAdmin' => $idAdmin
                ]);

                $messageSucces =
                    "L'actualité a été publiée avec succès.";

            } catch (Exception $e) {

                if (
                    $image !== null &&
                    file_exists($image)
                ) {

                    unlink($image);
                }

                $messageErreur =
                    "Erreur lors de la publication.";
            }
        }
    }
}

/* =========================================================
   RÉCUPÉRATION DES ACTUALITÉS PUBLIÉES
   ========================================================= */

$actualites = [];

try {

    $sql = "
        SELECT
            idActualite,
            titre,
            contenu,
            image,
            statut,
            date_publication,
            idAdmin
        FROM actualite
        WHERE statut = 'Publiée'
        ORDER BY date_publication DESC
    ";

    $stmt =
        $connexion->query($sql);

    $actualites =
        $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {

    $messageErreur =
        "Impossible de charger les actualités.";
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>

  <meta charset="UTF-8">

  <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, viewport-fit=cover"
  >

  <title>Actualités | HEMIP</title>

  <link rel="stylesheet" href="<?= asset('assets/vendor/fonts/fonts.css') ?>">

  <link
      rel="stylesheet"
      href="<?= asset('assets/css/actualite.css') ?>"
  >

  <!-- Remix Icon -->

  <link
      href="<?= asset('assets/vendor/remixicon/remixicon.css') ?>"
      rel="stylesheet"
  >

  <!-- CSS -->

  <link
      rel="stylesheet"
      href="<?= asset('assets/css/style.css') ?>"
  >

  <!-- favicon -->

  <link rel="icon" type="image/png" href="assets/icons/hemip-192.png">

  <!-- =================================================
       STYLE UNIQUEMENT POUR LES NOUVELLES FONCTIONNALITÉS
       ================================================= -->

  <style>

    .actu-tabs {
      display: flex;
      justify-content: center;
      gap: 12px;
      margin: 35px 0;
      flex-wrap: wrap;
    }

    .actu-tab {
      border: none;
      padding: 12px 22px;
      border-radius: 30px;
      background: var(--hemip-bg);
      color: var(--hemip-text);
      font-family: inherit;
      font-weight: 600;
      cursor: pointer;
      transition: 0.3s;
    }

    .actu-tab:hover,
    .actu-tab.active {
      background: var(--hemip-primary-strong);
      color: var(--hemip-bg);
    }

    .actu-tab-content {
      display: none;
    }

    .actu-tab-content.active {
      display: block;
    }

    .admin-publication {
      max-width: 900px;
      margin: 30px auto 45px;
      padding: 25px;
      background: var(--hemip-bg);
      border-radius: 15px;
      box-shadow: 0 8px 25px rgba(var(--hemip-text-rgb), 0.07);
    }

    .admin-publication h2 {
      margin-bottom: 20px;
    }

    .admin-publication .form-group {
      margin-bottom: 18px;
    }

    .admin-publication label {
      display: block;
      margin-bottom: 7px;
      font-weight: 600;
    }

    .admin-publication input,
    .admin-publication textarea {
      width: 100%;
      padding: 12px;
      border: 1px solid var(--hemip-neutral-soft);
      border-radius: 8px;
      font-family: inherit;
    }

    .admin-publication textarea {
      min-height: 140px;
      resize: vertical;
    }

    .admin-publication button {
      border: none;
      background: var(--hemip-primary-strong);
      color: var(--hemip-bg);
      padding: 12px 20px;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 600;
    }

    .admin-message {
      max-width: 900px;
      margin: 25px auto;
      padding: 14px 18px;
      border-radius: 8px;
    }

    .admin-message.success {
      background: var(--hemip-primary-soft);
      color: var(--hemip-primary-strong);
    }

    .admin-message.error {
      background: var(--hemip-accent-soft);
      color: var(--hemip-accent-strong);
    }

    .student-section-title {
      text-align: center;
      margin: 45px 0 25px;
    }

    .student-section-title h2 {
      margin-bottom: 8px;
    }

    .academic-grid {
      display: grid;
      grid-template-columns:
        repeat(auto-fit, minmax(240px, 1fr));
      gap: 20px;
    }

    .academic-card {
      background: var(--hemip-bg);
      padding: 22px;
      border-radius: 12px;
      box-shadow: 0 7px 22px rgba(var(--hemip-text-rgb), 0.06);
    }

    .academic-card h3 {
      margin-bottom: 7px;
    }

    .academic-card p {
      margin: 0;
      opacity: 0.75;
    }

    .admin-count {
      text-align: center;
      padding: 30px;
      background: var(--hemip-bg);
      border-radius: 12px;
      box-shadow: 0 7px 22px rgba(var(--hemip-text-rgb), 0.06);
    }

    .admin-count strong {
      display: block;
      font-size: 42px;
      color: var(--hemip-primary-strong);
    }

    .testimonial-info {
      background: var(--hemip-bg);
      padding: 22px;
      border-radius: 12px;
      margin-bottom: 45px;
    }

    /* Augmentation de la taille du texte */
    .news-content p, .news-content h2, .news-content h3 {
      font-size: 1.1rem;
      line-height: 1.8;
    }

    .news-content h2 {
      font-size: 1.4rem;
    }

  </style>

    <meta name="theme-color" content="#1094d7">
    <meta name="application-name" content="HEMIP">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="HEMIP">
    <script>window.addEventListener("beforeinstallprompt",function(e){e.preventDefault();window.__hemipInstallPrompt=e;});</script>
    <link rel="manifest" href="manifest.json" crossorigin="use-credentials">
    <link rel="apple-touch-icon" sizes="180x180" href="assets/icons/hemip-180.png">
    <link rel="icon" type="image/png" sizes="192x192" href="assets/icons/hemip-192.png">
    <link rel="stylesheet" href="<?= asset('assets/css/pwa.css') ?>">
  <link rel="stylesheet" href="<?= asset('assets/css/hemip-theme.css') ?>">
    <link rel="stylesheet" href="<?= asset('assets/css/responsive.css') ?>">
</head>

<body>

<header class="header">

    <nav class="navbar">

        <!-- LOGO -->

        <a href="index.php" class="logo">

            <img
                src="assets/img/logo-hemip.jpg"
                alt="Logo HEMIP"
            >

            <span>HEMIP</span>

        </a>

        <!-- BOUTON MOBILE -->

        <button
            class="menu-btn" id="menu-btn" type="button" aria-label="Ouvrir le menu de navigation" aria-expanded="false" aria-controls="nav-list">

            <i class="ri-menu-line"></i>

        </button>

        <!-- MENU -->

        <ul
            class="nav-list"
            id="nav-list"
        >

            <li>
                <a href="index.php">
                    Accueil
                </a>
            </li>

            <!-- DROPDOWN FORMATION -->

            <li class="dropdown">

                <button class="dropdown-btn">

                    Formation

                    <i class="ri-arrow-down-s-line"></i>

                </button>

                <ul class="dropdown-menu">

                    <!-- SOUS MENU pole technique -->

                    <li class="submenu">

                        <button class="submenu-btn">

                            Pôle technique

                            <i class="ri-arrow-right-s-line"></i>

                        </button>

                        <ul class="submenu-menu">

                            <li>
                                <a href="GP.html">
                                    Génie Pétrolier
                                </a>
                            </li>

                            <li>
                                <a href="GL.html">
                                    Génie Logiciel
                                </a>
                            </li>

                            <li>
                                <a href="RT.html">
                                    Réseaux et Télécommunication
                                </a>
                            </li>

                            <li>
                                <a href="MI.html">
                                    Maintenance industrielle
                                </a>
                            </li>

                            <li>
                                <a href="AII.html">
                                    Automatisation et informatique industriel
                                </a>
                            </li>

                            <li>
                                <a href="EE.html">
                                    Électrotechnique et Électronique
                                </a>
                            </li>

                            <li>
                                <a href="GC.html">
                                    Génie Chimique
                                </a>
                            </li>

                            <li>
                                <a href="RP.html">
                                    Raffinage et Pétrochimie
                                </a>
                            </li>

                            <li>
                                <a href="ENR.html">
                                    Énergies Renouvelables
                                </a>
                            </li>

                        </ul>

                    </li>

                    <!-- SOUS MENU -->

                    <li class="submenu">

                        <button class="submenu-btn">

                            Pôle Commercial

                            <i class="ri-arrow-right-s-line"></i>

                        </button>

                        <ul class="submenu-menu">

                            <li>
                                <a href="DAE.html">
                                    Droit des affaires et des entreprises
                                </a>
                            </li>

                            <li>
                                <a href="GLT.html">
                                    Gestion Logistique et transport
                                </a>
                            </li>

                            <li>
                                <a href="BFA.html">
                                    Banque et Finance des assurances
                                </a>
                            </li>

                            <li>
                                <a href="CIT.html">
                                    Commerce international et transit
                                </a>
                            </li>

                            <li>
                                <a href="GFC.html">
                                    Gestion finance et comptable
                                </a>
                            </li>

                            <li>
                                <a href="MRH.html">
                                    Management des Ressources Humaines
                                </a>
                            </li>

                        </ul>

                    </li>

                </ul>

            </li>

            <li>

                <a
                    href="inscription.php"
                    onclick="afficherFormulaire()"
                >
                    Inscription
                </a>

            </li>

            <li>

                <a href="actualite.php">
                    Actualités
                </a>

            </li>

            <li>
                <a href="realisations.php">
                    Nos réalisations
                </a>
            </li>

            <li>

                <a href="contact.php">
                    Contact
                </a>

            </li>

            <li><a href="faq.php">FAQ</a></li>

            <!-- AJOUT : À PROPOS -->

            <li>

                <a href="a-propos.php">
                    À propos
                </a>

            </li>

            <!-- ADMIN -->

            <li>

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

            </li>

        </ul>

    </nav>

</header>

<div class="news-header">

  <div class="container-actu">

    <h1>
      Actualités
    </h1>

    <p>
      Découvrez les dernières nouvelles et événements
      de la Haute École de Management et de l’Ingénierie - la Percée.
      Journées sportives, compétitions, remises des diplômes,
      bal de fin d'année ou encore visite en entreprise.
      Ne ratez rien des derniers évènements !
    </p>

  </div>

</div>

<!-- =====================================================
     MESSAGES ADMIN
     ===================================================== -->

<?php if ($messageSucces !== ''): ?>

    <div class="admin-message success">

        <?= htmlspecialchars($messageSucces) ?>

    </div>

<?php endif; ?>

<?php if ($messageErreur !== ''): ?>

    <div class="admin-message error">

        <?= htmlspecialchars($messageErreur) ?>

    </div>

<?php endif; ?>

<!-- =====================================================
     PUBLICATION ADMIN
     ===================================================== -->

<?php if ($adminConnecte): ?>

    <section class="admin-publication">

        <h2>
            <i class="ri-add-circle-line"></i>
            Publier une actualité
        </h2>

        <form
            method="POST"
            enctype="multipart/form-data"
        >

            <div class="form-group">

                <label for="titre">
                    Titre
                </label>

                <input
                    type="text"
                    id="titre"
                    name="titre"
                    maxlength="255"
                    required
                >

            </div>

            <div class="form-group">

                <label for="contenu">
                    Contenu
                </label>

                <textarea
                    id="contenu"
                    name="contenu"
                    required
                ></textarea>

            </div>

            <div class="form-group">

                <label for="image">
                    Image
                </label>

                <input
                    type="file"
                    id="image"
                    name="image"
                    accept=".jpg,.jpeg,.png,.webp"
                >

            </div>

            <button type="submit">

                <i class="ri-send-plane-line"></i>

                Publier l'actualité

            </button>

        </form>

    </section>

<?php endif; ?>

<!-- =====================================================
     ONGLET ACTUALITÉS
     ===================================================== -->

<div
    id="actualites"
    class="actu-tab-content active"
>

<main class="container-actu">

  <section class="news-grid">


    <!-- =================================================
         ACTUALITÉ ORIGINALE 1
         ================================================= -->

    <article class="news-card">

      <div class="news-image">

        <video
            src="assets/img/dac-presentation.mp4"
            controls
            autoplay
        ></video>

        <span class="tag">
            Vie académique
        </span>

      </div>

      <div class="news-content">

        <span class="date">
            NOUVELLE NOMINATION
        </span>

        <h2>
            Un nouveau Directeur des Affaires
            Académiques à HEMIP
        </h2>

        <p>

          HEMIP accueille
          <strong>Pr. Arthur NSEKA MPELA</strong>
          en qualité de
          <strong>
              Directeur des Affaires Académiques (DAAC)
          </strong>.

          Une nouvelle étape pour accompagner
          la vie académique et les étudiants
          de l’établissement.

        </p>

        <button
            class="read-more"
            type="button"
            onclick="openNews('daac')"
        >

            Lire l’actualité

            <span>→</span>

        </button>

      </div>

    </article>


    <!-- =================================================
         ACTUALITÉ ORIGINALE 2
         ================================================= -->

    <article class="news-card">

      <div class="news-image">

        <img
            src="assets/img/journee-fraicheur.png"
            alt="Journée de fraîcheur à HEMIP le 2 juin 2026"
        >

        <span class="tag">
            Vie estudiantine
        </span>

      </div>

      <div class="news-content">

        <span class="date">
            02 JUIN 2026
        </span>

        <h2>
            Journée de fraîcheur à HEMIP
        </h2>

        <p>

           <strong>
               Journée de fraîcheur
           </strong>

           placée sous le signe
           de l’élégance, du respect,
           du professionnalisme et de l’unité.

           Les étudiants étaient invités à
           s’habiller avec classe et à représenter
           fièrement leur établissement.

        </p>

        <button
            class="read-more"
            type="button"
            onclick="openNews('fraicheur')"
        >

            Lire l’actualité

            <span>→</span>

        </button>

      </div>

    </article>


    <!-- =================================================
         ACTUALITÉ ORIGINALE 3
         ================================================= -->

    <article class="news-card">

      <div class="news-image">

        <img
            src="assets/img/img-master.png"
            alt="Inscriptions ouvertes pour le cycle Master à HEMIP"
        >

        <span class="tag">
            Formation
        </span>

      </div>

      <div class="news-content">

        <span class="date">
            CYCLE MASTER
        </span>

        <h2>
            Les inscriptions au Master sont ouvertes
        </h2>

        <p>

          HEMIP ouvre les inscriptions pour son
          <strong>
              nouveau cycle Master
          </strong>.

          La rentrée académique est annoncée
          pour le
          <strong>
              12 octobre 2026 à 8h00
          </strong>.

          Une opportunité de poursuivre sa formation
          dans un cadre professionnel et orienté
          vers l’innovation.

        </p>

        <button
            class="read-more"
            type="button"
            onclick="openNews('master')"
        >

            Lire l’actualité

            <span>→</span>

        </button>

      </div>

    </article>


    <!-- =================================================
         ACTUALITÉS PUBLIÉES PAR L'ADMIN
         ================================================= -->

    <?php foreach ($actualites as $actualite): ?>

        <article class="news-card">

            <div class="news-image">

                <?php if (!empty($actualite['image'])): ?>

                    <img
                        src="<?= htmlspecialchars($actualite['image']) ?>"
                        alt="<?= htmlspecialchars($actualite['titre']) ?>"
                    >

                <?php else: ?>

                    <div
                        style="
                            height:100%;
                            min-height:200px;
                            display:flex;
                            align-items:center;
                            justify-content:center;
                            font-size:45px;
                        "
                    >

                        <i class="ri-newspaper-line"></i>

                    </div>

                <?php endif; ?>

                <span class="tag">
                    Actualité
                </span>

            </div>


            <div class="news-content">

                <span class="date">

                    <?= date(
                        'd/m/Y',
                        strtotime(
                            $actualite['date_publication']
                        )
                    ) ?>

                </span>


                <h2>

                    <?= htmlspecialchars(
                        $actualite['titre']
                    ) ?>

                </h2>


                <p>

                    <?= nl2br(
                        htmlspecialchars(
                            $actualite['contenu']
                        )
                    ) ?>

                </p>


                <button
                    class="read-more"
                    type="button"
                    onclick="openDynamicNews(
                        <?= htmlspecialchars(
                            json_encode(
                                $actualite['titre'],
                                JSON_UNESCAPED_UNICODE
                            )
                        ) ?>,
                        <?= htmlspecialchars(
                            json_encode(
                                $actualite['contenu'],
                                JSON_UNESCAPED_UNICODE
                            )
                        ) ?>
                    )"
                >

                    Lire l’actualité

                    <span>→</span>

                </button>

            </div>

        </article>

    <?php endforeach; ?>


  </section>

</main>

</div>


<!-- =====================================================
     MODAL ORIGINAL
     ===================================================== -->

<div
    class="modal"
    id="newsModal"
    aria-hidden="true"
>

  <div class="modal-box">

    <button
        class="close"
        onclick="closeNews()"
        aria-label="Fermer"
    >
        ×
    </button>

    <span
        class="eyebrow"
        id="modalTag"
    ></span>

    <h2 id="modalTitle"></h2>

    <p id="modalText"></p>

  </div>

</div>


<footer class="footer reveal">

    <div class="footer-container ">

        <!-- Présentation -->

        <div class="footer-col footer-about">

            <div class="footer-logo">

                <img
                    src="assets/img/logo-hemip.jpg"
                    alt="Logo HEMIP"
                >

                <span>
                    HEMIP
                </span>

            </div>

            <p class="footer-slogan">

                « Nous formons des professionnels
                qualifiés et chevronnés »

            </p>

        </div>


        <!-- Contact -->

        <div class="footer-col">

            <h3>
                Contactez-nous
            </h3>

            <ul
                class="footer-contact"
                id="footer"
            >

                <li>

                    <i class="ri-mail-line"></i>

                    <a
                        href="mailto:hemilaperceeinformation@gmail.com"
                    >

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


                <li>

                    <i class="ri-whatsapp-line"></i>

                    <a
                        href="https://wa.me/242055865094"
                        target="_blank"
                    >

                        WhatsApp

                    </a>

                </li>

            </ul>

        </div>


        <!-- Réseaux sociaux -->

        <div class="footer-col">

            <h3>
                Suivez-nous
            </h3>

            <p class="social-text">

                Retrouvez HEMIP sur nos réseaux sociaux.

            </p>

            <div class="social-links">


                <a
                    href="https://vm.tiktok.com/ZS9kFSarLrbTD-96s9G/"
                    class="social-link"
                    aria-label="TikTok"
                >

                    <i class="ri-tiktok-line"></i>

                </a>


                <a
                    href="https://www.facebook.com/hemipcongo"
                    class="social-link"
                    aria-label="Facebook"
                >

                    <i class="ri-facebook-fill"></i>

                </a>


                <a
                    href="https://wa.me/242055865094"
                    class="social-link"
                    aria-label="WhatsApp"
                    target="_blank"
                >

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


<script>

/* =========================================================
   ACTUALITÉS ORIGINALES
   ========================================================= */

const news = {

  dac: {

    tag: "VIE ACADÉMIQUE",

    title:
      "Un nouveau Directeur des Affaires Académiques à HEMIP",

    text:
      "HEMIP accueille Arthur Nseka Mpela en qualité de Directeur des Affaires Académiques (DAC). Cette nomination vient renforcer l’accompagnement de la vie académique et des étudiants au sein de l’établissement."

  },


  fraicheur: {

    tag:
      "VIE ÉTUDIANTE | 02 JUIN 2026",

    title:
      "Journée de fraîcheur à HEMIP",

    text:
      "À l’occasion de la Journée de fraîcheur, HEMIP invitait sa communauté à s’habiller avec classe et à représenter fièrement l’établissement, autour des valeurs d’élégance, de respect, de professionnalisme et d’unité."

  },


  master: {

    tag:
      "FORMATION | CYCLE MASTER",

    title:
      "Les inscriptions au Master sont ouvertes",

    text:
      "HEMIP ouvre les inscriptions pour son nouveau cycle Master. La rentrée académique est annoncée pour le 12 octobre 2026 à 8h00. Une opportunité de poursuivre sa formation dans un cadre professionnel et orienté vers l’innovation."

  }

};


/* =========================================================
   MODAL ORIGINAL
   ========================================================= */

function openNews(key) {

  const item = news[key];

  document.getElementById(
      "modalTag"
  ).textContent = item.tag;

  document.getElementById(
      "modalTitle"
  ).textContent = item.title;

  document.getElementById(
      "modalText"
  ).textContent = item.text;

  document.getElementById(
      "newsModal"
  ).classList.add("show");

  document.getElementById(
      "newsModal"
  ).setAttribute(
      "aria-hidden",
      "false"
  );

}


/* =========================================================
   MODAL DES ACTUALITÉS PUBLIÉES PAR L'ADMIN
   ========================================================= */

function openDynamicNews(
    titre,
    contenu
) {

  document.getElementById(
      "modalTag"
  ).textContent =
      "ACTUALITÉ HEMIP";

  document.getElementById(
      "modalTitle"
  ).textContent =
      titre;

  document.getElementById(
      "modalText"
  ).textContent =
      contenu;

  document.getElementById(
      "newsModal"
  ).classList.add("show");

  document.getElementById(
      "newsModal"
  ).setAttribute(
      "aria-hidden",
      "false"
  );

}


/* =========================================================
   FERMER LE MODAL
   ========================================================= */

function closeNews() {

  document.getElementById(
      "newsModal"
  ).classList.remove("show");

  document.getElementById(
      "newsModal"
  ).setAttribute(
      "aria-hidden",
      "true"
  );

}


/* =========================================================
   FERMETURE EN CLIQUANT À L'EXTÉRIEUR
   ========================================================= */

document
    .getElementById("newsModal")
    .addEventListener(
        "click",
        e => {

            if (
                e.target.id === "newsModal"
            ) {

                closeNews();

            }

        }
    );


</script>

<script src="<?= asset('assets/js/pwa.js') ?>" defer></script>

</body>

</html>