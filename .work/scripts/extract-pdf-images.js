const fs = require("node:fs");
const path = require("node:path");
const zlib = require("node:zlib");

const pdfPath = "C:/Users/Le MADISON/OneDrive/Desktop/Album Hemip/Capture d’écran 2026-09-05 213040/Capture d’écran 2026-09-05 213040.pdf";
const outputDir = "C:/Users/Le MADISON/OneDrive/Desktop/HEMIP-main/.work/pdf-assets";
const pdf = fs.readFileSync(pdfPath);
const latin = pdf.toString("latin1");
fs.mkdirSync(outputDir, { recursive: true });

const crcTable = new Uint32Array(256);
for (let n = 0; n < 256; n++) {
  let c = n;
  for (let k = 0; k < 8; k++) c = c & 1 ? 0xedb88320 ^ (c >>> 1) : c >>> 1;
  crcTable[n] = c >>> 0;
}

function crc32(buffer) {
  let c = 0xffffffff;
  for (const byte of buffer) c = crcTable[(c ^ byte) & 0xff] ^ (c >>> 8);
  return (c ^ 0xffffffff) >>> 0;
}

function pngChunk(type, data) {
  const name = Buffer.from(type, "ascii");
  const length = Buffer.alloc(4);
  length.writeUInt32BE(data.length);
  const crc = Buffer.alloc(4);
  crc.writeUInt32BE(crc32(Buffer.concat([name, data])));
  return Buffer.concat([length, name, data, crc]);
}

function encodeRgbaPng(width, height, rgba) {
  const stride = width * 4;
  const scanlines = Buffer.alloc((stride + 1) * height);
  for (let y = 0; y < height; y++) {
    const row = y * (stride + 1);
    scanlines[row] = 0;
    rgba.copy(scanlines, row + 1, y * stride, (y + 1) * stride);
  }
  const ihdr = Buffer.alloc(13);
  ihdr.writeUInt32BE(width, 0);
  ihdr.writeUInt32BE(height, 4);
  ihdr[8] = 8;
  ihdr[9] = 6;
  return Buffer.concat([
    Buffer.from([137, 80, 78, 71, 13, 10, 26, 10]),
    pngChunk("IHDR", ihdr),
    pngChunk("IDAT", zlib.deflateSync(scanlines, { level: 7 })),
    pngChunk("IEND", Buffer.alloc(0))
  ]);
}

function imageObjects() {
  const objects = [];
  const objectPattern = /\b(\d+)\s+(\d+)\s+obj\b/g;
  let match;
  while ((match = objectPattern.exec(latin))) {
    const objectEnd = latin.indexOf("endobj", objectPattern.lastIndex);
    if (objectEnd < 0) break;
    const objectText = latin.slice(objectPattern.lastIndex, objectEnd);
    if (/\/Subtype\s*\/Image\b/.test(objectText)) {
      const streamMarker = objectText.search(/stream\r?\n/);
      const dictionary = streamMarker >= 0 ? objectText.slice(0, streamMarker) : objectText;
      const width = Number((dictionary.match(/\/Width\s+(\d+)/) || [])[1]);
      const height = Number((dictionary.match(/\/Height\s+(\d+)/) || [])[1]);
      const bits = Number((dictionary.match(/\/BitsPerComponent\s+(\d+)/) || [])[1]);
      const colorSpace = (dictionary.match(/\/ColorSpace\s*\/(\w+)/) || [])[1];
      const length = Number((dictionary.match(/\/Length\s+(\d+)(?!\s+\d+\s+R)/) || [])[1]);
      const filter = (dictionary.match(/\/Filter\s*\/(\w+)/) || [])[1];
      const alphaObject = Number((dictionary.match(/\/SMask\s+(\d+)\s+\d+\s+R/) || [])[1]) || null;
      const streamToken = /stream\r?\n/.exec(objectText);
      const streamStart = streamToken ? objectPattern.lastIndex + streamMarker + streamToken[0].length : -1;
      objects.push({ id: Number(match[1]), width, height, bits, colorSpace, length, filter, alphaObject, streamStart });
    }
    objectPattern.lastIndex = objectEnd + 6;
  }
  return objects;
}

function inflateImage(object) {
  if (object.filter !== "FlateDecode" || object.bits !== 8 || !object.length || object.streamStart < 0) {
    throw new Error(`Unsupported image object ${object.id}`);
  }
  const inflated = zlib.inflateSync(pdf.subarray(object.streamStart, object.streamStart + object.length));
  return inflated;
}

const objects = imageObjects();
const byId = new Map(objects.map(object => [object.id, object]));
const colorImages = objects.filter(object => object.colorSpace === "DeviceRGB" && object.alphaObject);
if (colorImages.length === 0) throw new Error("No RGB image objects with alpha masks were found.");

let index = 0;
for (const image of colorImages) {
  const rgb = inflateImage(image);
  const pixels = image.width * image.height;
  if (rgb.length !== pixels * 3) throw new Error(`RGB data length mismatch for object ${image.id}: ${rgb.length}`);
  const alphaImage = byId.get(image.alphaObject);
  if (!alphaImage || alphaImage.width !== image.width || alphaImage.height !== image.height) {
    throw new Error(`Missing or mismatched alpha image for object ${image.id}`);
  }
  const alpha = inflateImage(alphaImage);
  if (alpha.length !== pixels) throw new Error(`Alpha data length mismatch for object ${alphaImage.id}: ${alpha.length}`);

  const rgba = Buffer.alloc(pixels * 4);
  for (let pixel = 0; pixel < pixels; pixel++) {
    rgba[pixel * 4] = rgb[pixel * 3];
    rgba[pixel * 4 + 1] = rgb[pixel * 3 + 1];
    rgba[pixel * 4 + 2] = rgb[pixel * 3 + 2];
    rgba[pixel * 4 + 3] = alpha[pixel];
  }

  index++;
  const filename = `pdf-image-${String(index).padStart(2, "0")}-${image.width}x${image.height}.png`;
  const destination = path.join(outputDir, filename);
  fs.writeFileSync(destination, encodeRgbaPng(image.width, image.height, rgba));
  console.log(`${filename}\t${fs.statSync(destination).size} bytes\tPDF object ${image.id}`);
}
console.log(`Reconstructed ${index} transparent PNG images in ${outputDir}`);
