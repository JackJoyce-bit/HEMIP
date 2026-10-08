const fs = require('node:fs');
const path = require('node:path');
const { execFileSync } = require('node:child_process');
const { pathToFileURL } = require('node:url');
const { chromium } = require('C:/Users/Le MADISON/node_modules/.pnpm/playwright@1.63.0/node_modules/playwright');
const project = 'C:/xampp/htdocs/HEMIP-main/config';
const output = 'C:/Users/Le MADISON/OneDrive/Desktop/HEMIP-main/.work/previews/responsive-smoke';
const sessionDir = 'C:/Users/Le MADISON/OneDrive/Desktop/HEMIP-main/.work/contact-test-sessions';
const php = 'C:/xampp/php/php.exe';
const base = pathToFileURL(project + path.sep).href;
const tests = [
  { file: 'index.php', name: 'home', width: 320, height: 780, php: true },
  { file: 'index.php', name: 'home', width: 390, height: 780, php: true },
  { file: 'contact.php', name: 'contact', width: 320, height: 780, php: true }
];
(async () => {
  fs.mkdirSync(output, { recursive: true });
  fs.mkdirSync(sessionDir, { recursive: true });
  const browser = await chromium.launch({ headless: true, executablePath: 'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe', args: ['--disable-gpu', '--no-sandbox'] });
  const results = [];
  const failures = [];
  try {
    for (const test of tests) {
      let html = execFileSync(php, ['-d', `session.save_path=${sessionDir}`, path.join(project, test.file)], { cwd: project, encoding: 'utf8' });
      html = html.replace(/<head\b([^>]*)>/i, match => `${match}\n<base href="${base}">`);
      html = html.replace(/action\s*=\s*(["'])contact-submit\.php\1/gi, 'action="#qa-no-submit"');
      html = html.replace(/(<input\b[^>]*\bname="csrf_token"[^>]*\bvalue=")[^"]*(")/i, '$1qa-token-redacted$2');
      const preview = path.join(output, `${test.name}-${test.width}-critical.html`);
      fs.writeFileSync(preview, html, 'utf8');
      const page = await browser.newPage({ viewport: { width: test.width, height: test.height } });
      const pageErrors = [];
      page.on('pageerror', error => pageErrors.push(error.message));
      await page.route(/^https?:\/\//, route => route.abort());
      await page.goto(pathToFileURL(preview).href, { waitUntil: 'load' });
      await page.locator('img').evaluateAll(images => Promise.all(images.map(image => image.decode().catch(() => null))));
      const metrics = await page.evaluate(() => {
        const rect = element => { const r = element.getBoundingClientRect(); return { top: Math.round(r.top), bottom: Math.round(r.bottom), width: Math.round(r.width), height: Math.round(r.height) }; };
        const slider = document.querySelector('.hero-slider');
        const pseudo = document.querySelector('.hero') ? getComputedStyle(document.querySelector('.hero'), '::before').backgroundImage : '';
        const email = document.querySelector('.contact-page-direct a[href^="mailto:"]');
        return {
          width: innerWidth,
          htmlWidth: document.documentElement.scrollWidth,
          bodyWidth: document.body.scrollWidth,
          heroPhoto: [...document.querySelectorAll('.hero-slide img')].map(image => ({ loaded: image.complete && image.naturalWidth > 0, naturalWidth: image.naturalWidth, fit: getComputedStyle(image).objectFit })),
          slider: slider ? rect(slider) : null,
          controls: document.querySelector('.home-slider-controls') ? rect(document.querySelector('.home-slider-controls')) : null,
          status: document.querySelector('.home-slider-status') ? rect(document.querySelector('.home-slider-status')) : null,
          mobileBackdrop: pseudo,
          emailFontSize: email ? getComputedStyle(email).fontSize : null
        };
      });
      const screenshot = path.join(output, `${test.name}-${test.width}x${test.height}-critical.png`);
      await page.screenshot({ path: screenshot });
      if (metrics.htmlWidth > test.width || metrics.bodyWidth > test.width) failures.push(`${test.name}@${test.width}: débordement horizontal`);
      if (test.name === 'home') {
        if (metrics.heroPhoto.some(image => !image.loaded || image.fit !== 'contain')) failures.push(`hero@${test.width}: photo non chargée ou recadrée`);
        if (!metrics.controls || !metrics.status || metrics.controls.top < metrics.slider.top || metrics.controls.bottom > metrics.slider.bottom || metrics.status.bottom > metrics.slider.bottom) failures.push(`hero@${test.width}: commandes hors de la photo`);
        if (!metrics.mobileBackdrop.includes('rgba(0, 0, 0, 0)') || !metrics.mobileBackdrop.includes('rgb(7, 26, 51)')) failures.push(`hero@${test.width}: dégradé de contraste mobile absent`);
      }
      if (test.name === 'contact' && metrics.emailFontSize !== '12px') failures.push(`contact@${test.width}: taille e-mail attendue 12 px, obtenu ${metrics.emailFontSize}`);
      if (pageErrors.length) failures.push(`${test.name}@${test.width}: erreurs JS ${JSON.stringify(pageErrors)}`);
      results.push({ ...test, screenshot, ...metrics, pageErrors });
      await page.close();
    }
  } finally { await browser.close(); }
  const report = { ok: failures.length === 0, failures, results };
  fs.writeFileSync(path.join(output, 'responsive-critical.json'), JSON.stringify(report, null, 2), 'utf8');
  console.log(JSON.stringify({ ok: report.ok, failures, results: results.map(({ name, width, htmlWidth, bodyWidth, heroPhoto, controls, status, emailFontSize }) => ({ name, width, htmlWidth, bodyWidth, heroPhoto, controls, status, emailFontSize })) }, null, 2));
  if (failures.length) process.exitCode = 1;
})().catch(error => { console.error(error); process.exitCode = 1; });
