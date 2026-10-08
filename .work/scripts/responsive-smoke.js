const fs = require('node:fs');
const path = require('node:path');
const { execFileSync } = require('node:child_process');
const { pathToFileURL } = require('node:url');
const { chromium } = require('C:/Users/Le MADISON/node_modules/.pnpm/playwright@1.63.0/node_modules/playwright');

const project = 'C:/xampp/htdocs/HEMIP-main/config';
const workspace = 'C:/Users/Le MADISON/OneDrive/Desktop/HEMIP-main';
const output = path.join(workspace, '.work/previews/responsive-smoke');
const sessionDir = path.join(workspace, '.work/contact-test-sessions');
const php = 'C:/xampp/php/php.exe';
const base = pathToFileURL(project + path.sep).href;
const tests = [
  ...[320, 360, 390, 430, 640, 768].map(width => ({ route: 'index.php', name: 'home', width, height: width < 400 ? 780 : 900, php: true })),
  ...[320, 360, 390, 430, 768].map(width => ({ route: 'contact.php', name: 'contact', width, height: width < 400 ? 780 : 900, php: true })),
  ...[320, 390, 768].map(width => ({ route: 'realisations.html', name: 'realisations', width, height: width < 400 ? 780 : 900, php: false }))
];
const results = [];
const failures = [];
fs.mkdirSync(output, { recursive: true });
fs.mkdirSync(sessionDir, { recursive: true });

function makePreview(test) {
  let html = test.php
    ? execFileSync(php, ['-d', `session.save_path=${sessionDir}`, path.join(project, test.route)], { cwd: project, encoding: 'utf8' })
    : fs.readFileSync(path.join(project, test.route), 'utf8');
  html = html.replace(/<head\b([^>]*)>/i, match => `${match}\n<base href="${base}">`);
  html = html.replace(/action\s*=\s*(["'])contact-submit\.php\1/gi, 'action="#qa-no-submit"');
  html = html.replace(/(<input\b[^>]*\bname="csrf_token"[^>]*\bvalue=")[^"]*(")/i, '$1qa-token-redacted$2');
  const file = path.join(output, `${test.name}-preview.html`);
  fs.writeFileSync(file, html, 'utf8');
  return file;
}

(async () => {
  const browser = await chromium.launch({ headless: true, executablePath: 'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe', args: ['--disable-gpu', '--no-sandbox'] });
  try {
    for (const test of tests) {
      const preview = makePreview(test);
      const page = await browser.newPage({ viewport: { width: test.width, height: test.height }, deviceScaleFactor: 1 });
      const pageErrors = [];
      const fileFailures = [];
      page.on('pageerror', error => pageErrors.push(error.message));
      page.on('requestfailed', request => {
        if (request.url().startsWith('file:') && !request.url().toLowerCase().endsWith('.mp4')) fileFailures.push(request.url());
      });
      await page.route(/^https?:\/\//, route => route.abort());
      await page.goto(pathToFileURL(preview).href, { waitUntil: 'load' });
      await page.locator('.reveal').evaluateAll(elements => elements.forEach(element => element.classList.add('active'))).catch(() => {});
      await page.locator('img').evaluateAll(images => images.forEach(image => { image.loading = 'eager'; }));
      await page.locator('img').evaluateAll(images => Promise.all(images.map(image => image.decode().catch(() => null))));
      const metrics = await page.evaluate(() => {
        const slider = document.querySelector('.hero-slider');
        const controls = document.querySelector('.home-slider-controls');
        const status = document.querySelector('.home-slider-status');
        const rect = element => {
          if (!element) return null;
          const r = element.getBoundingClientRect();
          return { top: Math.round(r.top), right: Math.round(r.right), bottom: Math.round(r.bottom), left: Math.round(r.left), width: Math.round(r.width), height: Math.round(r.height) };
        };
        const email = document.querySelector('.contact-page-direct a[href^="mailto:"]');
        const menu = document.getElementById('menu-btn');
        const menuRect = rect(menu);
        return {
          viewport: innerWidth,
          htmlWidth: document.documentElement.scrollWidth,
          bodyWidth: document.body.scrollWidth,
          slider: rect(slider),
          controls: rect(controls),
          status: rect(status),
          slides: [...document.querySelectorAll('.hero-slide img')].map(image => ({ loaded: image.complete && image.naturalWidth > 0, objectFit: getComputedStyle(image).objectFit })),
          emailFontSize: email ? getComputedStyle(email).fontSize : null,
          emailBoxWidth: email ? Math.round(email.getBoundingClientRect().width) : null,
          menu: menuRect ? { ...menuRect, expanded: menu.getAttribute('aria-expanded') } : null
        };
      });
      const screenshot = path.join(output, `${test.name}-${test.width}x${test.height}.png`);
      await page.screenshot({ path: screenshot });
      if (metrics.htmlWidth > test.width || metrics.bodyWidth > test.width) failures.push(`${test.route}@${test.width}: débordement (${metrics.htmlWidth}/${metrics.bodyWidth}).`);
      if (test.name === 'home' && test.width <= 640) {
        if (metrics.slides.some(slide => !slide.loaded || slide.objectFit !== 'contain')) failures.push(`Hero@${test.width}: photo incomplète ${JSON.stringify(metrics.slides)}.`);
        if (!metrics.slider || !metrics.controls || !metrics.status || metrics.controls.top < metrics.slider.top || metrics.controls.bottom > metrics.slider.bottom || metrics.status.bottom > metrics.slider.bottom) failures.push(`Hero@${test.width}: commandes hors du cadre image ${JSON.stringify({ slider: metrics.slider, controls: metrics.controls, status: metrics.status })}.`);
      }
      if (test.name === 'contact' && test.width <= 340 && metrics.emailFontSize !== '12px') failures.push(`Contact@${test.width}: règle typo petits écrans non appliquée (${metrics.emailFontSize}).`);
      if (test.width <= 768 && metrics.menu) {
        if (metrics.menu.width < 44 || metrics.menu.height < 44) failures.push(`${test.route}@${test.width}: zone du hamburger trop petite.`);
        await page.locator('#menu-btn').click();
        const state = await page.evaluate(() => ({ active: document.getElementById('nav-list')?.classList.contains('active'), expanded: document.getElementById('menu-btn')?.getAttribute('aria-expanded') }));
        if (!state.active || state.expanded !== 'true') failures.push(`${test.route}@${test.width}: menu non ouvrable ${JSON.stringify(state)}.`);
      }
      if (pageErrors.length || fileFailures.length) failures.push(`${test.route}@${test.width}: erreurs ${JSON.stringify({ pageErrors, fileFailures })}.`);
      results.push({ route: test.route, width: test.width, screenshot, ...metrics, pageErrors, fileFailures });
      await page.close();
    }
  } finally {
    await browser.close();
  }
  const report = { ok: failures.length === 0, testCount: tests.length, widths: [...new Set(tests.map(test => test.width))], failures, results };
  fs.writeFileSync(path.join(output, 'responsive-smoke.json'), JSON.stringify(report, null, 2), 'utf8');
  console.log(JSON.stringify({ ok: report.ok, testCount: report.testCount, widths: report.widths, failures: report.failures, output }, null, 2));
  if (failures.length) process.exitCode = 1;
})().catch(error => { console.error(error); process.exitCode = 1; });
