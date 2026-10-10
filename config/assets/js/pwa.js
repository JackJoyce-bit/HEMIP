(() => {
  "use strict";

  const CONSENT_COOKIE = "hemip_optional_media";
  const CONSENT_MAX_AGE = 60 * 60 * 24 * 180;
  const INSTALL_DISMISS_KEY = "hemip-install-prompt-dismissed-until";
  const INSTALL_DISMISS_MS = 30 * 24 * 60 * 60 * 1000;
  const INSTALL_PROMPT_DELAY_MS = 90 * 1000;

  let installPrompt = null;
  let installButton = null;
  let installDialog = null;
  let legalDialog = null;
  let cookieBanner = null;
  let cookieFeedback = null;
  let pendingMediaTrigger = null;
  let previousFocus = null;
  let wasOffline = !navigator.onLine;

  const legalPolicies = {
    privacy: {
      title: "Politique de confidentialité",
      html: `<section><h3>Responsable et contact</h3><p>Le responsable du traitement présenté ici est HEMIP — Haute École de Management et d’Ingénierie la Percée, située à Pointe-Noire, République du Congo (centre-ville, derrière la tour Mayombe). Contact : <a href="mailto:hemilaperceeinformation@gmail.com">hemilaperceeinformation@gmail.com</a> · <a href="tel:+242069149242">+242 06 914 92 42</a>.</p></section>
      <section><h3>Quelles données et pourquoi ?</h3><p>Le formulaire de contact recueille prénom, nom, adresse e-mail, téléphone facultatif, objet et message pour traiter la demande et y répondre. Le message est transmis par le service SMTP/de messagerie utilisé par l’école à son adresse institutionnelle ; le code du site ne l’enregistre pas dans sa base de données.</p><p>Les formulaires d’inscription ou de réinscription peuvent demander identité, date de naissance, e-mail, téléphone, sexe, adresse, filière, niveau, matricule antérieur et mot de passe. Les nouvelles candidatures comprennent les pièces demandées par le formulaire : acte de naissance, diplôme, photo, relevés de notes, certificat médical et assurance. Le mot de passe est haché ; les données sont enregistrées dans les systèmes administratifs du site et les pièces dans un espace de téléversement restreint. Elles servent à recevoir, instruire et gérer les demandes d’admission.</p><p>Les champs obligatoires et documents requis sont nécessaires au traitement du dossier. Le téléphone du formulaire Contact est facultatif. Le formulaire de contact demande un accord pour permettre à HEMIP de traiter les coordonnées afin de répondre. HEMIP doit confirmer et documenter la base juridique applicable à chaque finalité.</p></section>
      <section><h3>Destinataires, conservation et sécurité</h3><p>Les données sont accessibles au personnel HEMIP autorisé et, selon les opérations, aux fournisseurs d’hébergement ou de messagerie/SMTP. Ces prestataires peuvent traiter les données sur des infrastructures situées hors du Congo ; HEMIP doit vérifier les garanties et formalités qui s’appliquent. Les messages transmis par courriel peuvent subsister dans la boîte de l’école et les systèmes de messagerie.</p><p>Les dossiers et pièces sont conservés pour l’instruction et la gestion du parcours, puis selon les obligations et règles d’archivage applicables. <strong>Les durées chiffrées par catégorie doivent être fixées et validées par HEMIP ; aucune durée précise n’est inventée ici.</strong> Les protections visibles comprennent notamment la session/anti-robot du formulaire, le hachage des mots de passe et une restriction d’accès aux pièces déposées. Aucune sécurité n’est absolue.</p></section>
      <section><h3>Vos droits</h3><p>Dans les conditions prévues par le droit applicable au Congo, vous pouvez demander l’accès à vos données, leur rectification et, selon le cas, leur suppression ou vous opposer à certains traitements. Écrivez à <a href="mailto:hemilaperceeinformation@gmail.com">hemilaperceeinformation@gmail.com</a> en donnant les éléments nécessaires pour retrouver le dossier et vérifier votre identité, sans transmettre de pièce excessive. Le cadre de référence comprend la loi n° 29-2019 du 10 octobre 2019 portant protection des données à caractère personnel. <a href="https://natlex.ilo.org/dyn/natlex2/natlex2/files/download/110224/COG-110224.pdf" target="_blank" rel="noopener noreferrer">Lire le texte officiel</a>.</p></section>
      <section><h3>Contenus tiers et PWA</h3><p>YouTube et Google Maps ne sont contactés qu’après autorisation des contenus externes. Après « Tout accepter », les lecteurs et la carte concernés se chargent dans leurs cadres sur cette page. Le fournisseur peut alors recevoir l’adresse IP, des informations techniques du navigateur et l’origine du site, et appliquer ses propres conditions et technologies. Le service worker peut conserver sur votre appareil certaines pages publiques et ressources statiques consultées ; les pages PHP avec formulaires et sessions ne sont pas mises en cache.</p></section>`
    },
    cookies: {
      title: "Politique des cookies",
      html: `<section><h3>Votre choix</h3><p>Le bandeau propose « Tout accepter » ou « Tout refuser » pour les contenus tiers facultatifs. Le refus ne désactive pas les cookies strictement nécessaires à une session demandée par le visiteur. La décision est mémorisée pendant 180 jours dans un cookie de première partie <code>hemip_optional_media</code>, sans profilage publicitaire. Utilisez « Gérer les cookies » dans le pied de page pour modifier ou retirer votre choix.</p></section>
      <section><h3>Stockage nécessaire et PWA</h3><ul><li>Une session PHP peut utiliser un identifiant de session nécessaire au fonctionnement de formulaires, notamment à leur jeton CSRF ; sa durée dépend de la configuration du serveur.</li><li>Le cookie <code>hemip_optional_media</code> mémorise « accepted » ou « rejected » durant 180 jours.</li><li>Le service worker peut stocker localement des pages publiques et ressources déjà consultées pour un mode hors connexion limité. Ce cache n’est pas un cookie et ne contient pas les pages PHP des formulaires.</li></ul></section>
      <section><h3>YouTube et Google Maps</h3><p>Les lecteurs vidéo, servis par <code>youtube-nocookie.com</code>, et la carte Google sont bloqués au chargement initial. En acceptant les contenus externes, les lecteurs et la carte se chargent automatiquement dans leurs cadres sur cette page ; il suffit ensuite d’utiliser les commandes du lecteur pour démarrer une vidéo. Les lecteurs restent dans un cadre intégré isolé : les contrôles ne sont pas autorisés à ouvrir la fenêtre principale ou une nouvelle fenêtre hors du site HEMIP. YouTube/Google restent les fournisseurs du contenu ; un défaut du fournisseur, du navigateur, d’un bloqueur ou une restriction de la vidéo peut encore provoquer une erreur que HEMIP ne contrôle pas.</p><p>Lorsqu’un contenu externe est activé, le navigateur communique avec son fournisseur, qui peut recevoir l’adresse IP, des données techniques et le référent, et appliquer ses propres politiques et cookies.</p></section>
      <section><h3>Refuser ou révoquer</h3><p>Vous pouvez choisir « Tout refuser » dans le bandeau ou modifier votre décision plus tard via « Gérer les cookies ». Le retrait ferme les contenus tiers déjà ouverts sur la page. La suppression des données du site dans votre navigateur efface aussi le choix et fera réapparaître le bandeau. Le blocage de tous les cookies peut empêcher les formulaires à session de fonctionner.</p><p>Questions : <a href="mailto:hemilaperceeinformation@gmail.com">hemilaperceeinformation@gmail.com</a>. Les détails sur les données et vos droits figurent dans la politique de confidentialité.</p></section>`
    },
    terms: {
      title: "Politique d’utilisation du site",
      html: `<section><h3>Objet et informations</h3><p>Le site présente HEMIP, ses formations, actualités, réalisations et formulaires. Les contenus ont une vocation informative et peuvent évoluer. Les programmes, dates, frais et modalités d’admission doivent être confirmés auprès de l’administration ; une préinscription ne vaut pas admission définitive.</p></section>
      <section><h3>Formulaires et conduite attendue</h3><p>Fournissez des informations exactes, détenez les droits sur les documents transmis et n’envoyez que les pièces requises. N’utilisez pas le site pour diffuser un contenu illicite, perturber le service, contourner une authentification ou accéder sans autorisation à des comptes, données ou documents. La soumission d’un dossier est examinée selon les procédures de l’établissement.</p></section>
      <section><h3>Contenus et droits</h3><p>Sauf indication contraire, les textes, photographies, éléments graphiques et logos publiés sont ceux de HEMIP ou de leurs titulaires respectifs. Leur reproduction ou réutilisation commerciale nécessite l’autorisation préalable du détenteur des droits. Les marques de tiers restent la propriété de leurs titulaires.</p></section>
      <section><h3>Services tiers, disponibilité et responsabilité</h3><p>Les vidéos YouTube, la carte Google et les liens vers des services tiers sont soumis aux conditions de leurs fournisseurs. Les lecteurs sont intégrés à la page et isolés pour éviter une navigation hors de HEMIP via leurs contrôles, mais une erreur, une indisponibilité, un blocage du navigateur ou une restriction du fournisseur peut empêcher l’affichage. HEMIP s’efforce de maintenir le site, sans garantir un accès continu ou sans erreur. Rien dans cette politique n’exclut une responsabilité qui ne pourrait légalement être écartée.</p></section>
      <section><h3>Évolution et contact</h3><p>Ces règles peuvent être mises à jour lorsque le site ou les textes applicables changent. Pour une question, contactez <a href="mailto:hemilaperceeinformation@gmail.com">hemilaperceeinformation@gmail.com</a> ou le <a href="tel:+242069149242">+242 06 914 92 42</a>. Elles ne remplacent pas le règlement des études ni les documents d’admission propres à l’établissement.</p></section>`
    }
  };

  const isInstalled = () => window.matchMedia?.("(display-mode: standalone)").matches
    || window.navigator.standalone === true;

  function readConsent() {
    const match = document.cookie.match(new RegExp(`(?:^|;\\s*)${CONSENT_COOKIE}=([^;]+)`));
    return match ? decodeURIComponent(match[1]) : null;
  }

  function saveConsent(choice) {
    const path = new URL("./", window.location.href).pathname;
    const secure = window.location.protocol === "https:" ? "; Secure" : "";
    document.cookie = `${CONSENT_COOKIE}=${encodeURIComponent(choice)}; Path=${path}; Max-Age=${CONSENT_MAX_AGE}; SameSite=Lax${secure}`;
  }

  function createLegalDialog() {
    const dialog = document.createElement("dialog");
    dialog.className = "hemip-legal-dialog";
    dialog.setAttribute("aria-labelledby", "hemip-legal-title");
    dialog.innerHTML = `<div class="hemip-legal-dialog__inner"><button type="button" class="hemip-legal-dialog__close" data-legal-close aria-label="Fermer">×</button><p class="hemip-cookie-banner__eyebrow">HEMIP · Informations pratiques</p><h2 id="hemip-legal-title"></h2><div class="hemip-legal-dialog__body" data-legal-body></div><div class="hemip-legal-dialog__actions"><button type="button" class="hemip-cookie-button hemip-cookie-button--accept" data-cookie-preferences>Gérer les cookies</button><button type="button" class="hemip-cookie-button hemip-cookie-button--reject" data-legal-close>Fermer</button></div></div>`;
    document.body.appendChild(dialog);
    dialog.querySelectorAll("[data-legal-close]").forEach(button => button.addEventListener("click", () => dialog.close()));
    dialog.addEventListener("click", event => { if (event.target === dialog) dialog.close(); });
    dialog.addEventListener("close", () => { if (previousFocus?.isConnected) previousFocus.focus(); });
    return dialog;
  }

  function openLegalDialog(key, trigger) {
    const policy = legalPolicies[key];
    if (!policy || !legalDialog) return;
    previousFocus = trigger || document.activeElement;
    legalDialog.querySelector("#hemip-legal-title").textContent = policy.title;
    legalDialog.querySelector("[data-legal-body]").innerHTML = policy.html;
    if (typeof legalDialog.showModal === "function") legalDialog.showModal();
    else legalDialog.setAttribute("open", "");
    legalDialog.querySelector("[data-legal-close]").focus();
  }

  function createCookieBanner() {
    const banner = document.createElement("aside");
    banner.className = "hemip-cookie-banner";
    banner.hidden = true;
    banner.setAttribute("aria-labelledby", "hemip-cookie-title");
    banner.setAttribute("aria-describedby", "hemip-cookie-description");
    banner.innerHTML = `<div class="hemip-cookie-banner__inner"><div class="hemip-cookie-banner__copy"><p class="hemip-cookie-banner__eyebrow">Vos choix de confidentialité</p><h2 id="hemip-cookie-title">Cookies et contenus intégrés</h2><p id="hemip-cookie-description">Les cookies nécessaires au fonctionnement du site (dont la session des formulaires) restent actifs. « Tout accepter » affiche automatiquement les vidéos et la carte Google dans la page. « Tout refuser » bloque ces contenus externes. Votre choix est conservé pendant 6 mois et peut être modifié à tout moment.</p><p class="hemip-cookie-banner__feedback" data-cookie-feedback hidden></p></div><div class="hemip-cookie-banner__actions"><button type="button" class="hemip-cookie-button hemip-cookie-button--accept" data-cookies-accept>Tout accepter</button><button type="button" class="hemip-cookie-button hemip-cookie-button--reject" data-cookies-reject>Tout refuser</button></div></div>`;
    document.body.appendChild(banner);
    cookieBanner = banner;
    cookieFeedback = banner.querySelector("[data-cookie-feedback]");

    banner.querySelector("[data-cookies-accept]").addEventListener("click", () => {
      saveConsent("accepted");
      pendingMediaTrigger = null;
      hideCookieBanner();
      loadAllOptionalEmbeds();
    });
    banner.querySelector("[data-cookies-reject]").addEventListener("click", () => {
      saveConsent("rejected");
      pendingMediaTrigger = null;
      removeOptionalEmbeds();
      hideCookieBanner();
    });

    const savedChoice = readConsent();
    if (!savedChoice) showCookieBanner();
    else if (savedChoice === "accepted") loadAllOptionalEmbeds();
  }

  function showCookieBanner(message = "", focusAccept = false) {
    if (!cookieBanner) return;
    cookieBanner.hidden = false;
    cookieBanner.setAttribute("aria-hidden", "false");
    if (cookieFeedback) {
      cookieFeedback.textContent = message;
      cookieFeedback.hidden = !message;
    }
    if (focusAccept) cookieBanner.querySelector("[data-cookies-accept]")?.focus();
  }

  function hideCookieBanner() {
    if (!cookieBanner) return;
    cookieBanner.hidden = true;
    cookieBanner.setAttribute("aria-hidden", "true");
    cookieFeedback?.setAttribute("hidden", "");
  }

  function makeEmbedTrigger(type, id, title) {
    const button = document.createElement("button");
    button.type = "button";
    button.className = `consent-embed-trigger${type === "youtube" ? " consent-embed-trigger--video" : " consent-embed-trigger--map"}`;
    button.dataset[type === "youtube" ? "youtubeId" : "mapSrc"] = id;
    button.dataset.embedTitle = title;
    button.setAttribute("aria-label", `${title} — afficher le lecteur dans cette page`);
    button.innerHTML = '<span class="consent-embed-play" aria-hidden="true">▶</span><span class="consent-embed-label">Afficher dans cette page</span><span class="consent-embed-note">Contenu externe chargé uniquement après votre choix</span>';
    button.addEventListener("click", () => requestOptionalEmbed(button));
    return button;
  }

  function requestOptionalEmbed(trigger) {
    if (readConsent() === "accepted") {
      loadOptionalEmbed(trigger);
      return;
    }
    pendingMediaTrigger = trigger;
    showCookieBanner("Acceptez une seule fois pour afficher automatiquement les vidéos et la carte intégrées. La page restera sur le site HEMIP.", true);
  }

  function loadAllOptionalEmbeds() {
    document.querySelectorAll("[data-youtube-id], [data-map-src]").forEach(trigger => loadOptionalEmbed(trigger));
  }

  function loadOptionalEmbed(trigger) {
    if (!trigger?.isConnected) return;
    const isYoutube = Boolean(trigger.dataset.youtubeId);
    const rawSource = isYoutube ? trigger.dataset.youtubeId : trigger.dataset.mapSrc;
    const cardTitle = trigger.closest(".achievement-video-card")?.querySelector(".achievement-video-caption h3")?.textContent.trim()
      || trigger.closest(".home-video-card")?.querySelector(".home-video-caption h4")?.textContent.trim();
    const title = (trigger.dataset.embedTitle && trigger.dataset.embedTitle !== "Vidéo HEMIP" ? trigger.dataset.embedTitle : cardTitle)
      || (isYoutube ? "Vidéo HEMIP" : "Carte HEMIP");
    if (!rawSource) return;

    let src;
    if (isYoutube) {
      if (!/^[A-Za-z0-9_-]{11}$/.test(rawSource)) return;
      src = `https://www.youtube-nocookie.com/embed/${rawSource}?rel=0&playsinline=1&hl=fr`;
    } else {
      try {
        const parsed = new URL(rawSource, window.location.href);
        if (parsed.protocol !== "https:" || !["www.google.com", "maps.google.com"].includes(parsed.hostname)) return;
        src = parsed.href;
      } catch {
        return;
      }
    }

    const frame = document.createElement("iframe");
    frame.className = "consent-embed-frame";
    frame.title = title;
    frame.src = src;
    frame.loading = "lazy";
    frame.referrerPolicy = "strict-origin-when-cross-origin";
    frame.setAttribute("sandbox", "allow-scripts allow-same-origin allow-presentation");
    frame.setAttribute("allow", "accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; fullscreen");
    frame.allowFullscreen = true;
    frame.dataset.hemipOptionalEmbed = isYoutube ? "youtube" : "map";
    frame.dataset.hemipEmbedId = rawSource;
    frame.dataset.hemipEmbedTitle = title;
    trigger.replaceWith(frame);
  }

  function removeOptionalEmbeds() {
    document.querySelectorAll("iframe[data-hemip-optional-embed]").forEach(frame => {
      const type = frame.dataset.hemipOptionalEmbed;
      const id = frame.dataset.hemipEmbedId;
      const title = frame.dataset.hemipEmbedTitle || "Contenu intégré HEMIP";
      if (id) frame.replaceWith(makeEmbedTrigger(type === "youtube" ? "youtube" : "map", id, title));
    });
  }

  function addLegalAndCookieLinks() {
    const footer = document.querySelector("footer");
    const footerBottom = footer?.querySelector(".footer-bottom");
    const legalHost = footerBottom || footer;
    if (legalHost && !legalHost.querySelector(".hemip-footer-legal")) {
      if (footerBottom) footerBottom.classList.add("has-hemip-footer-legal");
      const nav = document.createElement("nav");
      nav.className = "hemip-footer-legal";
      nav.setAttribute("aria-label", "Informations légales et confidentialité");
      nav.innerHTML = '<a href="#politique-confidentialite" data-legal-open="privacy">Politique de confidentialité</a><a href="#politique-cookies" data-legal-open="cookies">Cookies</a><a href="#conditions-utilisation" data-legal-open="terms">Politique d’utilisation</a><button type="button" data-cookie-preferences>Gérer les cookies</button>';
      legalHost.appendChild(nav);
    }

    document.addEventListener("click", event => {
      const legalLink = event.target.closest("[data-legal-open]");
      if (legalLink) {
        event.preventDefault();
        openLegalDialog(legalLink.dataset.legalOpen, legalLink);
        return;
      }
      const preferences = event.target.closest("[data-cookie-preferences]");
      if (preferences) {
        if (legalDialog?.open) legalDialog.close();
        showCookieBanner("Choisissez si les contenus intégrés YouTube et Google peuvent être chargés. Vous pourrez modifier votre choix plus tard.", true);
      }
    });
  }

  function createInstallDialog() {
    const dialog = document.createElement("dialog");
    dialog.className = "pwa-install-dialog";
    dialog.setAttribute("aria-labelledby", "pwa-install-title");
    dialog.innerHTML = `<div class="pwa-dialog__content"><button class="pwa-dialog__close" type="button" data-pwa-close aria-label="Fermer">×</button><p class="pwa-dialog__eyebrow">HEMIP · La Percée</p><h2 id="pwa-install-title">Installer HEMIP</h2><p class="pwa-dialog__copy" data-pwa-instructions></p><p class="pwa-dialog__note">L’installation est facultative. Les pages et ressources publiques déjà consultées peuvent rester accessibles hors connexion ; les formulaires et vidéos nécessitent Internet.</p><div class="pwa-dialog__actions"><button class="pwa-dialog__install" type="button" data-pwa-install-confirm>Installer maintenant</button><button class="pwa-dialog__later" type="button" data-pwa-install-later>Plus tard</button></div></div>`;
    document.body.appendChild(dialog);

    const close = (dismiss = true) => {
      if (typeof dialog.close === "function" && dialog.open) dialog.close();
      else dialog.removeAttribute("open");
      if (dismiss) rememberInstallDismissal();
      if (previousFocus?.isConnected) previousFocus.focus();
    };
    dialog.querySelector("[data-pwa-close]").addEventListener("click", () => close(true));
    dialog.querySelector("[data-pwa-install-later]").addEventListener("click", () => close(true));
    dialog.addEventListener("click", event => { if (event.target === dialog) close(true); });
    dialog.addEventListener("cancel", event => { event.preventDefault(); close(true); });
    dialog.querySelector("[data-pwa-install-confirm]").addEventListener("click", () => confirmInstall());
    return dialog;
  }

  function isInstallPromptDismissed() {
    try {
      return Number(localStorage.getItem(INSTALL_DISMISS_KEY) || 0) > Date.now();
    } catch {
      return false;
    }
  }

  function rememberInstallDismissal() {
    try {
      localStorage.setItem(INSTALL_DISMISS_KEY, String(Date.now() + INSTALL_DISMISS_MS));
    } catch {
      // Une préférence indisponible peut seulement faire réapparaître l’invitation.
    }
  }

  function openInstallDialog() {
    if (!installDialog || installDialog.open || isInstalled()) return;
    const isAppleTouch = /iphone|ipad|ipod/i.test(navigator.userAgent)
      || (navigator.platform === "MacIntel" && navigator.maxTouchPoints > 1);
    const instructions = installDialog.querySelector("[data-pwa-instructions]");
    const confirm = installDialog.querySelector("[data-pwa-install-confirm]");
    const later = installDialog.querySelector("[data-pwa-install-later]");
    confirm.hidden = false;
    confirm.textContent = installPrompt ? "Continuer l’installation" : "Voir les étapes";
    later.textContent = "Plus tard";
    instructions.textContent = installPrompt
      ? "Ajoutez l’application HEMIP à votre appareil pour la retrouver plus facilement. Votre navigateur vous demandera de confirmer l’installation."
      : isAppleTouch
        ? "Ajoutez HEMIP à l’écran d’accueil de votre appareil pour y accéder comme une application."
        : "Vous pouvez installer HEMIP depuis le menu de votre navigateur. L’installation est facultative.";
    previousFocus = document.activeElement;
    if (typeof installDialog.showModal === "function") installDialog.showModal();
    else installDialog.setAttribute("open", "");
    later.focus();
  }

  function showInstallHelp() {
    const isAppleTouch = /iphone|ipad|ipod/i.test(navigator.userAgent)
      || (navigator.platform === "MacIntel" && navigator.maxTouchPoints > 1);
    installDialog.querySelector("[data-pwa-instructions]").textContent = isAppleTouch
      ? "Dans Safari, touchez le bouton Partager, puis choisissez « Sur l’écran d’accueil » pour ajouter HEMIP."
      : "Dans le menu de votre navigateur, choisissez « Installer l’application » ou « Ajouter à l’écran d’accueil ». Le libellé varie selon le navigateur.";
    installDialog.querySelector("[data-pwa-install-confirm]").hidden = true;
    installDialog.querySelector("[data-pwa-install-later]").textContent = "J’ai compris";
    installDialog.querySelector("[data-pwa-install-later]").focus();
  }

  async function confirmInstall() {
    if (!installPrompt) {
      showInstallHelp();
      return;
    }
    const button = installDialog.querySelector("[data-pwa-install-confirm]");
    button.disabled = true;
    try {
      const prompt = installPrompt;
      installPrompt = null;
      await prompt.prompt();
      const choice = await prompt.userChoice;
      if (choice?.outcome === "accepted") {
        rememberInstallDismissal();
        installDialog.close?.();
        const item = installButton?.closest(".pwa-install-item");
        if (item) item.hidden = true;
      }
    } catch (error) {
      console.warn("Invite d’installation HEMIP indisponible.", error);
      showInstallHelp();
    } finally {
      button.disabled = false;
    }
  }

  function addInstallButton() {
    const navList = document.querySelector("#nav-list");
    if (!navList || navList.querySelector(".pwa-install-item")) return;
    const item = document.createElement("li");
    item.className = "pwa-install-item";
    installButton = document.createElement("button");
    installButton.className = "pwa-install-button";
    installButton.type = "button";
    installButton.setAttribute("aria-label", "Installer HEMIP comme application");
    installButton.innerHTML = '<span aria-hidden="true">↓</span><span>Installer</span>';
    item.appendChild(installButton);
    navList.appendChild(item);
    installDialog = createInstallDialog();
    if (isInstalled()) item.hidden = true;
    installButton.addEventListener("click", openInstallDialog);
  }

  function maybeShowTimedInstallPrompt() {
    if (isInstalled() || isInstallPromptDismissed() || !installDialog) return;
    if (cookieBanner && !cookieBanner.hidden) {
      window.setTimeout(maybeShowTimedInstallPrompt, 5000);
      return;
    }
    openInstallDialog();
  }

  function addNetworkStatus() {
    const status = document.createElement("div");
    status.className = "pwa-network-status";
    status.setAttribute("role", "status");
    status.setAttribute("aria-live", "polite");
    status.hidden = true;
    document.body.appendChild(status);
    let onlineTimer;
    const update = () => {
      window.clearTimeout(onlineTimer);
      if (!navigator.onLine) {
        wasOffline = true;
        status.textContent = "Hors ligne : les formulaires et envois de documents nécessitent une connexion.";
        status.hidden = false;
      } else if (wasOffline) {
        wasOffline = false;
        status.textContent = "Connexion rétablie. Vous pouvez reprendre votre navigation.";
        status.hidden = false;
        onlineTimer = window.setTimeout(() => { status.hidden = true; }, 6000);
      } else {
        status.hidden = true;
      }
    };
    window.addEventListener("online", update);
    window.addEventListener("offline", update);
    update();
  }

  function registerServiceWorker() {
    const isSecure = window.isSecureContext || location.hostname === "localhost" || location.hostname === "127.0.0.1";
    if (!isSecure || !("serviceWorker" in navigator)) return;
    const workerUrl = new URL("./sw.js", location.href);
    const scope = new URL("./", location.href).pathname;
    navigator.serviceWorker.register(workerUrl.href, { scope })
      .catch(error => console.warn("Enregistrement du service worker HEMIP impossible.", error));
  }

  window.addEventListener("beforeinstallprompt", event => {
    event.preventDefault();
    installPrompt = event;
  });
  window.addEventListener("appinstalled", () => {
    installPrompt = null;
    const item = document.querySelector(".pwa-install-item");
    if (item) item.hidden = true;
  });

  document.querySelectorAll("[data-youtube-id], [data-map-src]").forEach(trigger => {
    if (trigger.tagName === "BUTTON") trigger.addEventListener("click", () => requestOptionalEmbed(trigger));
  });

  legalDialog = createLegalDialog();
  addLegalAndCookieLinks();
  createCookieBanner();
  addInstallButton();
  addNetworkStatus();
  registerServiceWorker();
  window.setTimeout(maybeShowTimedInstallPrompt, INSTALL_PROMPT_DELAY_MS);
})();
