(() => {
  "use strict";

  let installPrompt = null;
  let installButton = null;
  let installDialog = null;
  let previousFocus = null;
  let wasOffline = !navigator.onLine;

  const isInstalled = () => window.matchMedia?.("(display-mode: standalone)").matches
    || window.navigator.standalone === true;

  function createInstallDialog() {
    const dialog = document.createElement("dialog");
    dialog.className = "pwa-install-dialog";
    dialog.setAttribute("aria-labelledby", "pwa-install-title");
    dialog.innerHTML = `
      <div class="pwa-dialog__content">
        <button class="pwa-dialog__close" type="button" data-pwa-close aria-label="Fermer">×</button>
        <p class="pwa-dialog__eyebrow">HEMIP · La Percée</p>
        <h2 id="pwa-install-title">Installer HEMIP</h2>
        <p class="pwa-dialog__copy" data-pwa-instructions></p>
        <p class="pwa-dialog__note">L’application conserve uniquement des pages publiques et des ressources statiques déjà consultées. Les inscriptions ne sont pas enregistrées hors ligne.</p>
      </div>`;
    document.body.appendChild(dialog);

    const close = () => {
      if (typeof dialog.close === "function" && dialog.open) dialog.close();
      else dialog.removeAttribute("open");
      if (previousFocus?.isConnected) previousFocus.focus();
    };
    dialog.querySelector("[data-pwa-close]").addEventListener("click", close);
    dialog.addEventListener("click", event => {
      if (event.target === dialog) close();
    });
    dialog.addEventListener("cancel", event => {
      event.preventDefault();
      close();
    });
    return dialog;
  }

  function showInstallHelp() {
    const isAppleTouch = /iphone|ipad|ipod/i.test(navigator.userAgent)
      || (navigator.platform === "MacIntel" && navigator.maxTouchPoints > 1);
    const instructions = installDialog.querySelector("[data-pwa-instructions]");
    instructions.textContent = isAppleTouch
      ? "Dans Safari, touchez le bouton Partager, puis choisissez « Sur l’écran d’accueil » pour ajouter HEMIP à votre appareil."
      : "Ouvrez le menu de votre navigateur, puis choisissez « Installer l’application » ou « Ajouter à l’écran d’accueil ». Le libellé varie selon le navigateur.";

    previousFocus = installButton;
    if (typeof installDialog.showModal === "function") installDialog.showModal();
    else installDialog.setAttribute("open", "");
    installDialog.querySelector("[data-pwa-close]").focus();
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

    installButton.addEventListener("click", async () => {
      if (!installPrompt) {
        showInstallHelp();
        return;
      }

      installButton.disabled = true;
      try {
        const prompt = installPrompt;
        installPrompt = null;
        await prompt.prompt();
        const choice = await prompt.userChoice;
        if (choice?.outcome === "accepted") item.hidden = true;
      } catch (error) {
        console.warn("Invite d’installation HEMIP indisponible.", error);
        showInstallHelp();
      } finally {
        installButton.disabled = false;
      }
    });
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

  addInstallButton();
  addNetworkStatus();
  registerServiceWorker();
})();
