const fs = require("node:fs");
const path = require("node:path");
const { execFileSync } = require("node:child_process");
const { pathToFileURL } = require("node:url");
const { chromium } = require("C:/Users/Le MADISON/node_modules/.pnpm/playwright@1.63.0/node_modules/playwright");

const root = "C:/Users/Le MADISON/OneDrive/Desktop/HEMIP-main";
const config = path.join(root, "config");
const output = path.join(root, ".work/previews");
const php = "C:/xampp/php/php.exe";
const htmlPath = path.join(output, "homepage-integrated.html");

(async () => {
  fs.mkdirSync(output, { recursive: true });
  const sessionDirectory = path.join(root, ".work/contact-test-sessions");
  fs.mkdirSync(sessionDirectory, { recursive: true });
  const rendered = execFileSync(php, ["-d", `session.save_path=${sessionDirectory}`, path.join(config, "index.php")], {
    cwd: config,
    encoding: "utf8",
    maxBuffer: 12 * 1024 * 1024
  });
  if (!rendered.includes("data-home-carousel") || !rendered.includes('id="contact"')) {
    throw new Error("Le rendu PHP ne contient pas les sections attendues.");
  }
  const base = pathToFileURL(config + path.sep).href;
  let html = rendered;
  html = html.replace(/\b(src|href)="assets\//g, (_, attribute) => `${attribute}="${base}assets/`);
  html = html.replace(/\bhref="manifest\.json"/g, `href="${base}manifest.json"`);
  html = html.replace(/\baction="contact-submit\.php"/g, 'action="#contact"');
  html = html.replace(/(<input\b[^>]*\bname="csrf_token"[^>]*\bvalue=")[^"]*(")/i, "$1preview-token-redacted$2");
  fs.writeFileSync(htmlPath, html, "utf8");

  const browser = await chromium.launch({
    headless: true,
    executablePath: "C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe",
    args: ["--disable-gpu", "--no-sandbox"]
  });
  const results = [];
  try {
    for (const viewport of [
      { name: "desktop", width: 1440, height: 900 },
      { name: "mobile", width: 390, height: 844 }
    ]) {
      const page = await browser.newPage({ viewport: { width: viewport.width, height: viewport.height }, deviceScaleFactor: 1 });
      const pageErrors = [];
      const localFailures = [];
      page.on("pageerror", error => pageErrors.push(error.message));
      page.on("requestfailed", request => {
        if (request.url().startsWith("file:")) localFailures.push(request.url());
      });
      await page.route(/^https?:\/\//, route => route.abort());
      await page.goto(pathToFileURL(htmlPath).href, { waitUntil: "load" });
      await page.locator(".reveal").evaluateAll(elements => elements.forEach(element => element.classList.add("active")));
      await page.locator("img").evaluateAll(images => images.forEach(image => { image.loading = "eager"; }));
      await page.locator("img").evaluateAll(images => Promise.all(images.map(image => image.decode().catch(() => null))));
      await page.waitForTimeout(750);

      const metrics = await page.evaluate(() => {
        const images = [...document.querySelectorAll(".hero-slide img, .home-realization-card img")];
        const internalLinks = [...document.querySelectorAll('a[href^="#"]')]
          .map(link => link.getAttribute("href").slice(1))
          .filter(Boolean);
        return {
          documentWidth: document.documentElement.scrollWidth,
          viewportWidth: window.innerWidth,
          activeSlides: document.querySelectorAll(".hero-slide.is-active").length,
          mobileMenuButton: (() => {
            const button = document.getElementById("menu-btn");
            const rect = button.getBoundingClientRect();
            return { width: rect.width, height: rect.height, display: getComputedStyle(button).display };
          })(),
          images: images.map(image => ({
            file: image.getAttribute("src"),
            loaded: image.complete && image.naturalWidth > 0,
            naturalWidth: image.naturalWidth
          })),
          unresolvedAnchors: [...new Set(internalLinks.filter(id => !document.getElementById(id)))],
          contactFormFields: [...document.querySelectorAll("#contact form input, #contact form textarea")]
            .map(input => input.name).filter(Boolean)
        };
      });
      const screenshot = path.join(output, `homepage-integrated-${viewport.name}.png`);
      await page.screenshot({ path: screenshot, fullPage: true });

      await page.locator("[data-slide-next]").click();
      await page.waitForTimeout(700);
      const nextState = await page.evaluate(() => ({
        active: document.querySelector(".hero-slide.is-active")?.id,
        paused: document.querySelector("[data-slide-toggle]")?.getAttribute("aria-pressed")
      }));
      if (nextState.active !== "home-slide-2" || nextState.paused !== "true") {
        throw new Error(`${viewport.name}: la flèche suivante ou la pause clavier/souris ne fonctionne pas (${JSON.stringify(nextState)}).`);
      }
      await page.locator("[data-slide-toggle]").click();
      if (await page.locator("[data-slide-toggle]").getAttribute("aria-pressed") !== "false") {
        throw new Error(`${viewport.name}: le bouton Reprendre ne relance pas la rotation.`);
      }
      await page.locator("[data-slide-toggle]").click();
      if (await page.locator("[data-slide-toggle]").getAttribute("aria-pressed") !== "true") {
        throw new Error(`${viewport.name}: le bouton Pause ne met pas la rotation en pause.`);
      }

      await page.locator('.hero-buttons a[href="#formations"]').click();
      await page.waitForTimeout(150);
      const formationState = await page.evaluate(() => {
        const menu = document.querySelector("#formations > .dropdown-menu");
        const style = menu ? getComputedStyle(menu) : null;
        return {
          menuActive: document.getElementById("formations")?.classList.contains("active"),
          expanded: document.querySelector("#formations > .dropdown-btn")?.getAttribute("aria-expanded"),
          visible: !!style && style.visibility === "visible" && style.display !== "none",
          mobileNavigationOpen: document.getElementById("nav-list")?.classList.contains("active")
        };
      });
      if (!formationState.menuActive || formationState.expanded !== "true" || !formationState.visible) {
        throw new Error(`${viewport.name}: le CTA Formation n’ouvre pas la liste existante (${JSON.stringify(formationState)}).`);
      }
      if (viewport.width <= 768 && !formationState.mobileNavigationOpen) {
        throw new Error("mobile: le CTA Formation n’ouvre pas le menu hamburger.");
      }

      await page.locator('#nav-list a[href="#contact"]').click();
      const contactState = await page.evaluate(() => ({
        hash: location.hash,
        mobileNavigationOpen: document.getElementById("nav-list")?.classList.contains("active"),
        menuExpanded: document.getElementById("menu-btn")?.getAttribute("aria-expanded")
      }));
      if (contactState.hash !== "#contact") throw new Error(`${viewport.name}: le lien Contact n’atteint pas l’ancre.`);
      if (viewport.width <= 768 && (contactState.mobileNavigationOpen || contactState.menuExpanded !== "false")) {
        throw new Error(`mobile: le choix de Contact ne referme pas le menu (${JSON.stringify(contactState)}).`);
      }

      if (metrics.documentWidth > metrics.viewportWidth) throw new Error(`${viewport.name}: débordement horizontal (${metrics.documentWidth}px).`);
      if (viewport.width <= 768 && (metrics.mobileMenuButton.width < 44 || metrics.mobileMenuButton.height < 44)) {
        throw new Error(`mobile: hamburger trop petit (${JSON.stringify(metrics.mobileMenuButton)}).`);
      }
      if (metrics.activeSlides !== 1) throw new Error(`${viewport.name}: état initial du carrousel invalide.`);
      if (metrics.unresolvedAnchors.length) throw new Error(`${viewport.name}: ancres manquantes ${metrics.unresolvedAnchors.join(", ")}.`);
      if (metrics.images.some(image => !image.loaded)) throw new Error(`${viewport.name}: images locales non chargées ${JSON.stringify(metrics.images.filter(image => !image.loaded))}.`);
      if (pageErrors.length || localFailures.length) throw new Error(`${viewport.name}: erreurs navigateur ${JSON.stringify({ pageErrors, localFailures })}.`);

      results.push({ viewport: viewport.name, screenshot, ...metrics, nextState, formationState, contactState, pageErrors, localFailures });
      await page.close();
    }

    const reduced = await browser.newPage({ viewport: { width: 390, height: 844 }, reducedMotion: "reduce" });
    const reducedErrors = [];
    reduced.on("pageerror", error => reducedErrors.push(error.message));
    await reduced.route(/^https?:\/\//, route => route.abort());
    await reduced.goto(pathToFileURL(htmlPath).href, { waitUntil: "load" });
    const reducedStart = await reduced.evaluate(() => ({
      active: document.querySelector(".hero-slide.is-active")?.id,
      paused: document.querySelector("[data-slide-toggle]")?.getAttribute("aria-pressed")
    }));
    await reduced.waitForTimeout(6700);
    const reducedEnd = await reduced.evaluate(() => document.querySelector(".hero-slide.is-active")?.id);
    if (reducedStart.paused !== "true" || reducedStart.active !== reducedEnd || reducedErrors.length) {
      throw new Error(`prefers-reduced-motion: comportement inattendu ${JSON.stringify({ reducedStart, reducedEnd, reducedErrors })}.`);
    }
    results.push({ viewport: "mobile-reduced-motion", reducedStart, reducedEnd, pageErrors: reducedErrors });
    await reduced.close();
  } finally {
    await browser.close();
  }
  console.log(JSON.stringify({ renderedFrom: "PHP CLI output, no server or SMTP", results }, null, 2));
})().catch(error => {
  console.error(error);
  process.exitCode = 1;
});
