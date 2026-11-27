<?php
require_once __DIR__ . '/includes/role-guard.php';
hemip_start_session();
require_once __DIR__ . '/connexion.php';
hemip_require_role($connexion, 'DAAC');
hemip_private_headers();
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Espace DAAC | HEMIP</title>
  <link rel="stylesheet" href="assets/css/hemip-theme.css">
  <link rel="stylesheet" href="assets/css/espace-pedagogique.css">
</head>
<body>
<header class="pedag-topbar">
  <a class="pedag-brand" href="daac.php"><img src="assets/img/logo-hemip.jpg" alt="">HEMIP · DAAC</a>
  <a href="deconnexion.php">Déconnexion</a>
</header>
<main class="pedag-page"><div class="pedag-wrap">
  <div class="pedag-head"><div><h1>Espace DAAC</h1><p>Accès réservé au rôle DAAC.</p></div></div>
  <section class="pedag-panel" role="status">
    <h2>Fonctionnalités en préparation</h2>
    <p class="pedag-muted">La gestion et la modification des notes sont volontairement exclues de cette première version. Aucune action DAAC sur les notes n’est disponible ici.</p>
    <div class="pedag-links"><a class="pedag-button pedag-button--muted" href="index.php">Retour à l’accueil</a></div>
  </section>
</div></main>
</body>
</html>
