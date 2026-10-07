<?php

session_start();

$adminConnecte = isset($_SESSION['admin_connecte']) && $_SESSION['admin_connecte'] === true;

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>À propos</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.9.1/fonts/remixicon.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="assets/img/logo-hemip.jpg">
    
    <style>
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
            color : #0b2942
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
            color : #0a527e
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
            background : #0a527e
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
</head>
<body>
    <header class="header">
        <nav class="navbar">
        <a href="index.php" class="logo">
            <img src="assets/img/logo-hemip.jpg" alt="Logo HEMIP">
            <span>HEMIP</span>
        </a>
        <button class="menu-btn" id="menu-btn">
            <i class="ri-menu-line"></i>
        </button>
        <ul class="nav-list" id="nav-list">
            <li>
                <a href="index.php">Accueil</a>
            </li>
            <li class="dropdown">
                <button class="dropdown-btn">Formation <i class="ri-arrow-down-s-line"></i>
            </button><ul class="dropdown-menu">
                    <li class="submenu"><button class="submenu-btn">Pôle technique <i class="ri-arrow-right-s-line"></i></button><ul class="submenu-menu">
                        <li><a href="GP.html">Génie Pétrolier</a></li>
                        <li><a href="GL.html">Génie Logiciel</a></li>
                        <li><a href="RT.html">Réseaux et Télécommunication</a></li>
                        <li><a href="MI.html">Maintenance Industrielle</a></li>
                        <li><a href="AII.html">Automatisation et Informatique Industriel</a></li>
                    </ul></li>
                    <li class="submenu"><button class="submenu-btn">Pôle Commercial <i class="ri-arrow-right-s-line"></i></button><ul class="submenu-menu">
                        <li><a href="DAE.html">Droit des Affaires et des Entreprises</a></li>
                        <li><a href="GLT.html">Gestion Logistique et Transport</a></li>
                        <li><a href="BFA.html">Banque et Finance des Assurances</a></li>
                        <li><a href="CIT.html">Commerce International et Transit</a></li>
                        <li><a href="GFC.html">Gestion Finance et Comptable</a></li>
                        <li><a href="MRH.html">Management des Ressources Humaines</a></li>
                    </ul></li>
                </ul>

                <li>
                    <a href="inscription.php">Inscription</a>
                </li>
                <li>
                    <a href="actualite.php">Actualité</a>
                </li>
                <li>
                    <a href="index.php#footer">Contact</a>
                </li>
                <li>
                    <a href="a-propos.php" class="active">À propos</a>
                </li>
        <?php if($adminConnecte): ?>
            <li>
                <a href="dashboard.php" title="Tableau de bord">
                    <i class="ri-dashboard-line"></i>
                </a></li>
                <li class="admin-status">
                    <i class="ri-admin-line" style="color:white"></i>
                    <span style="color:white;font-size:15px">Connecté</span>
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
        <h1>À propos de HEMIP</h1><p>Une institution tertiaire privée et agréée de l’Enseignement Supérieur et Universitaire de la République du Congo, présente depuis 2013 à Pointe-Noire.</p>
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
            <p>La Haute Ecole de Management et d’Ingénierie La Percée (HEMIP en sigle) est une institution tertiaire privée et agréée de l’Enseignement Supérieur et Universitaire de la République du Congo qui existe depuis 2013 et localisée à Pointe-Noire avec une extension à NKayi.</p>
            <p>Depuis sa création jusqu’à ce jour HEMIP a déjà gradué des centaines de diplômés : Techniciens Supérieurs &amp; Licenciés Professionnels ; lesquels pour la plupart sont déjà dans la vie active professionnelle en tant qu’agents ou cadres supérieurs.</p>
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
                <h2>Nos Facilités</h2>
                <p>Les infrastructures et moyens présentés dans le document HEMIP.</p>
            </div>
        </div>
        <div class="about-grid">
            <div class="about-card">
                <h3>Infrastructures</h3>
                <ul class="about-list">
                    <li>14 salles de classe</li>
                    <li>Bibliothèque physique & numérique</li>
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
        <div class="about-card" style="margin-top:22px">
            <strong>Estimation du personnel :</strong> 10 administratifs et 82 enseignants.
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
                <div class="staff"><strong>Ir. Eugène DIAYIKA</strong><span>Génie Numérique</span></div>
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
                <div class="staff"><strong>Nom : M. MATOUTA Tayler</strong><span>Rôle : </span><p>Email: <u>taylermatouta@gmail.com</u></p></div>
                <div class="staff"><img src="assets/img/WhatsApp Image 2026-10-06 at 18.03.382.jpeg" alt="" width="300" height="300" style="border-radius : 50px; margin-left: 7px;"><strong>Nom : M. NSEKA MPELA Arthur</strong><span>Rôle : Directeur des Affaires académiques</span><p>Email: <u>arthurmpela123@gmail.com</u></p></div>
                <div class="staff"><strong>Nom : M. KOUENA MABIKA Louis</strong><span>Rôle : Secrétaire académique</span><p>Whatsapp: +242 06 463 65 91</p></div>
                <div class="staff"><img src="assets/img/WhatsApp Image 2026-10-06 at 18.03.222.jpeg" alt="" width="300" height="300" style="border-radius : 50px; margin-left: 7px;"><strong>Nom : M. KIBANGOU</strong><span>Rôle : Promoteur de l’institut</span></div>
                <div class="staff"><img src="assets/img/WhatsApp Image 2026-10-06 at 18.03.242.jpeg" alt="" width="300" height="300" style="border-radius : 50px; margin-left: 7px;"><strong>Nom : Mme INOUA Evie</strong><span>Rôle : Attachée à la scolarité et aux examens</span><p>Email: <u>Gychou2012@gmail.com</u></p></div>
                <div class="staff"><strong>Nom : M. Yengo</strong><span>Rôle : Attaché à la communication, responsable de l’immersion professionnelle des Etudiants.</span></div>
                <div class="staff"><img src="assets/img/WhatsApp Image 2026-10-06 at 18.03.152.jpeg" alt="" width="300" height="300" style="border-radius : 50px; margin-left: 7px;"><strong>Nom : Mme WARREN Branda</strong><span>Rôle : Caissière, Chargé du recouvrement</span><p>Email: <u>brendawaren92@gmail.com</u></p></div>
                <div class="staff"><strong>Nom : Mme Liliane</strong><span>Rôle : Responsable de la Scolarité et Examens</span></div>
                <div class="staff"><strong>Nom : M. NKOMBO Jonas</strong><span>Rôle : Responsable de la communication</span><p>Email: <u>jonasnkombo7@gmail.com</u></p></div>
            </div>
        </div>
<br>
<br>
            <div class="about-card">
                <h3>Nos témoignages d'anciens étudiants</h3>
                <p>Cette section est prête à recevoir les témoignages réels des anciens étudiants lorsqu'ils seront disponibles.</p>
            </div>
        </div>
    </section>
</main>
<footer class="footer">
    <div class="footer-container">
        <div class="footer-col footer-about">
            <div class="footer-logo">
                <img src="assets/img/logo-hemip.jpg" alt="Logo HEMIP">
                <span>HEMIP</span>
            </div>
            <p class="footer-slogan">« Nous formons des professionnels qualifiés et chevronnés »</p>
        </div>
        <div class="footer-col">
            <h3>Contactez-nous</h3>
            <ul class="footer-contact">
                <li>
                    <i class="ri-mail-line"></i>
                    <a href="mailto:hemilaperceeinformation@gmail.com">hemilaperceeinformation@gmail.com</a>
                </li>
                <li>
                    <i class="ri-phone-line"></i>
                    <a href="tel:+242069149242">+242 06 914 92 42</a>
                </li>
                <li>
                    <i class="ri-phone-line"></i>
                    <a href="tel:+242053560677">+242 06 356 06 77</a>
                </li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        <p>© 2026 HEMIP. Tous droits réservés.</p>
        <p>Haute École de Management et d’Ingénierie la Percée</p>
    </div>
</footer>

<script src="assets/js/main.js"></script>

</body>

</html>