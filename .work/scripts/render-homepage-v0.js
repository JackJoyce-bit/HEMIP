const path = require("node:path");
const { pathToFileURL } = require("node:url");
const { chromium } = require("C:/Users/Le MADISON/node_modules/.pnpm/playwright@1.63.0/node_modules/playwright");

(async () => {
  const preview = "C:/Users/Le MADISON/OneDrive/Desktop/HEMIP-main/.work/previews/homepage-v0.html";
  const output = "C:/Users/Le MADISON/OneDrive/Desktop/HEMIP-main/.work/previews";
  const browser = await chromium.launch({
    headless: true,
    executablePath: "C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe",
    args: ["--disable-gpu", "--no-sandbox"]
  });
  try {
    for (const viewport of [{ name: "desktop", width: 1440, height: 1000 }, { name: "mobile", width: 390, height: 844 }]) {
      const page = await browser.newPage({ viewport: { width: viewport.width, height: viewport.height }, deviceScaleFactor: 1 });
      const errors = [];
      page.on("pageerror", error => errors.push(error.message));
      await page.goto(pathToFileURL(preview).href, { waitUntil: "load" });
      await page.waitForTimeout(300);
      const images = await page.locator("img").evaluateAll(elements => elements.map(image => ({
        src: image.getAttribute("src"),
        loaded: image.complete && image.naturalWidth > 0,
        naturalWidth: image.naturalWidth,
        naturalHeight: image.naturalHeight
      })));
      const screenshot = path.join(output, `homepage-v0-${viewport.name}.png`);
      await page.screenshot({ path: screenshot, fullPage: true });
      console.log(JSON.stringify({ viewport: viewport.name, screenshot, title: await page.title(), documentWidth: await page.locator("body").evaluate(body => body.scrollWidth), images, pageErrors: errors }));
      await page.close();
    }
  } finally {
    await browser.close();
  }
})().catch(error => { console.error(error); process.exitCode = 1; });
