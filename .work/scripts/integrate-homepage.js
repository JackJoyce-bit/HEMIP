const fs = require("node:fs");
const path = require("node:path");

const root = "C:/Users/Le MADISON/OneDrive/Desktop/HEMIP-main/config";
const indexPath = path.join(root, "index.php");
let index = fs.readFileSync(indexPath, "utf8");
const eol = index.includes("\r\n") ? "\r\n" : "\n";
const lines = values => values.join(eol);

function replaceOnce(source, needle, replacement, label) {
  const first = source.indexOf(needle);
  if (first < 0 || source.indexOf(needle, first + needle.length) >= 0) {
    throw new Error(`Repère absent ou non unique (${label}); aucun fichier n'a encore été écrit.`);
  }
  return source.slice(0, first) + replacement + source.slice(first + needle.length);
}

if (index.includes("data-home-carousel") || index.includes("home-contact-form")) {
  throw new Error("La page index.php semble déjà intégrée; arrêter pour éviter une double insertion.");
}

const oldBootstrap = lines(["<?php", "session_start();", "", "$adminConnecte = isset($_SESSION['admin_connecte'])"]);
const newBootstrap = lines([
  "<?php",
  "session_start();",
  "",
  "if (",
  "    !isset($_SESSION['contact_csrf'])",
  "    || !is_string($_SESSION['contact_csrf'])",
  "    || !preg_match('/\\A[a-f0-9]{64}\\z/', $_SESSION['contact_csrf'])",
  ") {",
  "    $_SESSION['contact_csrf'] = bin2hex(random_bytes(32));",
  "}",
  "$contactCsrf = $_SESSION['contact_csrf'];",
  "$contactFlash = isset($_SESSION['contact_flash']) && is_string($_SESSION['contact_flash'])",
  "    ? $_SESSION['contact_flash']",
  "    : '';",
  "unset($_SESSION['contact_flash']);",
  "",
  "$adminConnecte = isset($_SESSION['admin_connecte'])"
]);
index = replaceOnce(index, oldBootstrap, newBootstrap, "bootstrap CSRF");

index = replaceOnce(
  index,
  '<link rel="stylesheet" href="assets/css/pwa.css">',
  lines(['<link rel="stylesheet" href="assets/css/pwa.css">', '    <link rel="stylesheet" href="assets/css/home-sections.css">']),
  "chargement CSS accueil"
);

const oldContactNav = lines([
  '            <li>',
  '                <a href="#footer">Contact</a>',
  '            </li>'
]);
const newContactNav = lines([
  '            <li>',
  '                <a href="#realisations">Nos réalisations</a>',
  '            </li>',
  '',
  '            <li>',
  '                <a href="#contact">Contact</a>',
  '            </li>'
]);
index = replaceOnce(index, oldContactNav, newContactNav, "menu Contact et réalisations");

const oldHeroStart = lines([
  '            <div class="hero-background"></div>',
  '',
  '            <div class="hero-content reveal">'
]);
const newHeroStart = lines([
  '            <div class="hero-background"></div>',
  '',
  '            <div class="hero-slider" aria-hidden="false">',
  '                <div class="hero-slide is-active" id="home-slide-1" role="group" aria-roledescription="diapositive" aria-label="1 sur 4 : La façade HEMIP" aria-hidden="false" data-slide-label="La façade HEMIP">',
  '                    <img src="assets/img/hemip-campus.jpg" alt="Façade et enseigne de la Haute École de Management et d’Ingénierie la Percée" fetchpriority="high" decoding="async">',
  '                </div>',
  '                <div class="hero-slide" id="home-slide-2" role="group" aria-roledescription="diapositive" aria-label="2 sur 4 : Une scène de cérémonie HEMIP" aria-hidden="true" data-slide-label="Une scène de cérémonie HEMIP">',
  '                    <img src="assets/img/hemip-ceremonie.jpg" alt="Groupe de personnes réuni lors d’un événement HEMIP" loading="eager" fetchpriority="low" decoding="async">',
  '                </div>',
  '                <div class="hero-slide" id="home-slide-3" role="group" aria-roledescription="diapositive" aria-label="3 sur 4 : La pratique en atelier" aria-hidden="true" data-slide-label="La pratique en atelier">',
  '                    <img src="assets/img/hemip-atelier.jpg" alt="Étudiant en travaux pratiques autour d’un équipement mécanique" loading="eager" fetchpriority="low" decoding="async">',
  '                </div>',
  '                <div class="hero-slide" id="home-slide-4" role="group" aria-roledescription="diapositive" aria-label="4 sur 4 : L’apprentissage informatique" aria-hidden="true" data-slide-label="L’apprentissage informatique">',
  '                    <img src="assets/img/hemip-informatique.jpg" alt="Étudiants en séance de travail devant des ordinateurs" loading="eager" fetchpriority="low" decoding="async">',
  '                </div>',
  '            </div>',
  '',
  '            <div class="hero-content reveal">'
]);
index = replaceOnce(index, oldHeroStart, newHeroStart, "images du carrousel");

const oldHeroSection = '<section class="hero" id="accueil">';
const newHeroSection = '<section class="hero" id="accueil" data-home-carousel role="region" aria-roledescription="carrousel" aria-label="Accueil HEMIP : photos de l’école">';
index = replaceOnce(index, oldHeroSection, newHeroSection, "région du carrousel");

const oldWhySection = '<section class="container_pourquoi">';
index = replaceOnce(index, oldWhySection, '<section class="container_pourquoi" id="ecole">', "ancre HEMIP existante");

const oldContactButton = lines([
  '                    <a href="#formations" class="btn btn-primary">',
  '                       Nous contacter '
]);
const newContactButton = lines([
  '                    <a href="#contact" class="btn btn-primary">',
  '                       Nous contacter '
]);
index = replaceOnce(index, oldContactButton, newContactButton, "CTA Nous contacter");

const controls = lines([
  '            <div class="home-slider-controls" role="group" aria-label="Commandes des photos de HEMIP">',
  '                <button type="button" data-slide-previous aria-label="Photo précédente"><span aria-hidden="true">‹</span></button>',
  '                <div class="home-slider-dots" role="group" aria-label="Choisir une photo">',
  '                    <button type="button" class="home-slider-dot" aria-current="true" aria-label="Afficher la photo 1 : La façade HEMIP" aria-controls="home-slide-1"></button>',
  '                    <button type="button" class="home-slider-dot" aria-label="Afficher la photo 2 : Une scène de cérémonie HEMIP" aria-controls="home-slide-2"></button>',
  '                    <button type="button" class="home-slider-dot" aria-label="Afficher la photo 3 : La pratique en atelier" aria-controls="home-slide-3"></button>',
  '                    <button type="button" class="home-slider-dot" aria-label="Afficher la photo 4 : L’apprentissage informatique" aria-controls="home-slide-4"></button>',
  '                </div>',
  '                <button type="button" data-slide-toggle aria-pressed="false" aria-label="Mettre le défilement des photos en pause">Ⅱ</button>',
  '                <button type="button" data-slide-next aria-label="Photo suivante"><span aria-hidden="true">›</span></button>',
  '            </div>',
  '            <p class="home-slider-status" aria-live="off" aria-atomic="true">01 — La façade HEMIP</p>'
]);
index = replaceOnce(index, '            <div class="hero-visual">', `${controls}${eol}${eol}            <div class="hero-visual">`, "commandes du carrousel");

const realizations = lines([
  '<!-- SECTION NOS RÉALISATIONS -->',
  '<section class="home-section home-realizations reveal" id="realisations" aria-labelledby="home-realizations-title">',
  '    <div class="home-container">',
  '        <div class="home-section-heading">',
  '            <div>',
  '                <p class="home-eyebrow">La vie à HEMIP</p>',
  '                <h2 id="home-realizations-title">Nos réalisations</h2>',
  '            </div>',
  '            <p class="home-section-intro">Des moments de formation, de pratique et de vie d’école, illustrés par les photos partagées par HEMIP.</p>',
  '        </div>',
  '        <div class="home-realization-grid">',
  '            <article class="home-realization-card">',
  '                <img src="assets/img/hemip-graduation.jpg" alt="Diplômée en tenue de cérémonie avec des membres de l’équipe HEMIP" loading="lazy" decoding="async">',
  '                <div class="home-realization-copy"><span>Cérémonie</span><h3>Un moment partagé</h3><p>Une scène de cérémonie réunissant la diplômée et des membres de l’équipe.</p></div>',
  '            </article>',
  '            <article class="home-realization-card">',
  '                <img src="assets/img/hemip-atelier.jpg" alt="Étudiants en pratique technique autour d’un moteur" loading="lazy" decoding="async">',
  '                <div class="home-realization-copy"><span>Pratique technique</span><h3>Apprendre en atelier</h3><p>Des étudiants réunis autour d’un équipement mécanique pendant un exercice pratique.</p></div>',
  '            </article>',
  '            <article class="home-realization-card">',
  '                <img src="assets/img/hemip-informatique.jpg" alt="Étudiants en travaux pratiques sur des ordinateurs" loading="lazy" decoding="async">',
  '                <div class="home-realization-copy"><span>Informatique</span><h3>La pratique en salle</h3><p>Une séance de travail appliquée devant les postes informatiques.</p></div>',
  '            </article>',
  '            <article class="home-realization-card">',
  '                <img src="assets/img/hemip-rencontre.jpg" alt="Groupe réuni devant l’école et une affiche de formation" loading="lazy" decoding="async">',
  '                <div class="home-realization-copy"><span>Vie de l’école</span><h3>Se retrouver et échanger</h3><p>Un groupe réuni devant les locaux de HEMIP à l’occasion d’une rencontre.</p></div>',
  '            </article>',
  '        </div>',
  '    </div>',
  '</section>',
  ''
]);
index = replaceOnce(index, '<!-- SECTION GOOGLE MAPS HEMIP -->', `${realizations}${eol}<!-- SECTION GOOGLE MAPS HEMIP -->`, "section réalisations");

const contactSection = lines([
  '<!-- SECTION CONTACT -->',
  '<section class="home-contact reveal" id="contact" aria-labelledby="home-contact-title">',
  '    <div class="home-contact-layout">',
  '        <div class="home-contact-copy">',
  '            <p class="home-eyebrow">Nous contacter</p>',
  '            <h2 id="home-contact-title">Parlons de votre avenir.</h2>',
  '            <p>Une question sur les formations ou les inscriptions ? Écrivez à l’équipe de HEMIP ; nous vous répondrons à l’adresse indiquée.</p>',
  '            <div class="home-contact-details">',
  '                <a class="home-contact-detail" href="mailto:hemilaperceeinformation@gmail.com"><span class="home-contact-detail-icon" aria-hidden="true"><i class="ri-mail-line"></i></span><span>hemilaperceeinformation@gmail.com</span></a>',
  '                <a class="home-contact-detail" href="tel:+242069149242"><span class="home-contact-detail-icon" aria-hidden="true"><i class="ri-phone-line"></i></span><span>+242 06 914 92 42</span></a>',
  '                <a class="home-contact-detail" href="tel:+242063560677"><span class="home-contact-detail-icon" aria-hidden="true"><i class="ri-phone-line"></i></span><span>+242 06 356 06 77</span></a>',
  '            </div>',
  '        </div>',
  '',
  '        <div class="home-contact-card">',
  '            <h3>Envoyer un message</h3>',
  '            <p class="home-contact-note">Les champs marqués * sont requis. Le téléphone est facultatif.</p>',
  '            <?php if ($contactFlash === \'sent\'): ?>',
  '                <div class="home-contact-alert home-contact-alert--success" role="status" tabindex="-1">Votre message a été transmis à l’école. Merci de nous avoir contactés.</div>',
  '            <?php elseif ($contactFlash === \'too_soon\'): ?>',
  '                <div class="home-contact-alert home-contact-alert--error" role="alert" tabindex="-1">Veuillez patienter avant d’envoyer un autre message.</div>',
  '            <?php elseif ($contactFlash === \'failed\'): ?>',
  '                <div class="home-contact-alert home-contact-alert--error" role="alert" tabindex="-1">Le message n’a pas pu être transmis. Réessayez ou écrivez directement à l’adresse ci-dessous.</div>',
  '            <?php elseif ($contactFlash === \'invalid\'): ?>',
  '                <div class="home-contact-alert home-contact-alert--error" role="alert" tabindex="-1">Certaines informations sont manquantes ou invalides. Vérifiez les champs requis puis réessayez.</div>',
  '            <?php endif; ?>',
  '',
  '            <form action="contact-submit.php" method="post" accept-charset="UTF-8">',
  '                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($contactCsrf, ENT_QUOTES, \'UTF-8\') ?>">',
  '                <div class="home-contact-honeypot" aria-hidden="true"><label for="contact-website">Ne pas remplir</label><input id="contact-website" name="website" type="text" tabindex="-1" autocomplete="off"></div>',
  '                <div class="home-contact-form-grid">',
  '                    <div class="home-contact-field"><label for="contact-prenom">Prénom *</label><input id="contact-prenom" name="prenom" type="text" autocomplete="given-name" maxlength="100" required></div>',
  '                    <div class="home-contact-field"><label for="contact-nom">Nom *</label><input id="contact-nom" name="nom" type="text" autocomplete="family-name" maxlength="100" required></div>',
  '                    <div class="home-contact-field"><label for="contact-email">Adresse e-mail *</label><input id="contact-email" name="email" type="email" autocomplete="email" maxlength="254" required></div>',
  '                    <div class="home-contact-field"><label for="contact-telephone">Téléphone</label><input id="contact-telephone" name="telephone" type="tel" autocomplete="tel" maxlength="40" inputmode="tel"></div>',
  '                    <div class="home-contact-field home-contact-field--full"><label for="contact-objet">Objet *</label><input id="contact-objet" name="objet" type="text" maxlength="150" required></div>',
  '                    <div class="home-contact-field home-contact-field--full"><label for="contact-message">Votre message *</label><textarea id="contact-message" name="message" rows="6" maxlength="5000" required></textarea></div>',
  '                </div>',
  '                <label class="home-contact-consent"><input type="checkbox" name="consentement" value="yes" required><span>J’accepte que mes coordonnées soient utilisées uniquement pour répondre à ma demande.</span></label>',
  '                <button class="btn btn-primary home-contact-submit" type="submit">Envoyer mon message <i class="ri-arrow-right-line" aria-hidden="true"></i></button>',
  '                <p class="home-contact-privacy">Le site ne conserve pas de copie du message dans sa base. L’envoi nécessite une connexion Internet ; aucun envoi hors ligne n’est effectué.</p>',
  '            </form>',
  '        </div>',
  '    </div>',
  '</section>',
  ''
]);
index = replaceOnce(index, '<footer class="footer reveal">', `${contactSection}<footer class="footer reveal">`, "formulaire de contact");

index = replaceOnce(
  index,
  '<script src="assets/js/main.js"></script>',
  lines(['<script src="assets/js/main.js"></script>', '  <script src="assets/js/home-sections.js" defer></script>']),
  "chargement du carrousel"
);

const pending = new Map([[indexPath, index]]);
const files = fs.readdirSync(root, { withFileTypes: true })
  .filter(entry => entry.isFile() && /\.(php|html)$/i.test(entry.name) && entry.name.toLowerCase() !== "index.php");
let updatedContactLinks = 0;
for (const entry of files) {
  const fullPath = path.join(root, entry.name);
  const original = fs.readFileSync(fullPath, "utf8");
  const updated = original
    .replace(/href="index\.php#footer"/g, 'href="index.php#contact"')
    .replace(/href="#footer"/g, 'href="index.php#contact"');
  if (updated !== original) {
    updatedContactLinks += (original.match(/href="(?:index\.php)?#footer"/g) || []).length;
    pending.set(fullPath, updated);
  }
}

// Écrire seulement après que toutes les ancres et tous les points d'insertion ont été validés.
for (const [file, contents] of pending) fs.writeFileSync(file, contents, "utf8");
console.log(JSON.stringify({ updatedFiles: pending.size, contactLinksRedirected: updatedContactLinks, homepage: indexPath }));
