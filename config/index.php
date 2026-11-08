Essaie avec ça                    <?php
if (!function_exists('asset')) { function asset($p) { $f = __DIR__ . '/' . $p; return $p . '?v=' . (is_file($f) ? filemtime($f) : '1'); } }
session_start();

$adminConnecte = isset($_SESSION['admin_connecte'])
    && $_SESSION['admin_connecte'] === true;
?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">

    <!-- Remix Icon -->
    <link
        href="<?= asset('assets/vendor/remixicon/remixicon.css') ?>"
        rel="stylesheet"
    >

    <!-- CSS -->
    <link rel="stylesheet" href="<?= asset('assets/css/style.css') ?>">
    <link rel="stylesheet" href="<?= asset('assets/css/pwa.css') ?>">
    <link rel="stylesheet" href="<?= asset('assets/css/home-sections.css') ?>">
    <link rel="stylesheet" href="<?= asset('assets/css/hemip-theme.css') ?>">

     <!-- favicon -->
    <link rel="icon" type="image/png" href="assets/icons/hemip-192.png">
    <title>Site web | HEMIP</title>

    <meta name="theme-color" content="#23a8eb">
    <meta name="application-name" content="HEMIP">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="HEMIP">
    <script>window.addEventListener("beforeinstallprompt",function(e){e.preventDefault();window.__hemipInstallPrompt=e;});</script>
    <link rel="manifest" href="manifest.json" crossorigin="use-credentials">
    <link rel="apple-touch-icon" sizes="180x180" href="assets/icons/hemip-180.png">
    <link rel="icon" type="image/png" sizes="192x192" href="assets/icons/hemip-192.png">
    
    <link rel="stylesheet" href="<?= asset('assets/css/responsive.css') ?>">

    <style>
        /* « Pourquoi choisir HEMIP » : titre à gauche, cartes pleine largeur, puis images */
        #ecole.container_pourquoi { display: flex; flex-direction: column; align-items: stretch; flex-wrap: nowrap; gap: 24px; margin-top: 40px; }
        #ecole .why-head { width: 100%; max-width: 100%; text-align: left; align-self: flex-start; }
        #ecole .why-head h1 { text-align: left; margin-left: 0; margin-right: auto; }
        #ecole .why-wide { width: 100%; max-width: 100%; }
        .why-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px; }
        .why-item { padding: 20px 20px 22px; border: 1px solid #e6edf3; border-radius: 18px; background: #fff; min-width: 0; }
        .why-item:last-child:nth-child(3n+1) { grid-column: 1 / -1; }
        .why-item strong { display: block; color: #102d45; font-size: 1rem; line-height: 1.4; margin-bottom: 12px; }
        .why-item p { margin: 0; font-size: .9rem; line-height: 1.7; color: #687583; }
        .why-cta { justify-content: center; margin-top: 28px; }

        /* FIX: images remplissent tout l'espace - plus de bandes noires PC + mobile */
        .hero-slide > img,
        .hero-slide.is-portrait > img,
        .hero-slide.is-lowres > img {
            object-fit: cover !important;
            object-position: center center !important;
            width: 100% !important;
            height: 100% !important;
        }
        .hero-slide-bg { display: none !important; }
        .hero-slider { background: #0b1620; }

        /* Couleur du texte sur les images en noir */
        .home-slider-status {
            color: #FFF !important;
        }

        @media (max-width: 900px) {
            .why-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .why-item:last-child:nth-child(3n+1) { grid-column: auto; }
            .why-item:last-child:nth-child(odd) { grid-column: 1 / -1; }
            /* Réduire l'espace sur mobile/tablette */
            #ecole.container_pourquoi { margin-top: 20px; }
        }
        @media (max-width: 560px) {
            .why-grid { grid-template-columns: 1fr; }
            .why-item:last-child:nth-child(odd) { grid-column: auto; }
            .why-item { padding: 16px 16px 18px; border-radius: 16px; }
            /* Réduire encore l'espace sur smartphone */
            #ecole.container_pourquoi { margin-top: 10px; }
        }
    </style>
</head>

<body>

<header class="header">

    <nav class="navbar">

        <!-- LOGO -->
      <a href="#" class="logo">
    <img src="assets/img/logo-hemip.jpg" alt="Logo HEMIP">
    <span>HEMIP</span>
</a>

        <!-- BOUTON MOBILE -->
        <button class="menu-btn" id="menu-btn" type="button" aria-label="Ouvrir le menu de navigation" aria-expanded="false" aria-controls="nav-list">
            <span aria-hidden="true">☰</span>
        </button>

        <!-- MENU -->
        <ul class="nav-list" id="nav-list">

            <li>
                <a href="index.php">Accueil</a>
            </li>

            <!-- DROPDOWN FORMATION -->
            <li class="dropdown" id="formations">

                <button class="dropdown-btn" aria-expanded="false">
                    Formation
                    <i class="ri-arrow-down-s-line"></i>
                </button>

                <ul class="dropdown-menu">

                    <!-- SOUS MENU pole technique-->
                    <li class="submenu">

                        <button class="submenu-btn">
                            Pôle technique

                            <i class="ri-arrow-right-s-line"></i>
                        </button>

                        <ul class="submenu-menu">
                            <li><a href="GP.html">Génie Pétrolier</a></li>
                            <li><a href="GL.html">Génie Logiciel</a></li>
                            <li><a href="RT.html">Réseaux et Télécommunication</a></li>
                            <li><a href="MI.html">Maintenance Industrielle</a></li>
                            <li><a href="AII.html">Automatisation et Informatique Industriel</a></li>
                            <li><a href="EE.html">Électrotechnique et Électronique</a></li>
                            <li><a href="GC.html">Génie Chimique</a></li>
                            <li><a href="RP.html">Raffinage et Pétrochimie</a></li>
                            <li><a href="ENR.html">Énergies Renouvelables</a></li>

                        </ul>

                    </li>
                    

                    <!-- SOUS MENU -->
                    <li class="submenu">

                        <button class="submenu-btn">
                            Pôle Commercial
                            <i class="ri-arrow-right-s-line"></i>
                        </button>

                        <ul class="submenu-menu">
                            <li><a href="DAE.html">Droit des Affaires et des Entreprises</a></li>
                            <li><a href="GLT.html">Gestion Logistique et Transport</a></li>
                            <li><a href="BFA.html">Banque et Finance des Assurances</a></li>
                            <li><a href="CIT.html">Commerce International et Transit</a></li>
                           <li><a href="GFC.html">Gestion Finance et Comptable</a></li>       
                         <li><a href="MRH.html">Management des Ressources Humaines</a></li> 
                        </ul>

                    </li>

                </ul>

            </li>

            <li>
                <a href="inscription.php">Inscription</a>
            </li>

            <li>
                <a href="actualite.php">Actualités</a>
            </li>

            <li>
                <a href="realisations.php">Nos réalisations</a>
            </li>

            <li>
                <a href="contact.php">Contact</a>
            </li>

            <li><a href="faq.php">FAQ</a></li>

            <li>
                <a href="a-propos.php">À propos</a>
            </li>

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

<!-- CONTENUE DE LA PAGE -->

  <!-- ================= HERO ================= -->
        <section class="hero" id="accueil" data-home-carousel role="region" aria-roledescription="carrousel" aria-label="Accueil HEMIP : photos de l’école">

            <div class="hero-background"></div>

            <div class="hero-slider" aria-hidden="false">
                <div class="hero-slide is-active" id="home-slide-1" role="group" aria-roledescription="diapositive" aria-label="1 sur 11 : La façade HEMIP" aria-hidden="false" data-slide-label="La façade HEMIP">
                    <img src="assets/img/hemip-campus.jpg" alt="Façade et enseigne de la Haute École de Management et de l’Ingénierie la Percée" fetchpriority="high" decoding="async">
                </div>
                <div class="hero-slide" id="home-slide-2" role="group" aria-roledescription="diapositive" aria-label="2 sur 11 : Une cérémonie à HEMIP" aria-hidden="true" data-slide-label="Une cérémonie à HEMIP">
                    <img src="assets/img/hemip-ceremonie.jpg" alt="Étudiants et enseignants réunis dans une salle de classe lors d’une cérémonie HEMIP" loading="eager" fetchpriority="low" decoding="async">
                </div>
                <div class="hero-slide" id="home-slide-3" role="group" aria-roledescription="diapositive" aria-label="3 sur 11 : L’ouverture du Master professionnel" aria-hidden="true" data-slide-label="L’ouverture du Master professionnel">
                    <img src="assets/img/hemip-presentation.jpeg" alt="Responsables devant la banderole d’ouverture du Master professionnel en partenariat avec l’ENSI" loading="eager" fetchpriority="low" decoding="async" style="object-position: 50% 45%;">
                </div>
                <div class="hero-slide is-lowres" id="home-slide-4" role="group" aria-roledescription="diapositive" aria-label="4 sur 11 : Un cours en salle de classe" aria-hidden="true" data-slide-label="Un cours en salle de classe">
                    <span class="hero-slide-bg" aria-hidden="true" style="background-image: url('assets/img/class-hemip-cours.jpg');"></span>
                    <img src="assets/img/class-hemip-cours.jpg" alt="Enseignante présentant un cours avec projection devant des étudiants" loading="eager" fetchpriority="low" decoding="async">
                </div>
                <div class="hero-slide is-lowres" id="home-slide-5" role="group" aria-roledescription="diapositive" aria-label="5 sur 11 : L’apprentissage informatique" aria-hidden="true" data-slide-label="L’apprentissage informatique">
                    <span class="hero-slide-bg" aria-hidden="true" style="background-image: url('assets/img/hemip-informatique.jpg');"></span>
                    <img src="assets/img/hemip-informatique.jpg" alt="Étudiants en séance de travail devant des ordinateurs" loading="eager" fetchpriority="low" decoding="async">
                </div>
                <div class="hero-slide is-lowres is-portrait" id="home-slide-6" role="group" aria-roledescription="diapositive" aria-label="6 sur 11 : Le laboratoire de chimie" aria-hidden="true" data-slide-label="Le laboratoire de chimie">
                    <span class="hero-slide-bg" aria-hidden="true" style="background-image: url('assets/img/classe-hemip-science.jpg');"></span>
                    <img src="assets/img/classe-hemip-science.jpg" alt="Étudiants en blouse blanche en séance de travaux pratiques au laboratoire de chimie" loading="eager" fetchpriority="low" decoding="async" style="object-position: 50% 40%;">
                </div>
                <div class="hero-slide is-lowres" id="home-slide-7" role="group" aria-roledescription="diapositive" aria-label="7 sur 11 : La pratique en atelier" aria-hidden="true" data-slide-label="La pratique en atelier">
                    <span class="hero-slide-bg" aria-hidden="true" style="background-image: url('assets/img/hemip-atelier.jpg');"></span>
                    <img src="assets/img/hemip-atelier.jpg" alt="Étudiants en bleu de travail démontant un moteur en atelier" loading="eager" fetchpriority="low" decoding="async">
                </div>
                <div class="hero-slide is-portrait" id="home-slide-8" role="group" aria-roledescription="diapositive" aria-label="8 sur 11 : La mécanique en atelier" aria-hidden="true" data-slide-label="La mécanique en atelier">
                    <span class="hero-slide-bg" aria-hidden="true" style="background-image: url('assets/img/TDB.jpeg');"></span>
<img src="assets/img/TDB.jpeg" alt="Étudiants en bleu de travail autour d’une culasse de moteur dans l’atelier de mécanique" loading="eager" fetchpriority="low" decoding="async" style="object-position: 50% 30%;">
                </div>
                <div class="hero-slide is-lowres is-portrait" id="home-slide-9" role="group" aria-roledescription="diapositive" aria-label="9 sur 11 : Les travaux pratiques d’instrumentation" aria-hidden="true" data-slide-label="Les travaux pratiques d’instrumentation">
                    <span class="hero-slide-bg" aria-hidden="true" style="background-image: url('assets/img/TDA.jpeg');"></span>
                    <img src="assets/img/TDA.jpeg" alt="Étudiante manipulant des instruments de mesure lors d’un travail pratique" loading="eager" fetchpriority="low" decoding="async" style="object-position: 50% 30%;">
                </div>
                <div class="hero-slide is-lowres is-portrait" id="home-slide-10" role="group" aria-roledescription="diapositive" aria-label="10 sur 11 : Une démonstration sur moteur" aria-hidden="true" data-slide-label="Une démonstration sur moteur">
                    <span class="hero-slide-bg" aria-hidden="true" style="background-image: url('assets/img/TDC.jpeg');"></span>
                    <img src="assets/img/TDC.jpeg" alt="Étudiants rassemblés autour d’un moteur lors d’une démonstration pratique" loading="eager" fetchpriority="low" decoding="async" style="object-position: 50% 40%;">
                </div>
                <div class="hero-slide is-lowres is-portrait" id="home-slide-11" role="group" aria-roledescription="diapositive" aria-label="11 sur 11 : Les étudiants en séance pratique" aria-hidden="true" data-slide-label="Les étudiants en séance pratique">
                    <span class="hero-slide-bg" aria-hidden="true" style="background-image: url('assets/img/hemip-etudiants.jpg');"></span>
                    <img src="assets/img/hemip-etudiants.jpg" alt="Étudiants en bleu de travail réunis autour d’un moteur en séance pratique" loading="eager" fetchpriority="low" decoding="async" style="object-position: 50% 40%;">
                </div>
            </div>

            <div class="hero-content reveal">

                <h1>
                    Construisez votre
                    <span>avenir.</span>
                </h1>

                <p>
                    Bienvenue à la Haute École de Management et
                    de l'Ingénierie la Percée. Une formation d'excellence
                    pour préparer les talents de demain.
                </p>

                <div class="hero-buttons">

                    <a href="#formations" class="btn btn-primary">
                        Découvrir nos formations
                        <i class="ri-arrow-right-line"></i>
                    </a>

                    <a href="#ecole" class="btn btn-secondary">
                        Découvrir HEMIP
                    </a>

                </div>

                <div class="hero-info">

                    <div class="info-item">
                        <strong>+10</strong>
                        <span>Formations</span>
                    </div>

                    <div class="info-line"></div>

                    <div class="info-item">
                        <strong>+500</strong>
                        <span>Étudiants</span>
                    </div>

                    <div class="info-line"></div>

                    <div class="info-item">
                        <strong>100%</strong>
                        <span>Engagement</span>
                    </div>

                </div>

            </div>

            <div class="home-slider-controls" role="group" aria-label="Commandes des photos de HEMIP">
                <button type="button" data-slide-previous aria-label="Photo précédente"><span aria-hidden="true">‹</span></button>
                <div class="home-slider-dots" role="group" aria-label="Choisir une photo">
                    <button type="button" class="home-slider-dot" aria-current="true" aria-label="Afficher la photo 1 : La façade HEMIP" aria-controls="home-slide-1"></button>
                    <button type="button" class="home-slider-dot" aria-label="Afficher la photo 2 : Une cérémonie à HEMIP" aria-controls="home-slide-2"></button>
                    <button type="button" class="home-slider-dot" aria-label="Afficher la photo 3 : L’ouverture du Master professionnel" aria-controls="home-slide-3"></button>
                    <button type="button" class="home-slider-dot" aria-label="Afficher la photo 4 : Un cours en salle de classe" aria-controls="home-slide-4"></button>
                    <button type="button" class="home-slider-dot" aria-label="Afficher la photo 5 : L’apprentissage informatique" aria-controls="home-slide-5"></button>
                    <button type="button" class="home-slider-dot" aria-label="Afficher la photo 6 : Le laboratoire de chimie" aria-controls="home-slide-6"></button>
                    <button type="button" class="home-slider-dot" aria-label="Afficher la photo 7 : La pratique en atelier" aria-controls="home-slide-7"></button>
                    <button type="button" class="home-slider-dot" aria-label="Afficher la photo 8 : La mécanique en atelier" aria-controls="home-slide-8"></button>
                    <button type="button" class="home-slider-dot" aria-label="Afficher la photo 9 : Les travaux pratiques d’instrumentation" aria-controls="home-slide-9"></button>
                    <button type="button" class="home-slider-dot" aria-label="Afficher la photo 10 : Une démonstration sur moteur" aria-controls="home-slide-10"></button>
                    <button type="button" class="home-slider-dot" aria-label="Afficher la photo 11 : Les étudiants en séance pratique" aria-controls="home-slide-11"></button>
                </div>
                <button type="button" data-slide-toggle aria-pressed="false" aria-label="Mettre le défilement des photos en pause">Ⅱ</button>
                <button type="button" data-slide-next aria-label="Photo suivante"><span aria-hidden="true">›</span></button>
            </div>
            <p class="home-slider-status" aria-live="off" aria-atomic="true">01 | La façade HEMIP</p>

            <div class="hero-visual">

                <div class="circle circle-one"></div>
                <div class="circle circle-two"></div>

                <div class="floating-card card-top">
                    <i class="ri-graduation-cap-line"></i>
                    <div>
                        <strong>Formation</strong>
                        <span>Professionnelle</span>
                    </div>
                </div>

                <div class="hero-card">

                    <div class="card-icon">
                        <i class="ri-lightbulb-flash-line"></i>
                    </div>

                    <span>Notre vision</span>

                    <h3>
                        Former les leaders
                        de demain.
                    </h3>

                    <div class="card-line"></div>

                    <small>
                        Savoir • Savoir-faire • Savoir-être
                    </small>

                </div>

                <div class="floating-card card-bottom">
                    <i class="ri-code-s-slash-line"></i>
                    <div>
                        <strong>Innovation</strong>
                        <span>& Technologie</span>
                    </div>
                </div>

            </div>

            <div class="scroll-indicator">
                <span></span>
                
            </div>

        </section>
        
        
        
        
     <!-- section POURQUOI CHOISIR HEMIP-->  
      
        <section class="container_pourquoi" id="ecole">
    <!-- 1) Titre, aligné à gauche -->
    <div class="content_pourquoi why-head reveal">
        <h1> Pourquoi choisir
            <span class="titre_pourquoi"> HEMIP ?</span>
        </h1>
    </div>

    <!-- 2) Cartes (même présentation que l'équipe académique) puis bouton -->
    <div class="why-wide reveal">
        <div class="why-grid">
                <div class="why-item"><strong>Des enseignants qui comptent sur vous autant que vous comptez sur eux.</strong><p>Chez nous, vous êtes accompagné par des professeurs expérimentés, qu’ils vivent ici ou viennent d’ailleurs, et par des formateurs qui ont tous au moins un Master. Nos cours suivent le système LMD et se donnent en français comme en anglais, surtout pour les matières de base et le Génie des Procédés.</p></div>
                <div class="why-item"><strong>Les mains dans le concret.</strong><p>À HEMIP, on ne se contente pas d’écouter : on manipule, on essaie, on se trompe, on recommence. Nos laboratoires de Chimie, de Génie des Procédés et d’Informatique, et nos ateliers d’Électromécanique et d’Électronique sont là pour ça. Nos classes sont volontairement petites, pour que chaque étudiant soit connu, suivi et encouragé. Et parce que la vie ne s’arrête pas aux études, vous choisissez vos horaires : cours du jour ou du soir.</p></div>
                <div class="why-item"><strong>Des portes déjà ouvertes.</strong><p>Nous travaillons main dans la main avec des institutions d’enseignement supérieur reconnues et avec des entreprises que vous connaissez : SARIS, Dangote, CORAF, SNPC, La Congolaise des Eaux, Eni Congo et TotalEnergies.</p></div>
                <div class="why-item"><strong>Des stages qui changent une trajectoire.</strong><p>Ces partenariats permettent à nos étudiants de décrocher des stages qui font la différence. Plusieurs d’entre eux ont déjà signé un CDD ou un CDI. Leur réussite nous rend fiers, et elle peut devenir la vôtre.</p></div>
                <div class="why-item"><strong>Une recherche qui sert notre quotidien.</strong><p>Notre unité de recherche en Génie des Procédés ne travaille pas pour le plaisir de la théorie : elle cherche des réponses aux vrais besoins de nos communautés.</p></div>
                <div class="why-item"><strong>Et pourquoi pas votre propre entreprise ?</strong><p>Avec une solide formation et beaucoup de pratique, vous aurez tout ce qu’il faut pour vous lancer et créer votre start-up.</p></div>
                <div class="why-item"><strong>Parce que le mérite doit être récompensé.</strong><p>Nous attribuons des bourses d’études aux étudiants les plus méritants.</p></div>
        </div>
        <div class="hero-buttons why-cta">
            <a href="contact.php#contact" class="btn btn-primary">
                Nous contacter
                <i class="ri-arrow-right-line"></i>
            </a>
        </div>
    </div>
</section>

<!-- logo partenaire -->
<section class="partners">
    <div class="partners-container reveal">

        <h2>Nos partenaires académiques</h2>
        <p style="font-size:1rem;">Ils accompagnent nos étudiants dans leur développement.</p>

        <div class="logo-slider">
            <div class="logo-track">

                <!-- PREMIÈRE SÉRIE -->
                <div class="logo-item">
                    <img src="assets/img/ENGDE.jpg" alt="ENGDE">
                </div>

                <div class="logo-item">
                    <img src="assets/img/HESTIM.jpg" alt="HESTIM">
                </div>

                <div class="logo-item">
                    <img src="assets/img/ISTA.jpg" alt="ISTA">
                </div>

                <div class="logo-item">
                    <img src="assets/img/ORAGEU.jpg" alt="ORAGEU">
                </div>

                <div class="logo-item">
                    <img src="assets/img/PDSAS.jpg" alt="PDSAS">
                </div>

                <!-- DEUXIÈME SÉRIE IDENTIQUE -->
                <div class="logo-item">
                    <img src="assets/img/ENGDE.jpg" alt="ENGDE">
                </div>

                <div class="logo-item">
                    <img src="assets/img/HESTIM.jpg" alt="HESTIM">
                </div>

                <div class="logo-item">
                    <img src="assets/img/ISTA.jpg" alt="ISTA">
                </div>

                <div class="logo-item">
                    <img src="assets/img/ORAGEU.jpg" alt="ORAGEU">
                </div>

                <div class="logo-item">
                    <img src="assets/img/PDSAS.jpg" alt="PDSAS">
                </div>

                
                <!-- TROISIEME SÉRIE IDENTIQUE -->
                <div class="logo-item">
                    <img src="assets/img/ENGDE.jpg" alt="ENGDE">
                </div>

                <div class="logo-item">
                    <img src="assets/img/HESTIM.jpg" alt="HESTIM">
                </div>

                <div class="logo-item">
                    <img src="assets/img/ISTA.jpg" alt="ISTA">
                </div>

                <div class="logo-item">
                    <img src="assets/img/ORAGEU.jpg" alt="ORAGEU">
                </div>

                <div class="logo-item">
                    <img src="assets/img/PDSAS.jpg" alt="PDSAS">
                </div>

            </div>
        </div>
<br>
<br>
        <h2>Nos partenaires professionnels</h2>
        <p style="font-size:1rem;">Ils nous accompagnent dans notre vision et notre développement.</p>

        <div class="logo-slider">
            <div class="logo-track">

                <!-- PREMIÈRE SÉRIE -->
                <div class="logo-item">
                    <img src="assets/img/SUNU-Assurances.png" alt="SUNU-Assurances">
                </div>

                <div class="logo-item">
                    <img src="assets/img/Congo-Telecom.png" alt="Congo-Telecom">
                </div>

                <div class="logo-item">
                    <img src="assets/img/sch.png" alt="SCHLUMBERGER">
                </div>

                <div class="logo-item">
                    <img src="assets/img/A.Sice.jpg" alt="Adolphe-Sice">
                </div>

                <div class="logo-item">
                    <img src="assets/img/OFIS.jpg" alt="OFIS">
                </div>

                <!-- DEUXIÈME SÉRIE IDENTIQUE -->
                <div class="logo-item">
                    <img src="assets/img/SUNU-Assurances.png" alt="SUNU-Assurances">
                </div>

                <div class="logo-item">
                    <img src="assets/img/Congo-Telecom.png" alt="Congo-Telecom">
                </div>

                <div class="logo-item">
                    <img src="assets/img/sch.png" alt="SCHLUMBERGER">
                </div>

                <div class="logo-item">
                    <img src="assets/img/A.Sice.jpg" alt="Adolphe-Sice">
                </div>

                <div class="logo-item">
                    <img src="assets/img/OFIS.jpg" alt="OFIS">
                </div>

                
                <!-- TROISIEME SÉRIE IDENTIQUE -->
                <div class="logo-item">
                    <img src="assets/img/SUNU-Assurances.png" alt="SUNU-Assurances">
                </div>

                <div class="logo-item">
                    <img src="assets/img/Congo-Telecom.png" alt="Congo-Telecom">
                </div>

                <div class="logo-item">
                    <img src="assets/img/sch.png" alt="SCHLUMBERGER">
                </div>

                <div class="logo-item">
                    <img src="assets/img/A.Sice.jpg" alt="Adolphe-Sice">
                </div>

                <div class="logo-item">
                    <img src="assets/img/OFIS.jpg" alt="OFIS">
                </div>

            </div>
        </div>

    </div>
</section>

<!-- SECTION GOOGLE MAPS HEMIP -->
<section class="location-section" id="localisation">

    <div class="location-container">

        <!-- Texte -->
        <div class="location-content">
          
           <h2>
                Retrouvez-nous<br>
                <span>à HEMIP</span>
            </h2>

            <p class="reveal">
                Venez nous rendre visite dans nos locaux.
                Retrouvez facilement l'emplacement de la
                Haute École de Management et de l'Ingénierie la Percée.
            </p>

            <div class="address">
                <i class="ri-map-pin-2-fill"></i>
                <div class="reveal">
                    <strong>Adresse</strong>
                    <span>Pointe-Noire, Centre ville, derrière la tour MAYOMBE</span>
                </div>
            </div>

            <a
                href="https://www.google.com/maps/place/HEMIP-haute+ecole+de+management+et+d'ingenieurie/@-4.7942983,11.8461233,17z/data=!3m1!4b1!4m6!3m5!1s0x1a60a573fa62b02f:0x5bbf0c8430ce4707!8m2!3d-4.7943037!4d11.8486982!16s%2Fg%2F11l8gl9p3l?hl=fr-FR&entry=ttu&g_ep=EgoyMDI2MDkyOS4wIKXMDSoASAFQAw%3D%3D"
                target="_blank"
                class="map-button"
            >
                <i class="ri-navigation-fill"></i>
                Voir l'itinéraire
            </a>
        </div>

        <!-- Google Maps -->
        <div class="map-container reveal">

            <iframe
                src="https://www.google.com/maps?q=HEMIP-haute+ecole+de+management+et+d'ingenieurie/@-4.7942983,11.8461233,17z/data=!3m1!4b1!4m6!3m5!1s0x1a60a573fa62b02f:0x5bbf0c8430ce4707!8m2!3d-4.7943037!4d11.8486982!16s%2Fg%2F11l8gl9p3l?hl=fr-FR&entry=ttu&g_ep=EgoyMDI2MDkyOS4wIKXMDSoASAFQAw%3D%3D&output=embed"
                loading="lazy"
                allowfullscreen
                referrerpolicy="no-referrer-when-downgrade">
            </iframe>

        </div>

    </div>

</section>

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

                <li>
                    <i class="ri-whatsapp-line"></i>
                    <a href="https://wa.me/242055865094" target="_blank">
                        WhatsApp
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
            Haute École de Management et de l'Ingénierie la Percée
        </p>
    </div>
</footer>

<script src="<?= asset('assets/js/main.js') ?>"></script>
  <script src="<?= asset('assets/js/home-sections.js') ?>" defer></script>
  <script src="<?= asset('assets/js/marquee.js') ?>" defer></script>

  <script src="<?= asset('assets/js/pwa.js') ?>" defer></script>
</body>
</html>