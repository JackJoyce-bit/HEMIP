const fs = require('node:fs');
const path = require('node:path');
const { execFileSync } = require('node:child_process');

const root = 'C:/xampp/htdocs/HEMIP-main/config';
const php = 'C:/xampp/php/php.exe';
const errors = [];
const report = { php: [], javascript: [], routes: [], cache: [], forms: [], css: [] };

function walk(dir, files = []) {
  for (const entry of fs.readdirSync(dir, { withFileTypes: true })) {
    if (entry.isDirectory()) {
      if (!['assets', 'uploads', 'docs', 'vendor', 'node_modules'].includes(entry.name.toLowerCase())) {
        walk(path.join(dir, entry.name), files);
      }
    } else files.push(path.join(dir, entry.name));
  }
  return files;
}
function check(condition, message) { if (!condition) errors.push(message); }
function read(rel) { return fs.readFileSync(path.join(root, rel), 'utf8'); }

const files = walk(root);
for (const file of files.filter(file => file.toLowerCase().endsWith('.php'))) {
  try {
    execFileSync(php, ['-l', file], { encoding: 'utf8', stdio: ['ignore', 'pipe', 'pipe'] });
    report.php.push(path.relative(root, file));
  } catch (error) {
    errors.push(`PHP ${path.relative(root, file)}: ${(error.stderr || error.message).toString().trim()}`);
  }
}
for (const rel of ['assets/js/main.js', 'assets/js/home-sections.js', 'assets/js/pwa.js', 'sw.js']) {
  try {
    execFileSync(process.execPath, ['--check', path.join(root, rel)], { encoding: 'utf8', stdio: ['ignore', 'pipe', 'pipe'] });
    report.javascript.push(rel);
  } catch (error) {
    errors.push(`JavaScript ${rel}: ${(error.stderr || error.message).toString().trim()}`);
  }
}

let manifest;
try { manifest = JSON.parse(read('manifest.json')); }
catch (error) { errors.push(`manifest.json : ${error.message}`); }
if (manifest) {
  for (const item of manifest.shortcuts || []) {
    const target = item.url.replace(/^\.\//, '').split('#')[0];
    check(fs.existsSync(path.join(root, target)), `Raccourci manifeste manquant : ${item.url}`);
  }
}

const sw = read('sw.js');
const shellBlock = sw.match(/const SHELL_FILES = \[([\s\S]*?)\]\.map/);
const pagesBlock = sw.match(/const PUBLIC_HTML_PAGES = new Set\(\[([\s\S]*?)\]\);/);
check(!!shellBlock, 'Liste SHELL_FILES introuvable');
check(!!pagesBlock, 'Liste PUBLIC_HTML_PAGES introuvable');
if (shellBlock) {
  const paths = [...shellBlock[1].matchAll(/["']([^"']+)["']/g)].map(match => match[1]);
  report.cache.push(...paths);
  for (const asset of paths) check(fs.existsSync(path.join(root, asset)), `Ressource précachée absente : ${asset}`);
}
if (pagesBlock) {
  const paths = [...pagesBlock[1].matchAll(/["']([^"']+)["']/g)].map(match => match[1]);
  report.cache.push(...paths.map(item => `page:${item}`));
  check(paths.includes('realisations.html'), 'realisations.html ne figure pas dans les pages publiques du cache');
  check(!paths.includes('contact.php') && !paths.includes('index.php'), 'Une page PHP a été autorisée dans le cache HTML public');
  check(paths.every(item => item.endsWith('.html')), 'La liste des pages publiques contient autre chose que du HTML statique');
  for (const page of paths) check(fs.existsSync(path.join(root, page)), `Page publique du cache absente : ${page}`);
}
check(/shell-v7/.test(sw) && /pages-v2/.test(sw) && /assets-v7/.test(sw), 'Versions de cache attendues absentes');
check(/request\.method !== "GET"/.test(sw), 'Le filtre de méthode GET du service worker a changé');

const index = read('index.php');
check(!index.includes('SECTION NOS RÉALISATIONS') && !index.includes('SECTION CONTACT'), 'Une ancienne section Réalisations/Contact demeure sur index.php');
check(!index.includes('$contactCsrf') && !index.includes('$contactFlash'), 'Variables Contact encore présentes sur index.php');
check(index.includes('href="realisations.html"') && index.includes('href="contact.php"'), 'Le menu de l’accueil ne relie pas les pages autonomes');
const contact = read('contact.php');
for (const field of ['prenom', 'nom', 'email', 'telephone', 'objet', 'message', 'consentement']) {
  check(new RegExp(`name="${field}"`).test(contact), `Champ Contact absent : ${field}`);
  report.forms.push(field);
}
check(contact.includes('name="csrf_token"') && contact.includes('name="website"'), 'CSRF ou honeypot absent de Contact');
check(contact.includes('action="contact-submit.php"'), 'Contact ne poste pas vers le handler existant');
const handler = read('contact-submit.php');
check(handler.includes("Location: contact.php#contact"), 'Le handler ne revient pas vers contact.php');
check(handler.includes('hemilaperceeinformation@gmail.com'), 'Le destinataire fixe n’est plus attesté par l’endpoint ou sa config');

const achievement = read('realisations.html');
for (const phrase of ['25', '10', '56', 'essai expérimental', 'production industrielle']) {
  check(achievement.toLowerCase().includes(phrase.toLowerCase()), `Mention attendue absente de Réalisations : ${phrase}`);
}
for (const rel of ['assets/css/style.css', 'assets/css/home-sections.css', 'assets/css/independent-pages.css']) {
  const css = read(rel).replace(/\/\*[\s\S]*?\*\//g, '');
  const opens = (css.match(/\{/g) || []).length;
  const closes = (css.match(/\}/g) || []).length;
  check(opens === closes, `Accolades CSS non équilibrées dans ${rel} : ${opens}/${closes}`);
  report.css.push({ file: rel, opens, closes });
}
const homeCss = read('assets/css/home-sections.css');
check(homeCss.includes('object-fit: contain'), 'Le hero mobile ne déclare pas object-fit: contain');

for (const file of files.filter(file => /\.(php|html)$/i.test(file))) {
  const rel = path.relative(root, file).replace(/\\/g, '/');
  const text = fs.readFileSync(file, 'utf8');
  check(!/index\.php#contact/i.test(text), `Ancien lien Contact trouvé : ${rel}`);
  if (text.includes('class="nav-list"')) {
    const nav = text.slice(text.indexOf('class="nav-list"'), text.indexOf('</nav>', text.indexOf('class="nav-list"')));
    if (nav.includes('href="contact.php"')) check(nav.includes('href="realisations.html"'), `Lien Réalisations absent du menu : ${rel}`);
    if (nav.includes('href="contact.php"') && nav.includes('href="realisations.html"')) report.routes.push(rel);
  }
}

const output = { ok: errors.length === 0, errors, counts: { php: report.php.length, javascript: report.javascript.length, cachedAssetsAndPages: report.cache.length, navigationPages: report.routes.length }, report };
console.log(JSON.stringify(output, null, 2));
if (errors.length) process.exitCode = 1;
