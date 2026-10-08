<?php
session_start();
$adminConnecte = isset($_SESSION['admin_connecte']) && $_SESSION['admin_connecte'] === true;
?>
<!DOCTYPE html>
<html lang="fr">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>À propos — HEMIP</title>
<link rel="stylesheet" href="assets/css/style.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/remixicon@4.9.1/fonts/remixicon.css" rel="stylesheet">
<link rel="icon" type="image/png" href="assets/img/logo-hemip.jpg">
<style>
.about-hero{padding:120px 20px 80px;background:linear-gradient(135deg,#071d33,#0b365b);color:#fff;text-align:center}
.about-hero .eyebrow{display:inline-block;padding:8px 15px;border:1px solid rgba(255,255,255,.25);border-radius:30px;font-size:.85rem;letter-spacing:.08em;text-transform:uppercase}
.about-hero h1{font-size:clamp(2.4rem,6vw,4.6rem);margin:18px 0 12px;font-family:'Playfair Display',serif}
.about-hero p{max-width:760px;margin:auto;line-height:1.8;color:rgba(255,255,255,.82)}
.about-wrap{max-width:1180px;margin:auto;padding:80px 20px}
.about-section{margin-bottom:70px}
.about-heading{display:flex;gap:16px;align-items:flex-start;margin-bottom:24px}
.about-icon{width:54px;height:54px;flex:0 0 54px;display:grid;place-items:center;border-radius:16px;background:#e9f3fb;color:#0a527e;font-size:25px}
.about-heading h2{margin:0;font-family:'Playfair Display',serif;font-size:2rem;color:#0b2942}
.about-heading p{margin:7px 0 0;color:#6b7785}
.about-card{background:#fff;border:1px solid #e7edf3;border-radius:22px;padding:30px;box-shadow:0 12px 35px rgba(12,42,67,.07);line-height:1.8;color:#45515d}
.about-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:22px}
.about-list{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;padding:0;margin:0;list-style:none}
.about-list li{padding:14px 16px;border-radius:14px;background:#f6f9fc;border:1px solid #e9eff5}
.staff-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px}
.staff{padding:17px;border:1px solid #e6edf3;border-radius:16px;background:#fff}
.staff strong{display:block;color:#102d45;margin-bottom:4px}.staff span{font-size:.9rem;color:#687583}
@media(max-width:800px){.about-grid,.about-list,.staff-grid{grid-template-columns:1fr}.about-wrap{padding:55px 18px}}
</style>
    <meta name="theme-color" content="#0b4ea2">
    <meta name="application-name" content="HEMIP">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="HEMIP">
    <link rel="manifest" href="manifest.json">
    <link rel="apple-touch-icon" sizes="180x180" href="assets/icons/hemip-180.png">
    <link rel="icon" type="image/png" sizes="192x192" href="assets/icons/hemip-192.png">
    <link rel="stylesheet" href="assets/css/pwa.css">
</head>
<body>
<header class="header"><nav class="navbar">
<a href="index.php" class="logo"><img src="assets/img/logo-hemip.jpg" alt="Logo HEMIP"><span>HEMIP</span></a>
<button class="menu-btn" id="menu-btn"><i class="ri-menu-line"></i></button>
<ul class="nav-list" id="nav-list">
<li><a href="index.php">Accueil</a></li>
<li class="dropdown"><button class="dropdown-btn">Formation <i class="ri-arrow-down-s-line"></i></button><ul class="dropdown-menu">
<li class="submenu"><button class="submenu-btn">Pôle technique <i class="ri-arrow-right-s-line"></i></button><ul class="submenu-menu">
<li><a href="GP.html">Génie Pétrolier</a></li><li><a href="GL.html">Génie Logiciel</a></li><li><a href="RT.html">Réseaux et Télécommunication</a></li><li><a href="MI.html">Maintenance Industrielle</a></li><li><a href="AII.html">Automatisation et Informatique Industriel</a></li></ul></li>
<li class="submenu"><button class="submenu-btn">Pôle Commercial <i class="ri-arrow-right-s-line"></i></button><ul class="submenu-menu">
<li><a href="DAE.html">Droit des Affaires et des Entreprises</a></li><li><a href="GLT.html">Gestion Logistique et Transport</a></li><li><a href="BFA.html">Banque et Finance des Assurances</a></li><li><a href="CIT.html">Commerce International et Transit</a></li><li><a href="GFC.html">Gestion Finance et Comptable</a></li><li><a href="MRH.html">Management des Ressources Humaines</a></li></ul></li>
</ul></li>
<li><a href="inscription.php">Inscription</a></li>
<li><a href="actualite.php">Actualité</a></li>
<li><a href="index.php#contact">Contact</a></li>
<li><a href="a-propos.php" class="active">À propos</a></li>
<?php if($adminConnecte): ?><li><a href="dashboard.php" title="Tableau de bord"><i class="ri-dashboard-line"></i></a></li><li class="admin-status"><i class="ri-admin-line" style="color:white"></i><span style="color:white;font-size:15px">Connecté</span></li>
<?php else: ?><li><a href="login.php" title="Connexion administrateur"><i class="ri-admin-line"></i></a></li><?php endif; ?>
</ul></nav></header>

<section class="about-hero"><span class="eyebrow">Découvrir HEMIP</span><h1>À propos de HEMIP</h1><p>Une institution tertiaire privée et agréée de l’Enseignement Supérieur et Universitaire de la République du Congo, présente depuis 2013 à Pointe-Noire.</p></section>

<main class="about-wrap">
<section class="about-section">
<div class="about-heading"><div class="about-icon"><i class="ri-history-line"></i></div><div><h2>Historique et Statut</h2><p>Quelques repères sur l’établissement.</p></div></div>
<div class="about-card"><p>La Haute Ecole de Management et d’Ingénierie La Percée (HEMIP en sigle) est une institution tertiaire privée et agréée de l’Enseignement Supérieur et Universitaire de la République du Congo qui existe depuis 2013 et localisée à Pointe-Noire avec une extension à NKayi.</p><p>Depuis sa création jusqu’à ce jour HEMIP a déjà gradué des centaines de diplômés : Techniciens Supérieurs &amp; Licenciés Professionnels ; lesquels pour la plupart sont déjà dans la vie active professionnelle en tant qu’agents ou cadres supérieurs.</p></div>
</section>

<section class="about-section">
<div class="about-heading"><div class="about-icon"><i class="ri-compass-3-line"></i></div><div><h2>Vision et Mission d’HEMIP</h2><p>Former, rechercher et préparer à la vie professionnelle.</p></div></div>
<div class="about-grid"><div class="about-card"><h3>Vision</h3><p>Devenir une des meilleures institutions supérieures en Afrique.</p></div><div class="about-card"><h3>Mission</h3><p>Assurer une formation de qualité (Système LMD avec des programmes de cours bien élaborés) des cadres dans les domaines de l’Ingénierie et du Management, avec des enseignants qualifiés, professionnels et innovateurs.</p><p>Organiser la recherche scientifique orientée et pratique au sein d’une unité de recherche pour des solutions adaptées aux problèmes divers inhérents à la société.</p><p>Préparer les étudiants à la vie professionnelle dans les industries, entreprises bancaires et autres organisations, ou encore pour des start-ups.</p></div></div>
</section>

<section class="about-section">
<div class="about-heading"><div class="about-icon"><i class="ri-building-4-line"></i></div><div><h2>Nos Facilités</h2><p>Les infrastructures et moyens présentés dans le document HEMIP.</p></div></div>
<div class="about-grid"><div class="about-card"><h3>Infrastructures</h3><ul class="about-list"><li>14 salles de classe</li><li>Bibliothèque physique &amp; numérique</li><li>Atelier mécanique</li><li>Atelier électrique</li><li>Laboratoire de chimie</li><li>Laboratoire d’informatique</li><li>Laboratoire d’électronique</li><li>760 étudiants (site de Pointe-Noire)</li></ul></div><div class="about-card"><h3>Logiciels disponibles</h3><ul class="about-list"><li>Visual Studio</li><li>Algobox</li><li>Cisco Packet Tracer</li><li>Autocad</li><li>Solidworks</li><li>Matlab</li><li>EnyB</li><li>Bsim-1</li><li>Sage</li></ul></div></div>
<div class="about-card" style="margin-top:22px"><strong>Estimation du personnel :</strong> 10 administratifs et 82 enseignants.</div>
</section>
</main>
<footer class="footer"><div class="footer-container"><div class="footer-col footer-about"><div class="footer-logo"><img src="assets/img/logo-hemip.jpg" alt="Logo HEMIP"><span>HEMIP</span></div><p class="footer-slogan">« Nous formons des professionnels qualifiés et chevronnés »</p></div><div class="footer-col"><h3>Contactez-nous</h3><ul class="footer-contact"><li><i class="ri-mail-line"></i><a href="mailto:hemilaperceeinformation@gmail.com">hemilaperceeinformation@gmail.com</a></li><li><i class="ri-phone-line"></i><a href="tel:+242069149242">+242 06 914 92 42</a></li><li><i class="ri-phone-line"></i><a href="tel:+242053560677">+242 06 356 06 77</a></li></ul></div></div><div class="footer-bottom"><p>© 2026 HEMIP. Tous droits réservés.</p><p>Haute École de Management et d’Ingénierie la Percée</p></div></footer>
<script src="assets/js/main.js"></script>
  <script src="assets/js/pwa.js" defer></script>
</body></html>

