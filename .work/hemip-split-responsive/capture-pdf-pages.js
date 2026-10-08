const fs = require("node:fs");
const { pathToFileURL } = require("node:url");
const { chromium } = require("C:/Users/Le MADISON/node_modules/.pnpm/playwright@1.63.0/node_modules/playwright");

const pdfPath = "C:/Users/Le MADISON/Downloads/Hemip -.Présentation - Quelques éléments pour le site web - 02-10-2026.pdf";
const edgePath = "C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe";
const outputDir = "C:/Users/Le MADISON/OneDrive/Desktop/HEMIP-main/.work/hemip-split-responsive";

(async () => {
  if (!fs.existsSync(pdfPath) || !fs.existsSync(edgePath)) throw new Error("PDF or Edge is unavailable");
  fs.mkdirSync(outputDir, { recursive: true });
  const browser = await chromium.launch({ headless: true, executablePath: edgePath });
  try {
    const page = await browser.newPage({ viewport: { width: 1280, height: 1600 }, deviceScaleFactor: 1 });
    await page.goto(pathToFileURL(pdfPath).href, { waitUntil: "domcontentloaded", timeout: 30000 });
    await page.waitForTimeout(3500);
    for (let index = 1; index <= 14; index++) {
      const filename = `pdf-pages-${String(index).padStart(2, "0")}.png`;
      const destination = `${outputDir}/${filename}`;
      await page.screenshot({ path: destination });
      console.log(filename);
      if (index < 14) {
        await page.mouse.move(1080, 1150);
        await page.mouse.wheel(0, 1250);
        await page.waitForTimeout(750);
      }
    }
  } finally {
    await browser.close();
  }
})().catch(error => {
  console.error(error);
  process.exit(1);
});
