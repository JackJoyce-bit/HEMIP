/* HEMIP - Ferme la fenêtre cookies après « Tout accepter » / « Tout refuser ».
   Complément du script existant : il ne remplace rien, il garantit la fermeture.
   S'appuie sur : .hemip-cookie-banner, .hemip-cookie-button--accept/--reject,
   [data-cookie-preferences] (voir pwa.css). */
(function () {
  "use strict";
  var FLAG = "hemip-consent-closed";
  var SIX_MONTHS = 183 * 24 * 60 * 60 * 1000;
  var BANNER = ".hemip-cookie-banner";
  var CHOICE = ".hemip-cookie-button--accept, .hemip-cookie-button--reject";
  var MANAGE = "[data-cookie-preferences], .consent-manage";

  function norm(t) { return (t || "").replace(/\s+/g, " ").trim().toLowerCase(); }
  function isChoice(el) {
    if (el.matches && el.matches(CHOICE)) return true;
    var t = norm(el.textContent);
    return t === "tout accepter" || t === "tout refuser";
  }
  function isManage(el) {
    return (el.matches && el.matches(MANAGE)) || norm(el.textContent).indexOf("gérer les cookies") !== -1;
  }

  function save() { try { localStorage.setItem(FLAG, String(Date.now())); } catch (e) {} }
  function clear() { try { localStorage.removeItem(FLAG); } catch (e) {} }
  function saved() {
    try { var v = Number(localStorage.getItem(FLAG)); return !!v && Date.now() - v < SIX_MONTHS; }
    catch (e) { return false; }
  }

  /* Trouve la fenêtre : classe connue, sinon élément fixe / dialogue le plus proche */
  function findBanner(btn) {
    var known = btn.closest && btn.closest(BANNER);
    if (known) return known;
    var el = btn.parentElement;
    while (el && el !== document.body && el !== document.documentElement) {
      var pos = getComputedStyle(el).position;
      if (el.getAttribute("role") === "dialog" || pos === "fixed") return el;
      el = el.parentElement;
    }
    return null;
  }

  function hideBanner(b) {
    if (!b) return;
    b.hidden = true;                              /* règle [hidden] de pwa.css */
    b.setAttribute("aria-hidden", "true");
    b.style.setProperty("display", "none", "important"); /* au cas où une autre règle l'emporte */
  }
  function showBannerAllowed(b) {
    b.style.removeProperty("display");
    b.removeAttribute("aria-hidden");
  }

  document.addEventListener("click", function (e) {
    var btn = e.target.closest && e.target.closest("button, a, [role='button']");
    if (!btn) return;

    if (isChoice(btn)) {
      var banner = findBanner(btn);
      save();
      hideBanner(banner);
      /* le script d'origine s'exécute ensuite ; on referme si besoin */
      setTimeout(function () { hideBanner(banner); }, 60);
      setTimeout(function () { hideBanner(banner); }, 300);
    } else if (isManage(btn)) {
      /* « Gérer les cookies » : on libère la fenêtre, le script d'origine la rouvre */
      clear();
      document.querySelectorAll(BANNER).forEach(showBannerAllowed);
    }
  }, true);

  /* Chargement : si le choix a déjà été fait, ne pas réafficher la fenêtre */
  function sweep() {
    if (!saved()) return;
    document.querySelectorAll(BANNER).forEach(hideBanner);
  }
  function start() {
    sweep();
    if (saved() && "MutationObserver" in window) {
      var n = 0, mo = new MutationObserver(function () { sweep(); if (++n > 200) mo.disconnect(); });
      mo.observe(document.body, { childList: true, subtree: true });
      setTimeout(function () { mo.disconnect(); }, 8000);
    }
  }
  if (document.readyState === "loading") document.addEventListener("DOMContentLoaded", start); else start();
})();
