const fs = require('node:fs');
const path = require('node:path');

const root = 'C:/xampp/htdocs/HEMIP-main/config';
const dryRun = process.argv.includes('--dry-run');
const changes = [];

function read(file) {
  return fs.readFileSync(path.join(root, file), 'utf8');
}

function planWrite(file, original, updated) {
  if (updated !== original) changes.push({ file, original, updated });
}

function walk(dir, files = []) {
  for (const item of fs.readdirSync(dir, { withFileTypes: true })) {
    if (item.isDirectory()) {
      if (!['assets', 'uploads', 'docs', 'vendor', 'node_modules'].includes(item.name.toLowerCase())) {
        walk(path.join(dir, item.name), files);
      }
      continue;
    }
    if (/\.(php|html)$/i.test(item.name)) files.push(path.join(dir, item.name));
  }
  return files;
}

function injectRealizationsLink(content, newline) {
  const navStart = content.indexOf('class="nav-list"');
  if (navStart < 0) return content;
  const navEnd = content.indexOf('</nav>', navStart);
  if (navEnd < 0) return content;
  const nav = content.slice(navStart, navEnd);
  if (/href\s*=\s*["'][^"']*realisations\.html(?:#[^"']*)?["']/i.test(nav)) return content;

  const contact = /<a\b(?=[^>]*href\s*=\s*["'][^"']*contact\.php(?:#[^"']*)?["'])[^>]*>[\s\S]*?<\/a>/i.exec(nav);
  if (!contact) return content;
  const close = nav.indexOf('</li>', contact.index + contact[0].length);
  if (close < 0) return content;
  const insertAt = navStart + close + '</li>'.length;
  return content.slice(0, insertAt)
    + newline + '            <li><a href="realisations.html">Nos réalisations</a></li>'
    + content.slice(insertAt);
}

for (const filePath of walk(root)) {
  const rel = path.relative(root, filePath).replace(/\\/g, '/');
  if (rel.toLowerCase() === 'contact-submit.php') continue;
  const original = fs.readFileSync(filePath, 'utf8');
  const newline = original.includes('\r\n') ? '\r\n' : '\n';
  let content = original;

  content = content.replace(/index\.php#contact/gi, 'contact.php');
  content = content.replace(/href\s*=\s*(["'])#contact\1/gi, 'href=$1contact.php$1');
  content = content.replace(/href\s*=\s*(["'])#realisations\1/gi, 'href=$1realisations.html$1');

  if (rel.toLowerCase() === 'index.php') {
    const csrfStart = content.search(/if\s*\(\s*!isset\(\$_SESSION\['contact_csrf'\]\)/);
    const csrfEndMatch = csrfStart >= 0
      ? /unset\(\$_SESSION\['contact_flash'\]\);\s*/.exec(content.slice(csrfStart))
      : null;
    if (csrfStart >= 0) {
      if (!csrfEndMatch) throw new Error('Bloc CSRF/contact incomplet dans index.php');
      const csrfEnd = csrfStart + csrfEndMatch.index + csrfEndMatch[0].length;
      content = content.slice(0, csrfStart) + content.slice(csrfEnd);
    } else if (content.includes('$contactCsrf') || content.includes('$contactFlash')) {
      throw new Error('Reliquat de variables Contact détecté dans index.php');
    }

    const realizationStart = content.indexOf('<!-- SECTION NOS RÉALISATIONS -->');
    const mapStart = content.indexOf('<!-- SECTION GOOGLE MAPS HEMIP -->');
    if (realizationStart >= 0) {
      if (mapStart <= realizationStart) throw new Error('Bornes de la galerie de l’accueil introuvables');
      content = content.slice(0, realizationStart) + content.slice(mapStart);
    }

    const contactStart = content.indexOf('<!-- SECTION CONTACT -->');
    const footerStart = content.indexOf('<footer class="footer reveal">', contactStart);
    if (contactStart >= 0) {
      if (footerStart <= contactStart) throw new Error('Bornes du formulaire de l’accueil introuvables');
      content = content.slice(0, contactStart) + content.slice(footerStart);
    }
  }

  content = injectRealizationsLink(content, newline);
  planWrite(rel, original, content);
}

let contactHandler = read('contact-submit.php');
contactHandler = contactHandler.replace('header(\'Location: index.php#contact\', true, 303);', 'header(\'Location: contact.php#contact\', true, 303);');
planWrite('contact-submit.php', read('contact-submit.php'), contactHandler);

let serviceWorker = read('sw.js');
serviceWorker = serviceWorker
  .replace('shell-v4', 'shell-v5')
  .replace('pages-v1', 'pages-v2')
  .replace('assets-v4', 'assets-v5');
if (!serviceWorker.includes('"assets/css/independent-pages.css"')) {
  serviceWorker = serviceWorker.replace('  "assets/css/home-sections.css",', '  "assets/css/home-sections.css",\n  "assets/css/independent-pages.css",');
}
if (!serviceWorker.includes('"realisations.html"')) {
  serviceWorker = serviceWorker.replace('  "actualite.html",', '  "actualite.html",\n  "realisations.html",');
}
planWrite('sw.js', read('sw.js'), serviceWorker);

let manifest = JSON.parse(read('manifest.json'));
manifest.shortcuts = Array.isArray(manifest.shortcuts) ? manifest.shortcuts : [];
for (const shortcut of [
  { name: 'Nos réalisations', short_name: 'Réalisations', url: './realisations.html' },
  { name: 'Contact', short_name: 'Contact', url: './contact.php' }
]) {
  if (!manifest.shortcuts.some(item => item.url === shortcut.url)) {
    manifest.shortcuts.push({
      ...shortcut,
      icons: [{ src: './assets/icons/hemip-192.png', sizes: '192x192', type: 'image/png' }]
    });
  }
}
const manifestText = JSON.stringify(manifest, null, 2) + '\n';
planWrite('manifest.json', read('manifest.json'), manifestText);

const globalResponsive = `\n\n/* Fluidité et garde-fous communs pour les pages PHP et HTML. */
html { -webkit-text-size-adjust: 100%; text-size-adjust: 100%; }
body { min-width: 320px; }
img, video, iframe { max-width: 100%; }
iframe { display: block; }
input, textarea, select, button { max-width: 100%; }
main, section, article, .navbar, .nav-list, .location-container, .footer-container,
.container_pourquoi, .content_pourquoi, .partners-container, .formation-container,
.containerpage, .containerIns, .form-wrapper, .card { min-width: 0; }
.visually-hidden {
    position: absolute !important;
    width: 1px !important;
    height: 1px !important;
    padding: 0 !important;
    margin: -1px !important;
    overflow: hidden !important;
    clip: rect(0, 0, 0, 0) !important;
    white-space: nowrap !important;
    border: 0 !important;
}
table { max-width: 100%; }

@media (min-width: 769px) and (max-width: 1100px) {
    .navbar { padding-inline: 16px; }
    .nav-list { gap: 0; }
    .nav-list > li > a, .dropdown-btn { padding-inline: 8px; font-size: 13px; }
}

@media (max-width: 768px) {
    .navbar { padding-inline: 16px; }
    .nav-list { max-height: calc(100dvh - 65px); overflow-y: auto; overscroll-behavior: contain; }
    .dropdown-menu, .submenu-menu { max-width: 100%; }
    .location-container { width: 100%; grid-template-columns: minmax(0, 1fr); }
    .location-content, .map-container { min-width: 0; }
    .map-container iframe { width: 100%; }
    .container_pourquoi { width: calc(100% - 32px); grid-template-columns: minmax(0, 1fr); gap: 24px; }
    .content_pourquoi { width: 100%; margin-inline: 0; }
    .image_container_pourquoi { width: 100%; max-width: 100%; grid-area: auto; }
    .image_container_pourquoi img { max-width: 100%; height: auto; }
    .partners-container, .logo-slider { max-width: 100%; overflow: hidden; }
    .logo-track { width: max-content; max-width: none; }
    .footer-container { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .footer-col, .footer-contact, .footer-contact a { min-width: 0; overflow-wrap: anywhere; }
    .btn { white-space: normal; line-height: 1.35; }
    .section, .location-section { padding-inline: 20px; }
    table { display: block; overflow-x: auto; }
}

@media (max-width: 520px) {
    .navbar { padding-inline: 12px; }
    .logo { gap: 7px; font-size: 18px; }
    .logo img { width: 38px; height: 38px; }
    .container_pourquoi { width: calc(100% - 32px); }
    .image_container_pourquoi { grid-template-columns: minmax(0, 1fr); }
    .location-section { padding: 54px 16px; }
    .location-content h2 { font-size: clamp(30px, 9vw, 38px); line-height: 1.2; }
    .map-container { height: clamp(240px, 72vw, 320px); border-width: 4px; }
    .footer-container { grid-template-columns: minmax(0, 1fr); }
    .section, .section-header { min-width: 0; }
    .section h1, .section h2, .section h3, .section p { overflow-wrap: anywhere; }
}
`;
let baseCss = read('assets/css/style.css');
if (!baseCss.includes('/* Fluidité et garde-fous communs')) baseCss += globalResponsive;
planWrite('assets/css/style.css', read('assets/css/style.css'), baseCss);

const mobileHero = `\n\n/* Sur téléphone, les photos du carrousel restent intégralement visibles. */
@media (max-width: 640px) {
    .hero {
        min-height: 0;
        padding: 0 16px 112px;
        align-items: stretch;
        background: #071a33;
    }

    .hero-slider {
        inset: 65px 0 auto;
        height: clamp(180px, 72vw, 320px);
        background: #071a33;
    }

    .hero-slide img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        object-position: center;
        background: #071a33;
    }

    .hero-content {
        width: 100%;
        max-width: none;
        margin-top: calc(65px + clamp(180px, 72vw, 320px) + 22px);
    }

    .hero-content h1 { font-size: clamp(38px, 12vw, 58px); }
    .hero .hero-buttons { margin-bottom: 0; }
    .home-slider-controls { right: 16px; bottom: 58px; }
    .home-slider-status { right: 16px; bottom: 24px; }
}

@media (max-width: 360px) {
    .hero { padding-inline: 12px; }
    .hero-content h1 { font-size: clamp(36px, 11.5vw, 44px); }
    .home-slider-controls { gap: 5px; }
    .home-slider-controls button { min-width: 34px; min-height: 34px; }
}
`;
let homeCss = read('assets/css/home-sections.css');
if (!homeCss.includes('/* Sur téléphone, les photos du carrousel restent intégralement visibles. */')) homeCss += mobileHero;
planWrite('assets/css/home-sections.css', read('assets/css/home-sections.css'), homeCss);

for (const change of changes) {
  console.log(`${dryRun ? 'WOULD UPDATE' : 'UPDATE'} ${change.file}`);
}
const finalContent = file => changes.find(item => item.file === file)?.updated ?? read(file);
if (!finalContent('index.php').includes('href="realisations.html"') || finalContent('index.php').includes('SECTION CONTACT')) throw new Error('Postconditions de séparation de l’accueil non satisfaites.');
if (!finalContent('contact-submit.php').includes("Location: contact.php#contact")) throw new Error('La route de retour du handler est incorrecte.');
if (!finalContent('sw.js').includes('shell-v5') || !finalContent('sw.js').includes('pages-v2') || !finalContent('sw.js').includes('assets-v5')) throw new Error('Versions attendues du service worker absentes.');
if (!dryRun) {
  for (const { file, updated } of changes) {
    fs.writeFileSync(path.join(root, file), updated, 'utf8');
  }
}
console.log(`${dryRun ? 'Simulation' : 'Écriture'} terminée : ${changes.length} fichiers ${dryRun ? 'seraient ' : ''}mis à jour.`);
