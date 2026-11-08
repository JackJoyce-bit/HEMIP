<?php
if (!function_exists('asset')) { function asset($p) { $f = __DIR__ . '/' . $p; return $p . '?v=' . (is_file($f) ? filemtime($f) : '1'); } }
session_start();
$adminConnecte = isset($_SESSION['admin_connecte']) && $_SESSION['admin_connecte'] === true;
?>
<!DOCTYPE html>
<html lang="fr">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<title>À propos | HEMIP</title>
<link rel="stylesheet" href="<?= asset('assets/css/style.css') ?>">
<link rel="stylesheet" href="<?= asset('assets/vendor/fonts/fonts.css') ?>">
<link href="<?= asset('assets/vendor/remixicon/remixicon.css') ?>" rel="stylesheet">
<link rel="icon" type="image/png" href="assets/icons/hemip-192.png">
<style>
        /* Augmentation de la taille du texte pour les sections de contenu */
        .about-card p, .about-card li, .about-grid p, .about-grid li {
            font-size: 1.1rem;
            line-height: 1.8;
        }

        .about-hero {
            padding : 120px 20px 80px;
            background : linear-gradient(135deg,#071d33,#0b365b);
            color : #fff;
            text-align : center
        }

        .about-hero .eyebrow {
            display : inline-block;
            padding : 8px 15px;
            border : 1px solid rgba(255,255,255,.25);
            border-radius : 30px;font-size:.85rem;
            letter-spacing:.08em;
            text-transform : uppercase
        }

        .about-hero h1 {
            font-size : clamp(2.4rem,6vw,4.6rem);
            margin : 18px 0 12px;
            font-family : 'Playfair Display',serif
        }

        .about-hero p {
            max-width : 760px;
            margin : auto;
            line-height : 1.8;
            color : rgba(255,255,255,.82);
        }

        .about-wrap{
            max-width : 1180px;
            margin : auto;
            padding : 80px 20px
        }

        .about-section {
            margin-bottom : 70px
        }

        .about-heading {
            display : flex;
            gap : 16px;
            align-items : flex-start;
            margin-bottom : 24px
        }

        .about-icon {
            width : 54px;
            height : 54px;
            flex : 0 0 54px;
            display : grid;
            place-items : center;
            border-radius : 16px;
            background : #e9f3fb;
            color : #0a527e;
            font-size : 25px
        }
        .about-heading h2 {
            margin : 0;
            font-family : 'Times New Roman',serif;
            font-size : 2rem;
            color : #0b2942
        }
        .about-heading p {
            margin : 7px 0 0;
            color : #6b7785
        }
        .about-card {
            background : #fff;
            border : 1px solid #e7edf3;
            border-radius : 22px;padding : 30px;
            box-shadow : 0 12px 35px rgba(12,42,67,.07);
            line-height : 1.8;color : #45515d
        }

        .about-grid {
            display : grid;
            grid-template-columns : repeat(2,minmax(0,1fr));
            gap : 22px
        }

        .about-list {
            display : grid;
            grid-template-columns : repeat(2,minmax(0,1fr));
            gap : 12px;
            padding : 0;
            margin : 0;
            list-style : none
        }

        .about-list li {
            padding : 14px 16px;
            border-radius : 14px;
            background : #f6f9fc;
            border : 1px solid #e9eff5
        }

        .staff-grid {
            display : grid;
            grid-template-columns : repeat(3,minmax(0,1fr));
            gap : 14px
        }

        .staff{
            padding : 17px;
            border : 1px solid #e6edf3;
            border-radius : 16px;
            background : #fff
        }

        .staff strong{
            display : block;
            color : #102d45;
            margin-bottom : 4px
        }

        .staff span{
            font-size : .9rem;
            color : #687583
        }

        /* ---------- Recherche, Innovations & Développement ---------- */

        .rid-intro {
            margin : 0 0 22px;
            line-height : 1.8;
            color : #45515d
        }

        .rid-titre {
            display : flex;
            gap : 14px;
            align-items : center;
            margin-bottom : 6px
        }

        .rid-ico {
            width : 46px;
            height : 46px;
            flex : 0 0 46px;
            display : grid;
            place-items : center;
            border-radius : 14px;
            background : #e9f3fb;
            color : #0a527e;
            font-size : 22px
        }

        .rid-card h3 {
            margin : 0;
            font-size : 1.2rem;
            color : #0f5085
        }

        .rid-card small {
            display : block;
            margin-top : 3px;
            font-size : .85rem;
            color : #6b7785
        }

        .rid-label {
            margin : 20px 0 9px;
            font-size : .78rem;
            font-weight : 700;
            letter-spacing : .08em;
            text-transform : uppercase;
            color : #1094d7
        }

        .rid-sous {
            margin : 0 0 9px;
            font-weight : 600;
            color : #102d45
        }

        .rid-puces {
            display : flex;
            flex-wrap : wrap;
            gap : 8px;
            padding : 0;
            margin : 0;
            list-style : none
        }

        .rid-puces li {
            padding : 7px 14px;
            border-radius : 30px;
            background : #f6f9fc;
            border : 1px solid #e9eff5;
            font-size : .9rem;
            line-height : 1.4;
            color : #35424e
        }

        .rid-clients li {
            background : #102d45;
            border-color : #102d45;
            color : #fff
        }

        .rid-liste {
            display : grid;
            gap : 9px;
            padding : 0;
            margin : 0;
            list-style : none
        }

        .rid-liste li {
            position : relative;
            padding-left : 24px;
            line-height : 1.65
        }

        .rid-liste li::before {
            content : "";
            position : absolute;
            left : 2px;
            top : .62em;
            width : 9px;
            height : 9px;
            border-radius : 50%;
            background : #1094d7
        }

        .rid-sep {
            margin : 16px 0 14px;
            border : 0;
            border-top : 1px dashed #d8e2ec
        }

        @media(max-width:800px){
            .about-grid,.about-list,.staff-grid{
                grid-template-columns : 1fr
            }
            .about-wrap{
                padding : 55px 18px
            }
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
        <a href="index.php" class="logo">
            <img src="assets/img/logo-hemip.jpg" alt="Logo HEMIP">
            <span>HEMIP</span>
        </a>
        <button class="menu-btn" id="menu-btn" type="button" aria-label="Ouvrir le menu de navigation" aria-expanded="false" aria-controls="nav-list">
            <i class="ri-menu-line"></i>
        </button>
        <ul class="nav-list" id="nav-list">
            <li>
                <a href="index.php">Accueil</a>
            </li>
            <li class="dropdown">
                <button class="dropdown-btn" type="button">Formation <i class="ri-arrow-down-s-line"></i>
            </button><ul class="dropdown-menu">
                    <li class="submenu"><button class="submenu-btn" type="button">Pôle technique <i class="ri-arrow-right-s-line"></i></button><ul class="submenu-menu">
                        <li><a href="GP.html">Génie Pétrolier</a></li>
                        <li><a href="GL.html">Génie Logiciel</a></li>
                        <li><a href="RT.html">Réseaux et Télécommunication</a></li>
                        <li><a href="MI.html">Maintenance Industrielle</a></li>
                        <li><a href="AII.html">Automatisation et Informatique Industriel</a></li>
                        <li><a href="EE.html">Électrotechnique et Électronique</a></li>
                        <li><a href="GC.html">Génie Chimique</a></li>
                        <li><a href="RP.html">Raffinage et Pétrochimie</a></li>
                        <li><a href="ENR.html">Énergies Renouvelables</a></li>
                    </ul></li>
                    <li class="submenu"><button class="submenu-btn" type="button">Pôle Commercial <i class="ri-arrow-right-s-line"></i></button><ul class="submenu-menu">
                        <li><a href="DAE.html">Droit des Affaires et des Entreprises</a></li>
                        <li><a href="GLT.html">Gestion Logistique et Transport</a></li>
                        <li><a href="BFA.html">Banque et Finance des Assurances</a></li>
                        <li><a href="CIT.html">Commerce International et Transit</a></li>
                        <li><a href="GFC.html">Gestion Finance et Comptable</a></li>
                        <li><a href="MRH.html">Management des Ressources Humaines</a></li>
                    </ul></li>
                </ul></li>

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
                    <a href="a-propos.php" class="active">À propos</a>
                </li>
        <?php if($adminConnecte): ?>
            <li>
                <a href="dashboard.php" title="Tableau de bord">
                    <i class="ri-dashboard-line"></i>
                </a></li>
                <li class="admin-status">
                    <i class="ri-admin-line" style="color: var(--hemip-bg);"></i>
                    <span style="color: var(--hemip-bg); font-size: 15px;">Connecté</span>
                </li>
        <?php else: ?>
            <li>
                <a href="login.php" title="Connexion administrateur">
                    <i class="ri-admin-line"></i>
                </a>
            </li>
        <?php endif; ?>

        </ul></nav>
    </header>

    <section class="about-hero">
        <span class="eyebrow">Découvrir HEMIP</span>
        <h1>À propos de HEMIP</h1><p>HEMIP est un établissement privé d’enseignement supérieur, définitivement agréé par le ministère de l’Enseignement supérieur de la République du Congo. Présente à Pointe-Noire depuis 2013, l’institution accompagne les étudiants dans leur formation et les aide à acquérir les connaissances et les compétences nécessaires pour réussir leur insertion dans le monde professionnel.</p>
    </section>

    <main class="about-wrap">

    <section class="about-section">
        <div class="about-heading">
            <div class="about-icon">
                <i class="ri-history-line"></i>
            </div>
            <div>
                <h2>Historique et Statut</h2>
                <p>Quelques repères sur l’établissement.</p>
            </div>
        </div>
        <div class="about-card">
            <p>La Haute Ecole de Management et de l’Ingénierie La Percée (HEMIP en sigle) est une institution tertiaire privée et agréée définitivement par le ministère de l’Enseignement Supérieur de la République du Congo qui existe depuis 2013 et localisée à Pointe-Noire, avec une extension à Nkayi.</p>
            <p>Depuis sa création jusqu’à ce jour, HEMIP a déjà gradué des centaines de diplômés : Techniciens Supérieurs &amp; Licenciés Professionnels ; lesquels pour la plupart sont déjà dans la vie active professionnelle en tant qu’agents ou cadres supérieurs.</p>
        </div>
    </section>

    <section class="about-section">
        <div class="about-heading">
            <div class="about-icon">
                <i class="ri-compass-3-line"></i>
            </div>
            <div>
                <h2>Vision et Mission d’HEMIP</h2>
                <p>Former, rechercher et préparer à la vie professionnelle.</p>
            </div>
        </div>
        <div class="about-grid">
            <div class="about-card">
                <h3>Vision</h3>
                <p>Devenir une des meilleures institutions supérieures en Afrique.</p>
            </div>
            <div class="about-card">
                <h3>Mission</h3>
                <p>Assurer une formation de qualité (Système LMD avec des programmes de cours bien élaborés) des cadres dans les domaines de l’Ingénierie et du Management, avec des enseignants qualifiés, professionnels et innovateurs.</p>
                <p>Organiser la recherche scientifique orientée et pratique au sein d’une unité de recherche pour des solutions adaptées aux problèmes divers inhérents à la société.</p>
                <p>Préparer les étudiants à la vie professionnelle dans les industries, entreprises bancaires et autres organisations, ou encore pour des start-ups.</p>
            </div>
        </div>
    </section>

    <section class="about-section">
        <div class="about-heading">
            <div class="about-icon">
                <i class="ri-building-4-line"></i>
            </div>
            <div>
                <h2>Nos infrastructures & équipements</h2>
                <p>Les infrastructures et moyens présentés dans les locaux d'HEMIP.</p>
            </div>
        </div>
        <div class="about-grid">
            <div class="about-card">
                <h3>Infrastructures</h3>
                <ul class="about-list">
                    <li>14 salles de classe</li>
                    <li>Atelier mécanique</li>
                    <li>Atelier électrique</li>
                    <li>Laboratoire de chimie</li>
                    <li>Laboratoire d’informatique</li>
                    <li>Laboratoire d’électronique</li>
                    <li>760 étudiants (site de Pointe-Noire)</li>
                </ul>
            </div>
            <div class="about-card">
                <h3>Logiciels disponibles</h3>
                <ul class="about-list">
                    <li>Visual Studio</li>
                    <li>Algobox</li>
                    <li>Cisco Packet Tracer</li>
                    <li>Autocad</li>
                    <li>Solidworks</li>
                    <li>Matlab</li>
                    <li>EnyB</li>
                    <li>Bsim-1</li>
                    <li>Sage</li>
                </ul>
            </div>
        </div>
    </section>

    <section class="about-section" id="recherche">
        <div class="about-heading">
            <div class="about-icon">
                <i class="ri-flask-line"></i>
            </div>
            <div>
                <h2>Recherche, Innovations & Développement</h2>
                <p>Nos secteurs d’intervention, nos produits et nos clients potentiels.</p>
            </div>
        </div>
        <div class="about-card">
            <p>Chez HEMIP, nous sommes convaincus que chaque idée peut devenir une solution et que la recherche ouvre la voie à de nouvelles possibilités. C’est pourquoi nous mettons l’innovation et le savoir-faire au service de projets concrets, adaptés aux besoins des entreprises et aux réalités du terrain.</p>
            <p>À travers nos activités en Mining & Minéralurgie, Génie Industriel et Consultance, nous souhaitons valoriser les ressources locales, partager les connaissances et contribuer à un développement durable.</p>
            <br>
            <strong>HEMIP, transformer les idées en solutions pour construire l’avenir.</strong>
        </div>
<br>
<br>
        <p class="rid-intro">HEMIP intervient dans quatre secteurs : l’énergie et les hydrocarbures, les mines et la minéralurgie, le génie industriel et la consultance.</p>

        <div class="about-grid">

            <div class="about-card rid-card">
                <div class="rid-titre">
                    <div class="rid-ico"><i class="ri-flashlight-line"></i></div>
                    <div>
                        <h3>Énergies &amp; Hydrocarbures</h3>
                        <small>Énergies renouvelables, raffinage, pétrochimie</small>
                    </div>
                </div>

                <a class="rid-figure" href="assets/img/hemip-energies-hydrocarbures.png" target="_blank" rel="noopener" title="Toucher pour agrandir">
                    <img src="assets/img/hemip-energies-hydrocarbures.png" alt="Schéma Énergies et Hydrocarbures : du pétrole brut au raffinage, de la biomasse aux biocarburants et des énergies renouvelables à l’électricité." width="400" height="200" loading="lazy" decoding="async">
                    <span class="rid-zoom"><i class="ri-zoom-in-line" aria-hidden="true"></i> Toucher pour agrandir</span>
                </a>

                <p class="rid-label">Produits et services</p>
                <p class="rid-sous">Production semi-industrielle :</p>
                <ul class="rid-puces">
                    <li>Essence</li>
                    <li>Kérosène</li>
                    <li>GPL</li>
                    <li>Biogaz</li>
                    <li>Biocarburant</li>
                    <li>Biodiesel</li>
                    <li>Bioéthanol</li>
                    <li>Électricité (thermique-bio, solaire, hydro…)</li>
                </ul>

                <p class="rid-label">Clients potentiels</p>
                <ul class="rid-puces rid-clients">
                    <li>Coraf</li>
                    <li>Eni</li>
                    <li>AOGC</li>
                    <li>Perenco</li>
                    <li>E2C</li>
                    <li>Population</li>
                    <li>Transporteurs</li>
                    <li>Trident</li>
                    <li>SNPC</li>
                    <li>TotalEnergies…</li>
                </ul>
            </div>

            <div class="about-card rid-card">
                <div class="rid-titre">
                    <div class="rid-ico"><i class="ri-hammer-line"></i></div>
                    <div>
                        <h3>Mining &amp; Minéralurgie</h3>
                        <small>Secteur minier</small>
                    </div>
                </div>

                <a class="rid-figure" href="assets/img/hemip-mining-mineralurgie.png" target="_blank" rel="noopener" title="Toucher pour agrandir">
                    <img src="assets/img/hemip-mining-mineralurgie.png" alt="Schéma Mining et Minéralurgie : plans d’affaires miniers, projets d’exploitation optimale et exploitation des calcaires en ciments et craies." width="2400" height="1400" loading="lazy" decoding="async">
                    <span class="rid-zoom"><i class="ri-zoom-in-line" aria-hidden="true"></i> Toucher pour agrandir</span>
                </a>

                <p class="rid-label">Produits et services</p>
                <ul class="rid-liste">
                    <li>Établissement de plans d’affaires miniers</li>
                    <li>Projets d’exploitation optimale d’or, de coltan, de cuivre, de cobalt, de palladium, de platine…</li>
                    <li>Exploitation des calcaires pour la production de ciments et de craies…</li>
                </ul>

                <p class="rid-label">Clients potentiels</p>
                <ul class="rid-puces rid-clients">
                    <li>Dangote</li>
                    <li>Anglogold</li>
                    <li>GCM</li>
                    <li>CILU</li>
                    <li>Entrepreneurs…</li>
                </ul>
            </div>

            <div class="about-card rid-card">
                <div class="rid-titre">
                    <div class="rid-ico"><i class="ri-settings-3-line"></i></div>
                    <div>
                        <h3>Génie Industriel</h3>
                        <small>Production et services techniques</small>
                    </div>
                </div>

                <a class="rid-figure" href="assets/img/hemip-genie-industriel.png" target="_blank" rel="noopener" title="Toucher pour agrandir">
                    <img src="assets/img/hemip-genie-industriel.png" alt="Schéma Génie Industriel : production semi-industrielle et services techniques." width="2400" height="1540" loading="lazy" decoding="async">
                    <span class="rid-zoom"><i class="ri-zoom-in-line" aria-hidden="true"></i> Toucher pour agrandir</span>
                </a>

                <p class="rid-label">Produits et services</p>
                <p class="rid-sous">Production semi-industrielle :</p>
                <ul class="rid-puces">
                    <li>Eau potable</li>
                    <li>Catalyseurs</li>
                    <li>Craies</li>
                    <li>Peintures</li>
                    <li>Ciments</li>
                    <li>Sucres, alcools…</li>
                    <li>Pièces mécaniques</li>
                    <li>Détergents</li>
                    <li>Biofertilisants</li>
                    <li>Engrais chimiques…</li>
                </ul>

                <hr class="rid-sep">

                <ul class="rid-liste">
                    <li>Développement de logiciels</li>
                    <li>Forage d’eau</li>
                    <li>Installation de systèmes solaires photovoltaïques</li>
                </ul>

                <p class="rid-label">Clients potentiels</p>
                <ul class="rid-puces rid-clients">
                    <li>Agriculteurs</li>
                    <li>Populations</li>
                    <li>Saris Congo</li>
                    <li>Dangote</li>
                    <li>Congolaise des eaux</li>
                    <li>Écoles</li>
                    <li>Universités</li>
                    <li>Entreprises…</li>
                </ul>
            </div>

            <div class="about-card rid-card">
                <div class="rid-titre">
                    <div class="rid-ico"><i class="ri-lightbulb-line"></i></div>
                    <div>
                        <h3>Consultance</h3>
                        <small>Formation, études et ingénierie</small>
                    </div>
                </div>

                <a class="rid-figure" href="assets/img/hemip-consultance.png" target="_blank" rel="noopener" title="Toucher pour agrandir">
                    <img src="assets/img/hemip-consultance.png" alt="Schéma Consultance : formation de courte durée, renforcement des capacités, projets d’ingénierie, études techniques et environnementales." width="2400" height="1560" loading="lazy" decoding="async">
                    <span class="rid-zoom"><i class="ri-zoom-in-line" aria-hidden="true"></i> Toucher pour agrandir</span>
                </a>

                <p class="rid-label">Produits et services</p>
                <ul class="rid-liste">
                    <li>Formation de courte durée</li>
                    <li>Renforcement des capacités</li>
                    <li>Études techniques au profit des entreprises pétrolières (production, raffinage et pétrochimie…), de construction et agroalimentaires</li>
                    <li>Projets d’ingénierie</li>
                    <li>Études environnementales (EIE, PGES…)</li>
                </ul>

                <p class="rid-label">Clients potentiels</p>
                <ul class="rid-puces rid-clients">
                    <li>Entreprises chimiques et pétrolières</li>
                    <li>Universités</li>
                    <li>Population</li>
                    <li>Constructeurs…</li>
                </ul>
            </div>

        </div>
    </section>

    <section class="about-section">
        <div class="about-heading">
            <div class="about-icon">
                <i class="ri-team-line"></i>
            </div>
            <div>
                <h2>Equipes/Personnel d'HEMIP</h2>
                <p>Les équipes qui accompagnent la vie académique et administrative de HEMIP.</p>
            </div>
        </div>

        <div class="about-card">
            <h3>Notre équipe académique</h3>
            <p>Corps enseignants et chercheurs - Parcours Technologie & Industrie.</p><br>
            <div class="staff-grid">
                <div class="staff"><strong>Dr. KIEMBA</strong><span>Chimie pure</span></div>
                <div class="staff"><strong>Dr. MBENGUELE Martial</strong><span>Management</span></div>
                <div class="staff"><strong>Dr. NZAOU</strong><span>Littérature Française</span></div>
                <div class="staff"><strong>Dr. Fabrice KAMPIAMBA</strong><span>Génie des Procédés</span></div>
                <div class="staff"><strong>Dr. Wighens NGOIE</strong><span>Génie Chimique</span></div>
                <div class="staff"><strong>Dr. Lauriane MENANKUTIMA</strong><span>Raffinage & Pétrochimie</span></div>
                <div class="staff"><strong>Ir. Joseph KOMBI</strong><span>Raffinage & Pétrochimie</span></div>
                <div class="staff"><strong>Ir. Dugèce DIAYIKA</strong><span>Génie Numérique</span></div>
                <div class="staff"><strong>Ir. Junior MALATOU</strong><span>Electronique</span></div>
                <div class="staff"><strong>Ir. Ferdinand MUKOKO</strong><span>Génie Electro-Mécanique</span></div>
                <div class="staff"><strong>Ir. Thryphon MUNGONGO</strong><span>Servo-Automatisme</span></div>
                <div class="staff"><strong>Ir. Dorian TETA</strong><span>Electromécanique & Production Pétrolière</span></div>
                <div class="staff"><strong>Ir. Van EKOKO</strong><span>Génie des Procédés</span></div>
                <div class="staff"><strong>Ir. Christian KAYEMBE</strong><span>Génie Electrique et Energies Renouvelables</span></div>
            </div>
        </div>
<br>
<br>
        <div class="about-card">
            <h3>Notre équipe administrative</h3>
            <p>Directeurs et gestionnaires d'HEMIP.</p><br>
            <div class="staff-grid">
                <div class="staff"><img class="staff-photo" src="assets/img/M KIBANGOU.jpeg" alt="" width="300" height="300" loading="lazy"><strong>M. KIBANGOU</strong><span>Promoteur & Directeur Général de l’institut</span></div>
                <div class="staff"><img class="staff-photo" src="assets/img/M. Arthur.jpeg" alt="" width="300" height="300" loading="lazy"><strong>Pr. NSEKA MPELA Arthur</strong><span>Directeur des Affaires académiques</span><p><u>arthurmpela123@gmail.com</u></p></div>
                <div class="staff"><img class="staff-photo" src="assets/img/M. KOUENA.jpeg" alt="" width="300" height="300" loading="lazy"><strong>Dr. KOUENA MABIKA Louis</strong><span>Secrétaire académique</span></div>
                <div class="staff"><img class="staff-photo" src="assets/img/user-icon.jpg" alt="" width="300" height="300" loading="lazy"><strong>Mme BAMVI Liliane</strong><span>Responsable de la Scolarité et Examens</span><p><u>bebeliliane6@gmail.com</u></p></div>
                <div class="staff"><img class="staff-photo" src="assets/img/user-icon.jpg" alt="" width="300" height="300" loading="lazy"><strong>M. NKOMBO Jonas</strong><span>Responsable de la communication</span><p><u>jonasnkombo7@gmail.com</u></p></div>
                <div class="staff"><img class="staff-photo" src="assets/img/user-icon.jpg" alt="" width="300" height="300" loading="lazy"><strong>M. NGIRABATWARE Alexis</strong><span>Responsable de la comptabilité</span></div>
                <div class="staff"><img class="staff-photo" src="assets/img/M Taylor.jpeg" alt="" width="300" height="300" loading="lazy"><strong>M. MATOUTA Tayler</strong><span>Responsable informatique</span><p><u>taylermatouta@gmail.com</u></p></div>
                <div class="staff"><img class="staff-photo" src="assets/img/Mme Evie.jpeg" alt="" width="300" height="300" loading="lazy"><strong>Mme INOUA Evie</strong><span>Attachée à la scolarité et aux examens</span><p><u>Gychou2012@gmail.com</u></p></div>  
                <div class="staff"><img class="staff-photo" src="assets/img/user-icon.jpg" alt="" width="300" height="300" loading="lazy"><strong>M. Yengo</strong><span>Attaché à la communication, responsable de l’immersion professionnelle des Etudiants.</span></div>
                <div class="staff"><img class="staff-photo" src="assets/img/user-icon.jpg" alt="" width="300" height="300" loading="lazy"><strong>Mme EKAMBA Dacha</strong><span>Assistante comptable</span></div>
                <div class="staff"><img class="staff-photo" src="assets/img/Ms Liliane.jpeg" alt="" width="300" height="300" loading="lazy"><strong>Mme WARREN Branda</strong><span>Caissière, Chargé du recouvrement</span><p><u>brendawaren92@gmail.com</u></p></div>
                <div class="staff"><img class="staff-photo" src="assets/img/user-icon.jpg" alt="" width="300" height="300" loading="lazy"><strong>M. NTOUADIKISSA Themar Louis</strong><span>Surveillant</span><p><u>ntouadikissathemar@gmail.com</u></p></div>
                <div class="staff"><img class="staff-photo" src="assets/img/user-icon.jpg" alt="" width="300" height="300" loading="lazy"><strong>M. MBOUAKA Patrice</strong><span>Surveillant</span></div>
                
            </div>
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

</body></html>

