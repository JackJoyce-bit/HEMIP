const fs = require('node:fs');
const path = require('node:path');
const { execFileSync } = require('node:child_process');
const { pathToFileURL } = require('node:url');
const { chromium } = require('C:/Users/Le MADISON/node_modules/.pnpm/playwright@1.63.0/node_modules/playwright');

const project = 'C:/xampp/htdocs/HEMIP-main/config';
const workspace = 'C:/Users/Le MADISON/OneDrive/Desktop/HEMIP-main';
const output = path.join(workspace, '.work/previews/responsive-matrix');
const sessionDir = path.join(workspace, '.work/contact-test-sessions');
const php = 'C:/xampp/php/php.exe';
const viewports = [
  { width: 320, height: 740 }, { width: 360, height: 800 },
  { width: 390, height: 844 }, { width: 430, height: 932 },
  { width: 768, height: 1024 }, { width: 820, height: 1180 },
  { width: 1024, height: 768 }, { width: 1280, height: 800 },
  { width: 1440, height: 900 }, { width: 1920, height: 1080 }
];
const routes = [
  { name: 'home', file: 'index.php', kind: 'php' },
  { name: 'contact', file: 'contact.php', kind: 'php' },
  { name: 'realisations', file: 'realisations.html', kind: 'html' },
  { name: 'formation-gp', file: 'GP.html', kind: 'html' },
  { name: 'actualite', file: 'actualite.html', kind: 'html' }
];
const failures = [];
const records = [];

fs.mkdirSync(output, { recursive: true });
fs.mkdirSync(sessionDir, { recursive: true });
const baseUrl = pathToFileURL(project + path.sep).href;

function prepareHtml(route) {
  let html;
  if (route.kind === 'php') {
    html = execFileSync(php, ['-d', `session.save_path=${sessionDir}`, path.join(project, route.file)], {
      cwd: project, encoding: 'utf8', maxBuffer: 16 * 1024 * 1024
    });
  } else {
    html = fs.readFileSync(path.join(project, route.file), 'utf8');
  }
  if (!/<head\b/i.test(html)) throw new Error(`${route.file} : élément <head> absent du rendu.`);
  html = html.replace(/<head\b([^>]*)>/i, match => `${match}\n<base href="${baseUrl}">`);
  html = html.replace(/action\s*=\s*(["'])contact-submit\.php\1/gi, 'action="#qa-no-submit"');
  html = html.replace(/(<input\b[^>]*\bname="csrf_token"[^>]*\bvalue=")[^"]*(")/i, '$1qa-token-redacted$2');
  const target = path.join(output, `${route.name}-preview.html`);
  fs.writeFileSync(target, html, 'utf8');
  return target;
}

(async () => {
  const pages = routes.map(route => ({ ...route, preview: prepareHtml(route) }));
  const browser = await chromium.launch({
    headless: true,
    executablePath: 'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe',
    args: ['--disable-gpu', '--no-sandbox']
  });
  try {
    for (const route of pages) {
      for (const viewport of viewports) {
        const page = await browser.newPage({ viewport, deviceScaleFactor: 1 });
        const pageErrors = [];
        const localFailures = [];
        page.on('pageerror', error => pageErrors.push(error.message));
        page.on('requestfailed', request => {
          if (request.url().startsWith('file:')) localFailures.push(request.url());
        });
        await page.route(/^https?:\/\//, routeRequest => routeRequest.abort());
        await page.goto(pathToFileURL(route.preview).href, { waitUntil: 'load' });
        await page.locator('.reveal').evaluateAll(elements => elements.forEach(element => element.classList.add('active'))).catch(() => {});
        await page.locator('img').evaluateAll(images => images.forEach(image => { image.loading = 'eager'; }));
        await page.locator('img').evaluateAll(images => Promise.all(images.map(image => image.decode().catch(() => null))));
        await page.waitForTimeout(100);
        await page.evaluate(() => window.scrollTo(0, 0));

        const metrics = await page.evaluate(() => {
          const hero = document.querySelector('.hero');
          const slider = document.querySelector('.hero-slider');
          const heroContent = document.querySelector('.hero-content');
          const slideImages = [...document.querySelectorAll('.hero-slide img')];
          const mobileMenu = document.getElementById('menu-btn');
          const contactAnchor = [...document.querySelectorAll('.nav-list a')].find(link => /contact\.php/i.test(link.getAttribute('href') || ''));
          const realizationAnchor = [...document.querySelectorAll('.nav-list a')].find(link => /realisations\.html/i.test(link.getAttribute('href') || ''));
          const form = document.querySelector('#contact-form');
          return {
            viewportWidth: window.innerWidth,
            htmlWidth: document.documentElement.scrollWidth,
            bodyWidth: document.body.scrollWidth,
            activeSlides: document.querySelectorAll('.hero-slide.is-active').length,
            mobileMenu: mobileMenu ? (() => {
              const rect = mobileMenu.getBoundingClientRect();
              return { width: rect.width, height: rect.height, display: getComputedStyle(mobileMenu).display, expanded: mobileMenu.getAttribute('aria-expanded') };
            })() : null,
            navHasContact: !!contactAnchor,
            navHasRealisations: !!realizationAnchor,
            contactHref: contactAnchor?.getAttribute('href') || null,
            realizationHref: realizationAnchor?.getAttribute('href') || null,
            contactFields: form ? [...form.querySelectorAll('[name]')].map(input => input.name).filter(name => name !== 'csrf_token' && name !== 'website') : [],
            slideImages: slideImages.map(image => ({
              file: image.getAttribute('src'),
              loaded: image.complete && image.naturalWidth > 0,
              naturalWidth: image.naturalWidth,
              naturalHeight: image.naturalHeight,
              objectFit: getComputedStyle(image).objectFit
            })),
            heroGeometry: hero && slider && heroContent ? {
              sliderTop: Math.round(slider.getBoundingClientRect().top),
              sliderBottom: Math.round(slider.getBoundingClientRect().bottom),
              contentTop: Math.round(heroContent.getBoundingClientRect().top),
              heroHeight: Math.round(hero.getBoundingClientRect().height)
            } : null
          };
        });

        const screenshot = path.join(output, `${route.name}-${viewport.width}x${viewport.height}.png`);
        await page.screenshot({ path: screenshot });

        if (metrics.htmlWidth > viewport.width || metrics.bodyWidth > viewport.width) {
          failures.push(`${route.file} @ ${viewport.width}px : débordement HTML/body (${metrics.htmlWidth}/${metrics.bodyWidth}px).`);
        }
        if (route.name === 'home') {
          if (metrics.activeSlides !== 1) failures.push(`Accueil @ ${viewport.width}px : le carrousel n’a pas exactement une diapositive active.`);
          if (metrics.slideImages.some(image => !image.loaded)) failures.push(`Accueil @ ${viewport.width}px : photo non chargée (${JSON.stringify(metrics.slideImages)}).`);
          if (viewport.width <= 640) {
            if (metrics.slideImages.some(image => image.objectFit !== 'contain')) failures.push(`Accueil @ ${viewport.width}px : photo non affichée entièrement (${JSON.stringify(metrics.slideImages)}).`);
            if (metrics.heroGeometry && metrics.heroGeometry.contentTop < metrics.heroGeometry.sliderBottom) failures.push(`Accueil @ ${viewport.width}px : contenu superposé au hero (${JSON.stringify(metrics.heroGeometry)}).`);
          }
        }
        if (route.name === 'contact' || route.name === 'realisations') {
          if (!metrics.navHasContact || !metrics.navHasRealisations) failures.push(`${route.file} : liens d’en-tête manquants (${JSON.stringify({ contact: metrics.navHasContact, realisations: metrics.navHasRealisations })}).`);
        }
        if (route.name === 'contact' && metrics.contactFields.join(',') !== 'prenom,nom,email,telephone,objet,message,consentement') {
          failures.push(`contact.php : champs visibles inattendus ${JSON.stringify(metrics.contactFields)}.`);
        }
        if (viewport.width <= 768 && metrics.mobileMenu) {
          if (metrics.mobileMenu.display === 'none' || metrics.mobileMenu.width < 44 || metrics.mobileMenu.height < 44) {
            failures.push(`${route.file} @ ${viewport.width}px : hamburger caché ou trop petit ${JSON.stringify(metrics.mobileMenu)}.`);
          } else {
            await page.locator('#menu-btn').click();
            const openState = await page.evaluate(() => ({ active: document.getElementById('nav-list')?.classList.contains('active'), expanded: document.getElementById('menu-btn')?.getAttribute('aria-expanded') }));
            if (!openState.active || openState.expanded !== 'true') failures.push(`${route.file} @ ${viewport.width}px : hamburger ne s’ouvre pas (${JSON.stringify(openState)}).`);
            await page.locator('#menu-btn').click();
          }
        }
        if (pageErrors.length) failures.push(`${route.file} @ ${viewport.width}px : erreurs JavaScript ${JSON.stringify(pageErrors)}.`);
        if (localFailures.length) failures.push(`${route.file} @ ${viewport.width}px : ressources locales manquantes ${JSON.stringify(localFailures)}.`);
        records.push({ route: route.file, width: viewport.width, height: viewport.height, screenshot, ...metrics, pageErrors, localFailures });
        await page.close();
      }
    }
  } finally {
    await browser.close();
  }
  const report = { ok: failures.length === 0, routes: routes.length, viewports: viewports.map(item => item.width), screenshots: records.map(item => item.screenshot), failures, records };
  fs.writeFileSync(path.join(output, 'responsive-matrix.json'), JSON.stringify(report, null, 2), 'utf8');
  console.log(JSON.stringify({ ok: report.ok, routes: report.routes, viewportWidths: report.viewports, screenshotCount: report.screenshots.length, failures: report.failures, output }, null, 2));
  if (failures.length) process.exitCode = 1;
})().catch(error => {
  console.error(error);
  process.exitCode = 1;
});
