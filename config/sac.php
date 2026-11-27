<?php
require_once __DIR__ . '/includes/pedagogie.php';
hemip_start_session();
require_once __DIR__ . '/connexion.php';
pedagogie_exiger_sac($connexion);
hemip_private_headers();

$modules = (int) $connexion->query('SELECT COUNT(*) FROM module_pedagogique WHERE actif = 1')->fetchColumn();
$enseignants = (int) $connexion->query('SELECT COUNT(*) FROM enseignant WHERE actif = 1')->fetchColumn();
$classes = (int) $connexion->query('SELECT COUNT(*) FROM classe_pedagogique')->fetchColumn();
$affectations = (int) $connexion->query('SELECT COUNT(*) FROM affectation_enseignant WHERE active = 1')->fetchColumn();
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Espace SAC | HEMIP</title>
  <link rel="stylesheet" href="assets/css/hemip-theme.css">
  <link rel="stylesheet" href="assets/css/espace-pedagogique.css">
  <link rel="stylesheet" href="assets/vendor/remixicon/remixicon.css">
</head>
<body>
<header class="pedag-topbar">
  <a class="pedag-brand" href="sac.php"><img src="assets/img/logo-hemip.jpg" alt="">HEMIP · SAC</a>
  <nav class="pedag-nav" aria-label="Navigation SAC">
    <a href="sac.php" aria-current="page">Accueil</a>
    <a href="modules.php">Modules</a>
    <a href="affectations.php">Affectations</a>
    <a href="emploi-du-temps.php">Planning</a>
  </nav>
  <a href="deconnexion.php">Déconnexion</a>
</header>
<main class="pedag-page"><div class="pedag-wrap">
  <div class="pedag-head">
    <div><h1>Espace SAC</h1><p>Gestion de la scolarité pédagogique, des classes et des affectations.</p></div>
    <span class="pedag-tag">Connecté · <?= pedagogie_e($_SESSION['prenom'] ?? '') ?> <?= pedagogie_e($_SESSION['nom'] ?? '') ?></span>
  </div>
  <section class="pedag-grid" aria-label="Indicateurs pédagogiques">
    <article class="pedag-card"><h2>Modules actifs</h2><strong class="pedag-kpi"><?= $modules ?></strong><p>Par filière et niveau.</p></article>
    <article class="pedag-card"><h2>Enseignants actifs</h2><strong class="pedag-kpi"><?= $enseignants ?></strong><p>Comptes créés par le SuperAdmin.</p></article>
    <article class="pedag-card"><h2>Classes annuelles</h2><strong class="pedag-kpi"><?= $classes ?></strong><p>Une classe par filière, niveau et année.</p></article>
    <article class="pedag-card"><h2>Affectations actives</h2><strong class="pedag-kpi"><?= $affectations ?></strong><p>Enseignant, classe et module.</p></article>
  </section>
  <section class="pedag-panel" style="margin-top:18px">
    <h2>Actions</h2>
    <p class="pedag-muted">Les classes sont créées au moment de leur première affectation ; les étudiants apparaissent uniquement si leur inscription est validée pour la même filière, le même niveau et la même année.</p>
    <div class="pedag-links">
      <a class="pedag-button" href="modules.php"><i class="ri-book-2-line"></i> Gérer les modules</a>
      <a class="pedag-button" href="affectations.php"><i class="ri-user-shared-line"></i> Affecter un enseignant</a>
      <a class="pedag-button pedag-button--muted" href="emploi-du-temps.php"><i class="ri-calendar-schedule-line"></i> Gérer le planning</a>
    </div>
  </section>
</div></main>
</body>
</html>
