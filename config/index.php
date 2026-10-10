<?php
session_start();

$adminConnecte = isset($_SESSION['admin_connecte'])
    && $_SESSION['admin_connecte'] === true;
?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Remix Icon -->
    <link
        href="assets/vendor/remixicon/remixicon.css"
        rel="stylesheet"
    >

    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/style.css">

     <!-- favicon -->
    <link rel="icon" type="image/png" href="assets/icons/hemip-192.png">
    <title>HEMIP site web</title>

    <meta name="theme-color" content="#1094d7">
    <meta name="application-name" content="HEMIP">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="HEMIP">
    <link rel="manifest" href="manifest.json">
    <link rel="apple-touch-icon" sizes="180x180" href="assets/icons/hemip-180.png">
    <link rel="icon" type="image/png" sizes="192x192" href="assets/icons/hemip-192.png">
    <link rel="stylesheet" href="assets/css/pwa.css">
    <link rel="stylesheet" href="assets/css/home-sections.css">
  <link rel="stylesheet" href="assets/css/hemip-theme.css">
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
                <a href="actualite.php">Actualité</a>
            </li>


            <li>
                <a href="realisations.html">Nos réalisations</a>
            </li>

            <li>
                <a href="contact.php#contact">Contact</a>
            </li>

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
                <div class="hero-slide is-active" id="home-slide-1" role="group" aria-roledescription="diapositive" aria-label="1 sur 4 : La façade HEMIP" aria-hidden="false" data-slide-label="La façade HEMIP">
                    <img src="assets/img/hemip-campus.jpg" alt="Façade et enseigne de la Haute École de Management et d’Ingénierie la Percée" fetchpriority="high" decoding="async">
                </div>
                <div class="hero-slide" id="home-slide-2" role="group" aria-roledescription="diapositive" aria-label="2 sur 4 : Une scène de cérémonie HEMIP" aria-hidden="true" data-slide-label="Une scène de cérémonie HEMIP">
                    <img src="assets/img/hemip-ceremonie.jpg" alt="Groupe de personnes réuni lors d’un événement HEMIP" loading="eager" fetchpriority="low" decoding="async">
                </div>
                <div class="hero-slide" id="home-slide-3" role="group" aria-roledescription="diapositive" aria-label="3 sur 4 : La pratique en atelier" aria-hidden="true" data-slide-label="La pratique en atelier">
                    <img src="assets/img/hemip-atelier.jpg" alt="Étudiant en travaux pratiques autour d’un équipement mécanique" loading="eager" fetchpriority="low" decoding="async">
                </div>
                <div class="hero-slide" id="home-slide-4" role="group" aria-roledescription="diapositive" aria-label="4 sur 4 : L’apprentissage informatique" aria-hidden="true" data-slide-label="L’apprentissage informatique">
                    <img src="assets/img/hemip-informatique.jpg" alt="Étudiants en séance de travail devant des ordinateurs" loading="eager" fetchpriority="low" decoding="async">
                </div>
            </div>

            <div class="hero-content reveal">

                

                <h1>
                    Construisez votre
                    <span>avenir.</span>
                </h1>

                <p>
                    Bienvenue à la Haute École de Management et
                    d'Ingénierie la Percée. Une formation d'excellence
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
                    <button type="button" class="home-slider-dot" aria-label="Afficher la photo 2 : Une scène de cérémonie HEMIP" aria-controls="home-slide-2"></button>
                    <button type="button" class="home-slider-dot" aria-label="Afficher la photo 3 : La pratique en atelier" aria-controls="home-slide-3"></button>
                    <button type="button" class="home-slider-dot" aria-label="Afficher la photo 4 : L’apprentissage informatique" aria-controls="home-slide-4"></button>
                </div>
                <button type="button" data-slide-toggle aria-pressed="false" aria-label="Mettre le défilement des photos en pause">Ⅱ</button>
                <button type="button" data-slide-next aria-label="Photo suivante"><span aria-hidden="true">›</span></button>
            </div>
            <p class="home-slider-status" aria-live="off" aria-atomic="true">01 — La façade HEMIP</p>

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
         <div class="content_pourquoi reveal">
             <!-- Titre section POURQUOI-->
    <h1> Pourquoi choisir
    <!-- paragraphe section POURQUOI-->
    <span class="titre_pourquoi"> HEMIP</span>    
     </h1>
     <div>
     <p>
         Choisir HEMIP, c’est opter pour une formation de qualité alliant théorie et pratique, un accompagnement personnalisé et un environnement dynamique. Nous préparons nos étudiants à devenir des professionnels compétents, autonomes et prêts à relever les défis du monde professionnel.
     </p>
  

                     <div class="hero-buttons">

                    <a href="contact.php#contact" class="btn btn-primary">
                       Nous contacter 
                        <i class="ri-arrow-right-line"></i>
                    </a>

         </div>   
</div>
          
 
 <div class="image_container_pourquoi">
     <img src="assets/img/classe-hemip-science.jpg" alt="travaux pratiques chimi">
     <img src="assets/img/class-hemip-cours.jpg" alt="cours avec projection">
     <div class="image_content_pourquoi">
    <ul>
      <li>• Reconnue par l'Etat</li>
   <li>• Multiples partenaires</li>
    </ul>     
     </div>
     
     
 </div>         
       

</section>



<!-- logo partenaire -->
<section class="partners">
    <div class="partners-container reveal">

        <h2>Nos partenaires</h2>
        <p style="font-size:1rem;">Ils nous accompagnent dans notre vision et notre développement.</p>

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
                    <a href="tel:+242053560677">
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
            Haute École de Management et d'Ingénierie la Percée
        </p>
    </div>
</footer>

<script src="assets/js/main.js"></script>
  <script src="assets/js/home-sections.js" defer></script>

  <script src="assets/js/pwa.js" defer></script>
</body>
</html>
