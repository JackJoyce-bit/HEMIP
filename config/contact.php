<?php
session_start();

if (
    !isset($_SESSION['contact_csrf'])
    || !is_string($_SESSION['contact_csrf'])
    || !preg_match('/\A[a-f0-9]{64}\z/', $_SESSION['contact_csrf'])
) {
    $_SESSION['contact_csrf'] = bin2hex(random_bytes(32));
}
$contactCsrf = $_SESSION['contact_csrf'];
$contactFlash = isset($_SESSION['contact_flash']) && is_string($_SESSION['contact_flash'])
    ? $_SESSION['contact_flash']
    : '';
unset($_SESSION['contact_flash']);
$adminConnecte = isset($_SESSION['admin_connecte']) && $_SESSION['admin_connecte'] === true;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Contactez l’équipe de la Haute École de Management et d’Ingénierie la Percée (HEMIP).">
    <meta name="theme-color" content="#1094d7">
    <meta name="application-name" content="HEMIP">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="HEMIP">
    <link rel="manifest" href="manifest.json">
    <link rel="apple-touch-icon" sizes="180x180" href="assets/icons/hemip-180.png">
    <link rel="icon" type="image/png" sizes="192x192" href="assets/icons/hemip-192.png">
    <link rel="stylesheet" href="assets/vendor/remixicon/remixicon.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/pwa.css">
    <link rel="stylesheet" href="assets/css/home-sections.css">
    <link rel="stylesheet" href="assets/css/independent-pages.css">
    <title>Contact — HEMIP</title>
  <link rel="stylesheet" href="assets/css/hemip-theme.css">
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
                        <li><a href="GP.html">Génie Pétrolier</a></li><li><a href="GL.html">Génie Logiciel</a></li><li><a href="RT.html">Réseaux et Télécommunication</a></li><li><a href="MI.html">Maintenance Industrielle</a></li><li><a href="AII.html">Automatisation et Informatique Industriel</a></li>
                    </ul></li>
                    <li class="submenu"><button class="submenu-btn" type="button">Pôle Commercial <i class="ri-arrow-right-s-line" aria-hidden="true"></i></button><ul class="submenu-menu">
                        <li><a href="DAE.html">Droit des Affaires et des Entreprises</a></li><li><a href="GLT.html">Gestion Logistique et Transport</a></li><li><a href="BFA.html">Banque et Finance des Assurances</a></li><li><a href="CIT.html">Commerce International et Transit</a></li><li><a href="GFC.html">Gestion Finance et Comptable</a></li><li><a href="MRH.html">Management des Ressources Humaines</a></li>
                    </ul></li>
                </ul>
            </li>
            <li><a href="inscription.php">Inscription</a></li>
            <li><a href="actualite.php">Actualité</a></li>
            <li><a href="realisations.html">Nos réalisations</a></li>
            <li><a href="contact.php" aria-current="page">Contact</a></li>
            <li><a href="a-propos.php">À propos</a></li>
            <?php if ($adminConnecte): ?>
                <li><a href="dashboard.php" title="Tableau de bord"><i class="ri-dashboard-line" aria-hidden="true"></i><span class="visually-hidden">Tableau de bord</span></a></li>
                <li class="admin-status"><i class="ri-admin-line" aria-hidden="true"></i><span>Connecté</span></li>
            <?php else: ?>
                <li><a href="login.php" title="Connexion administrateur"><i class="ri-admin-line" aria-hidden="true"></i><span class="visually-hidden">Connexion administrateur</span></a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>
<main class="contact-page-main">
    <section class="independent-page-hero" aria-labelledby="contact-page-title">
        <div class="independent-page-hero-inner">
            <div>
                <p class="independent-page-eyebrow">Nous sommes à votre écoute</p>
                <h1 id="contact-page-title">Contactez HEMIP</h1>
                <p>Une question sur une formation ou une inscription ? L’équipe de la Haute École de Management et d’Ingénierie la Percée vous répond.</p>
            </div>
            <div class="contact-page-direct">
                <a href="mailto:hemilaperceeinformation@gmail.com"><i class="ri-mail-line" aria-hidden="true"></i><span>hemilaperceeinformation@gmail.com</span></a>
                <a href="tel:+242069149242"><i class="ri-phone-line" aria-hidden="true"></i><span>+242 06 914 92 42</span></a>
            </div>
        </div>
    </section>

    <section class="home-contact" id="contact" aria-labelledby="home-contact-title">
        <div class="home-contact-layout">
            <div class="home-contact-copy">
                <p class="home-eyebrow">Écrivez-nous</p>
                <h2 id="home-contact-title">Parlons de votre avenir.</h2>
                <p>Remplissez le formulaire. Votre message sera transmis à l’adresse de l’école et nous vous répondrons à l’adresse e-mail fournie.</p>
                <div class="home-contact-details">
                    <a class="home-contact-detail" href="mailto:hemilaperceeinformation@gmail.com"><span class="home-contact-detail-icon" aria-hidden="true"><i class="ri-mail-line"></i></span><span>hemilaperceeinformation@gmail.com</span></a>
                    <a class="home-contact-detail" href="tel:+242069149242"><span class="home-contact-detail-icon" aria-hidden="true"><i class="ri-phone-line"></i></span><span>+242 06 914 92 42</span></a>
                    <a class="home-contact-detail" href="tel:+242063560677"><span class="home-contact-detail-icon" aria-hidden="true"><i class="ri-phone-line"></i></span><span>+242 06 356 06 77</span></a>
                </div>
            </div>

            <div class="home-contact-card">
                <h3>Envoyer un message</h3>
                <p class="home-contact-note">Les champs marqués * sont requis. Le téléphone est facultatif.</p>
                <?php if ($contactFlash === 'sent'): ?>
                    <div class="home-contact-alert home-contact-alert--success" role="status" tabindex="-1">Votre message a été transmis à l’école. Merci de nous avoir contactés.</div>
                <?php elseif ($contactFlash === 'too_soon'): ?>
                    <div class="home-contact-alert home-contact-alert--error" role="alert" tabindex="-1">Veuillez patienter avant d’envoyer un autre message.</div>
                <?php elseif ($contactFlash === 'failed'): ?>
                    <div class="home-contact-alert home-contact-alert--error" role="alert" tabindex="-1">Le message n’a pas pu être transmis. Réessayez ou écrivez directement à l’adresse ci-dessous.</div>
                <?php elseif ($contactFlash === 'invalid'): ?>
                    <div class="home-contact-alert home-contact-alert--error" role="alert" tabindex="-1">Certaines informations sont manquantes ou invalides. Vérifiez les champs requis puis réessayez.</div>
                <?php endif; ?>
                <form id="contact-form" action="contact-submit.php" method="post" accept-charset="UTF-8">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($contactCsrf, ENT_QUOTES, 'UTF-8') ?>">
                    <div class="home-contact-honeypot" aria-hidden="true"><label for="contact-website">Ne pas remplir</label><input id="contact-website" name="website" type="text" tabindex="-1" autocomplete="off"></div>
                    <div class="home-contact-form-grid">
                        <div class="home-contact-field"><label for="contact-prenom">Prénom *</label><input id="contact-prenom" name="prenom" type="text" autocomplete="given-name" maxlength="100" required></div>
                        <div class="home-contact-field"><label for="contact-nom">Nom *</label><input id="contact-nom" name="nom" type="text" autocomplete="family-name" maxlength="100" required></div>
                        <div class="home-contact-field"><label for="contact-email">Adresse e-mail *</label><input id="contact-email" name="email" type="email" autocomplete="email" maxlength="254" required></div>
                        <div class="home-contact-field"><label for="contact-telephone">Téléphone</label><input id="contact-telephone" name="telephone" type="tel" autocomplete="tel" maxlength="40" inputmode="tel"></div>
                        <div class="home-contact-field home-contact-field--full"><label for="contact-objet">Objet *</label><input id="contact-objet" name="objet" type="text" maxlength="150" required></div>
                        <div class="home-contact-field home-contact-field--full"><label for="contact-message">Votre message *</label><textarea id="contact-message" name="message" rows="6" maxlength="5000" required></textarea></div>
                    </div>
                    <label class="home-contact-consent"><input type="checkbox" name="consentement" value="yes" required><span>J’accepte que mes coordonnées soient utilisées uniquement pour répondre à ma demande.</span></label>
                    <button class="btn btn-primary home-contact-submit" type="submit">Envoyer mon message <i class="ri-arrow-right-line" aria-hidden="true"></i></button>
                    <p class="home-contact-privacy">Le site ne conserve pas de copie du message dans sa base. L’envoi nécessite une connexion Internet ; aucun envoi hors ligne n’est effectué.</p>
                </form>
            </div>
        </div>
    </section>
</main>
<footer class="footer">
    <div class="footer-container" id="footer">
        <div class="footer-col footer-about"><a class="footer-logo" href="index.php"><img src="assets/img/logo-hemip.jpg" alt=""><span>HEMIP</span></a><p class="footer-slogan">« Nous formons des professionnels qualifiés et chevronnés »</p></div>
        <div class="footer-col"><h3>Contactez-nous</h3><ul class="footer-contact"><li><i class="ri-mail-line" aria-hidden="true"></i><a href="mailto:hemilaperceeinformation@gmail.com">hemilaperceeinformation@gmail.com</a></li><li><i class="ri-phone-line" aria-hidden="true"></i><a href="tel:+242069149242">+242 06 914 92 42</a></li><li><i class="ri-whatsapp-line" aria-hidden="true"></i><a href="https://wa.me/242055865094" target="_blank" rel="noopener noreferrer">WhatsApp</a></li></ul></div>
        <div class="footer-col"><h3>Explorer</h3><ul class="footer-contact"><li><a href="index.php">Accueil</a></li><li><a href="realisations.html">Nos réalisations</a></li><li><a href="inscription.php">Inscription</a></li></ul></div>
    </div>
    <div class="footer-bottom"><p>© 2026 HEMIP. Tous droits réservés.</p><p>Haute École de Management et d’Ingénierie la Percée</p></div>
</footer>
<script src="assets/js/main.js"></script>
<script src="assets/js/pwa.js" defer></script>
</body>
</html>
