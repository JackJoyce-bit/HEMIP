const CACHE_PREFIX = "hemip-pwa-";
const SHELL_CACHE = `${CACHE_PREFIX}shell-v4`;
const PAGES_CACHE = `${CACHE_PREFIX}pages-v1`;
const ASSETS_CACHE = `${CACHE_PREFIX}assets-v4`;
const APP_BASE = new URL("./", self.registration.scope);
const OFFLINE_URL = new URL("offline.html", APP_BASE).href;
const SHELL_FILES = [
  "offline.html",
  "manifest.json",
  "assets/css/style.css",
  "assets/css/pwa.css",
  "assets/css/home-sections.css",
  "assets/js/main.js",
  "assets/js/home-sections.js",
  "assets/js/pwa.js",
  "assets/img/logo-hemip.jpg",
  "assets/img/hemip-campus.jpg",
  "assets/img/hemip-graduation.jpg",
  "assets/img/hemip-atelier.jpg",
  "assets/img/hemip-etudiants.jpg",
  "assets/img/hemip-ceremonie.jpg",
  "assets/img/hemip-rencontre.jpg",
  "assets/img/hemip-informatique.jpg",
  "assets/img/hemip-vie-ecole.jpg",
  "assets/icons/hemip-180.png",
  "assets/icons/hemip-192.png",
  "assets/icons/hemip-512.png"
].map(path => new URL(path, APP_BASE).href);

// Only these same-origin public documents are static, anonymous HTML pages.
// PHP output can vary by session or contain private form/admin data and is never cached.
const PUBLIC_HTML_PAGES = new Set([
  "actualite.html",
  "gp.html",
  "gl.html",
  "rt.html",
  "mi.html",
  "aii.html",
  "dae.html",
  "glt.html",
  "bfa.html",
  "cit.html",
  "gfc.html",
  "mrh.html"
]);
const MAX_CACHED_ASSETS = 100;
const MAX_ASSET_BYTES = 4 * 1024 * 1024;

function relativePath(url) {
  if (url.origin !== APP_BASE.origin || !url.pathname.startsWith(APP_BASE.pathname)) return null;
  return url.pathname.slice(APP_BASE.pathname.length).toLowerCase();
}

async function offlineResponse() {
  const cache = await caches.open(SHELL_CACHE);
  return (await cache.match(OFFLINE_URL)) || new Response(
    "Vous êtes hors ligne. Reconnectez-vous puis réessayez.",
    { status: 503, headers: { "Content-Type": "text/plain; charset=utf-8" } }
  );
}

async function networkFirstStaticPage(request, url) {
  const pageKey = new URL(url.pathname, APP_BASE).href;
  try {
    const response = await fetch(request);
    const finalUrl = new URL(response.url || request.url);
    const isExpectedHtml = response.ok
      && response.type === "basic"
      && response.headers.get("content-type")?.toLowerCase().includes("text/html")
      && finalUrl.origin === APP_BASE.origin
      && finalUrl.pathname === url.pathname;
    if (isExpectedHtml) {
      const cache = await caches.open(PAGES_CACHE);
      await cache.put(pageKey, response.clone());
    }
    return response;
  } catch {
    const pages = await caches.open(PAGES_CACHE);
    return (await pages.match(pageKey)) || offlineResponse();
  }
}

async function networkOnlyNavigation(request) {
  try {
    return await fetch(request);
  } catch {
    return offlineResponse();
  }
}

async function trimAssetCache(cache) {
  const keys = await cache.keys();
  const excess = keys.length - MAX_CACHED_ASSETS;
  for (const key of keys.slice(0, Math.max(0, excess))) await cache.delete(key);
}

async function cacheFirstAsset(request, url) {
  const cache = await caches.open(ASSETS_CACHE);
  const key = new URL(url.pathname, APP_BASE).href;
  const cached = await cache.match(key);
  if (cached) return cached;

  try {
    const response = await fetch(request);
    const contentType = response.headers.get("content-type")?.toLowerCase() || "";
    const contentLength = Number(response.headers.get("content-length") || 0);
    const isPublicStaticAsset = response.ok
      && response.type === "basic"
      && (contentType.startsWith("text/css")
        || contentType.includes("javascript")
        || contentType.startsWith("image/"))
      && (!contentLength || contentLength <= MAX_ASSET_BYTES);
    if (isPublicStaticAsset) {
      await cache.put(key, response.clone());
      await trimAssetCache(cache);
    }
    return response;
  } catch {
    return new Response("Ressource indisponible hors ligne.", {
      status: 503,
      headers: { "Content-Type": "text/plain; charset=utf-8" }
    });
  }
}

self.addEventListener("install", event => {
  event.waitUntil(
    caches.open(SHELL_CACHE)
      .then(cache => cache.addAll(SHELL_FILES))
      .then(() => self.skipWaiting())
  );
});

self.addEventListener("activate", event => {
  const activeCaches = new Set([SHELL_CACHE, PAGES_CACHE, ASSETS_CACHE]);
  event.waitUntil(
    caches.keys()
      .then(keys => Promise.all(keys
        .filter(key => key.startsWith(CACHE_PREFIX) && !activeCaches.has(key))
        .map(key => caches.delete(key))))
      .then(() => self.clients.claim())
  );
});

self.addEventListener("fetch", event => {
  const request = event.request;
  if (request.method !== "GET" || request.headers.has("range")) return;

  const url = new URL(request.url);
  const path = relativePath(url);
  if (path === null) return;

  if (request.mode === "navigate") {
    if (PUBLIC_HTML_PAGES.has(path)) {
      event.respondWith(networkFirstStaticPage(request, url));
    } else {
      // PHP pages, sessions, authentication, registration, uploads and admin stay network-only.
      event.respondWith(networkOnlyNavigation(request));
    }
    return;
  }

  if (path.startsWith("assets/css/")
      || path.startsWith("assets/js/")
      || path.startsWith("assets/img/")
      || path.startsWith("assets/icons/")) {
    event.respondWith(cacheFirstAsset(request, url));
  }
});
