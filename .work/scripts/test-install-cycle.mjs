export default async function run(page, ui) {
  const base = 'http://127.0.0.1:8123/';
  const ctx = page.context();

  const lire = (p) => p.evaluate(() => {
    const item = document.querySelector('.pwa-install-item');
    const btn = document.querySelector('.pwa-install-button');
    return {
      boutonVisible: item ? !item.hidden : null,
      libelle: btn ? btn.textContent.trim() : null
    };
  });

  const scriptStandalone = (actif) => `(() => {
    const orig = window.matchMedia.bind(window);
    window.matchMedia = (q) => /display-mode:\\s*standalone/.test(q)
      ? { matches: ${actif}, media: q, addEventListener(){}, removeEventListener(){}, addListener(){}, removeListener(){} }
      : orig(q);
  })();`;

  const ouvrir = async (initScript) => {
    const p = await ctx.newPage();
    await p.setViewportSize({ width: 1280, height: 900 });
    if (initScript) await p.addInitScript({ content: initScript });
    await p.goto(base + 'index.php', { waitUntil: 'domcontentloaded' });
    await p.waitForTimeout(900);
    return p;
  };

  const p1 = await ouvrir(null);
  const etatNormal = await lire(p1);

  let dialogue = null;
  if (etatNormal.boutonVisible) {
    await p1.click('.pwa-install-button').catch(() => {});
    await p1.waitForTimeout(500);
    dialogue = await p1.evaluate(() => {
      const d = document.querySelector('.pwa-install-dialog');
      return {
        ouvert: d ? d.open === true : false,
        instructions: d ? d.querySelector('[data-pwa-instructions]').textContent.trim().slice(0, 70) : null,
        boutonSecours: d ? d.querySelector('[data-pwa-install-later]').textContent.trim() : null
      };
    });
  }
  await p1.close();

  const p2 = await ouvrir(scriptStandalone(true));
  const etatInstalle = await lire(p2);
  await p2.close();

  const p3 = await ouvrir(scriptStandalone(false));
  const apresDesinstall = await lire(p3);

  let reinstallOk = null;
  if (apresDesinstall.boutonVisible) {
    await p3.click('.pwa-install-button').catch(() => {});
    await p3.waitForTimeout(500);
    reinstallOk = await p3.evaluate(() => {
      const d = document.querySelector('.pwa-install-dialog');
      return d ? d.open === true : false;
    });
  }
  await p3.close();

  return { etatNormal, dialogue, etatInstalle, apresDesinstall, reinstallOk };
}
