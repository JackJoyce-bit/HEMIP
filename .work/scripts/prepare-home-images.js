const fs = require("node:fs");
const path = require("node:path");
const http = require("node:http");
const { chromium } = require("C:/Users/Le MADISON/node_modules/.pnpm/playwright@1.63.0/node_modules/playwright");

const sourceRoot = "C:/Users/Le MADISON/OneDrive/Desktop/HEMIP-main/.work/pdf-assets";
const outputRoot = "C:/Users/Le MADISON/OneDrive/Desktop/HEMIP-main/config/assets/img";
const assets = [
  { name: "hemip-campus.jpg", source: "preview-07.jpg" },
  { name: "hemip-graduation.jpg", source: "pdf-image-01-658x485.png" },
  { name: "hemip-atelier.jpg", source: "pdf-image-02-509x448.png" },
  { name: "hemip-etudiants.jpg", source: "pdf-image-03-501x771.png" },
  { name: "hemip-ceremonie.jpg", source: "pdf-image-04-1080x810.png" },
  { name: "hemip-rencontre.jpg", source: "pdf-image-05-1080x810.png" },
  { name: "hemip-informatique.jpg", source: "pdf-image-06-526x358.png" },
  { name: "hemip-vie-ecole.jpg", source: "pdf-image-08-1080x810.png" }
];
const bySource = new Map(assets.map(asset => [asset.source, asset]));
const byOutput = new Map(assets.map(asset => [asset.name, asset]));

const server = http.createServer((req, res) => {
  const url = new URL(req.url, "http://127.0.0.1");
  if (req.method === "GET" && url.pathname === "/convert") {
    res.writeHead(200, { "Content-Type": "text/html; charset=utf-8", "Cache-Control": "no-store" });
    res.end("<!doctype html><meta charset=utf-8><title>Local image conversion</title>");
    return;
  }
  if (req.method === "GET" && url.pathname.startsWith("/source/")) {
    const sourceName = decodeURIComponent(url.pathname.slice("/source/".length));
    if (!bySource.has(sourceName)) { res.writeHead(404).end(); return; }
    const sourcePath = path.join(sourceRoot, sourceName);
    if (!fs.existsSync(sourcePath)) { res.writeHead(404).end(`Missing source ${sourceName}`); return; }
    const type = sourceName.toLowerCase().endsWith(".jpg") ? "image/jpeg" : "image/png";
    res.writeHead(200, { "Content-Type": type, "Cache-Control": "no-store", "X-Content-Type-Options": "nosniff" });
    fs.createReadStream(sourcePath).pipe(res);
    return;
  }
  if (req.method === "POST" && url.pathname === "/save") {
    const outputName = url.searchParams.get("name");
    if (!byOutput.has(outputName)) { res.writeHead(400).end("Unknown output"); return; }
    const chunks = [];
    let size = 0;
    req.on("data", chunk => {
      size += chunk.length;
      if (size > 8 * 1024 * 1024) { req.destroy(new Error("Output exceeds size limit")); return; }
      chunks.push(chunk);
    });
    req.on("end", () => {
      if (!size) { res.writeHead(400).end("Empty image"); return; }
      const outputPath = path.join(outputRoot, outputName);
      fs.writeFileSync(outputPath, Buffer.concat(chunks));
      res.writeHead(201, { "Content-Type": "application/json" }).end(JSON.stringify({ name: outputName, bytes: size }));
    });
    return;
  }
  res.writeHead(404).end("Not found");
});

(async () => {
  await new Promise((resolve, reject) => {
    server.once("error", reject);
    server.listen(0, "127.0.0.1", resolve);
  });
  const address = server.address();
  const browser = await chromium.launch({
    headless: true,
    executablePath: "C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe",
    args: ["--disable-gpu", "--no-sandbox"]
  });
  try {
    const page = await browser.newPage();
    await page.goto(`http://127.0.0.1:${address.port}/convert`, { waitUntil: "load" });
    const results = await page.evaluate(async assetsToConvert => {
      const outputs = [];
      for (const asset of assetsToConvert) {
        const image = new Image();
        image.src = `/source/${encodeURIComponent(asset.source)}`;
        await image.decode();
        const scale = Math.min(1, 1920 / Math.max(image.naturalWidth, image.naturalHeight));
        const width = Math.max(1, Math.round(image.naturalWidth * scale));
        const height = Math.max(1, Math.round(image.naturalHeight * scale));
        const canvas = document.createElement("canvas");
        canvas.width = width;
        canvas.height = height;
        const context = canvas.getContext("2d", { alpha: false });
        context.fillStyle = "#ffffff";
        context.fillRect(0, 0, width, height);
        context.imageSmoothingEnabled = true;
        context.imageSmoothingQuality = "high";
        context.drawImage(image, 0, 0, width, height);
        const blob = await new Promise((resolve, reject) => canvas.toBlob(result => result ? resolve(result) : reject(new Error(`JPEG encode failed: ${asset.name}`)), "image/jpeg", 0.86));
        const response = await fetch(`/save?name=${encodeURIComponent(asset.name)}`, { method: "POST", body: blob });
        if (!response.ok) throw new Error(`Save failed for ${asset.name}: ${response.status}`);
        const saved = await response.json();
        outputs.push({ ...saved, width, height });
        image.src = "";
        canvas.width = canvas.height = 0;
      }
      return outputs;
    }, assets);
    for (const result of results) console.log(JSON.stringify(result));
  } finally {
    await browser.close();
    await new Promise(resolve => server.close(resolve));
  }
})().catch(error => {
  console.error(error);
  server.close();
  process.exitCode = 1;
});
