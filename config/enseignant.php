<?php
require_once __DIR__ . '/includes/pedagogie.php';
hemip_start_session();
require_once __DIR__ . '/connexion.php';
$enseignant = pedagogie_exiger_enseignant($connexion);
hemip_private_headers();

$affectations = pedagogie_affectations_enseignant($connexion, (int) $enseignant['id_enseignant']);
$classes = [];
foreach ($affectations as $affectation) {
    $idClasse = (int) $affectation['id_classe'];
    if (!isset($classes[$idClasse])) {
        $classes[$idClasse] = [
            'id_classe' => $idClasse,
            'id_filiere' => (int) $affectation['id_filiere'],
            'niveau' => $affectation['niveau'],
            'annee_academique' => $affectation['annee_academique'],
            'code_filiere' => $affectation['code_filiere'],
            'nomFiliere' => $affectation['nomFiliere'],
            'modules' => [],
        ];
    }
    $classes[$idClasse]['modules'][(int) $affectation['id_module']] = $affectation['nom_module'];
}
foreach ($classes as &$classe) {
    $classe['etudiants'] = pedagogie_liste_etudiants($connexion, $classe['id_classe']);
}
unset($classe);

$idClasseDemandee = filter_var($_GET['classe'] ?? null, FILTER_VALIDATE_INT) ?: 0;
$classeSelectionnee = null;
if ($idClasseDemandee > 0) {
    if (!pedagogie_enseignant_autorise($connexion, (int) $enseignant['id_enseignant'], $idClasseDemandee)) {
        http_response_code(403);
        exit('Accès refusé à cette classe.');
    }
    $classeSelectionnee = $classes[$idClasseDemandee] ?? null;
    if ($classeSelectionnee === null) {
        http_response_code(403);
        exit('Cette classe ne possède plus d’affectation active.');
    }
}

$initiales = mb_strtoupper(mb_substr((string) $enseignant['prenom'], 0, 1) . mb_substr((string) $enseignant['nom'], 0, 1));
$photo = null;
$photoCandidate = $enseignant['photo'] ?? '';
if (is_string($photoCandidate) && preg_match('~^uploads/enseignants/[A-Za-z0-9_.-]+\.(?:png|jpe?g|webp)$~i', $photoCandidate) && is_file(__DIR__ . '/' . $photoCandidate)) {
    $photo = $photoCandidate;
}
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Espace enseignant | HEMIP</title>
  <link rel="stylesheet" href="assets/css/hemip-theme.css"><link rel="stylesheet" href="assets/css/espace-pedagogique.css"><link rel="stylesheet" href="assets/vendor/remixicon/remixicon.css">
</head>
<body>
<header class="pedag-topbar">
  <a class="pedag-brand" href="enseignant.php"><img src="assets/img/logo-hemip.jpg" alt="">HEMIP · Enseignant</a>
  <nav class="pedag-nav" aria-label="Espace enseignant"><a href="enseignant.php" aria-current="page">Mes classes</a><a href="emploi-du-temps.php">Planning</a><a href="notifications.php">Notifications</a></nav>
  <a href="deconnexion.php">Déconnexion</a>
</header>
<main class="pedag-page"><div class="pedag-wrap">
  <section class="pedag-panel" style="margin-bottom:18px;display:flex;align-items:center;gap:16px;flex-wrap:wrap">
    <?php if ($photo): ?><img src="<?= pedagogie_e($photo) ?>" alt="Photo de <?= pedagogie_e($enseignant['prenom'] . ' ' . $enseignant['nom']) ?>" style="width:72px;height:72px;border-radius:50%;object-fit:cover"><?php else: ?><span aria-hidden="true" style="width:72px;height:72px;border-radius:50%;display:grid;place-items:center;background:var(--hemip-primary-soft);color:var(--hemip-primary-strong);font-weight:750;font-size:1.25rem"><?= pedagogie_e($initiales) ?></span><?php endif; ?>
    <div><h1 style="margin:0"><?= pedagogie_e($enseignant['prenom'] . ' ' . $enseignant['nom']) ?></h1><p class="pedag-muted" style="margin:5px 0"><?= pedagogie_e($enseignant['email']) ?> · <?= pedagogie_e($enseignant['telephone'] ?? 'Téléphone non renseigné') ?></p><p class="pedag-muted" style="margin:0">Profil en lecture seule · <?= count($affectations) ?> affectation(s) active(s)</p></div>
  </section>

  <?php if ($classeSelectionnee): ?>
  <section class="pedag-panel" aria-labelledby="roster-title">
    <div class="pedag-head"><div><h1 id="roster-title"><?= pedagogie_e($classeSelectionnee['code_filiere'] . ' — ' . $classeSelectionnee['nomFiliere']) ?></h1><p><?= pedagogie_e($classeSelectionnee['niveau'] . ' · ' . $classeSelectionnee['annee_academique']) ?></p></div><a class="pedag-button pedag-button--muted" href="enseignant.php">Retour à mes classes</a></div>
    <h2>Modules enseignés</h2><p><?= pedagogie_e(implode(', ', array_values($classeSelectionnee['modules']))) ?></p>
    <h2>Étudiants inscrits et validés (<?= count($classeSelectionnee['etudiants']) ?>)</h2>
    <?php if (!$classeSelectionnee['etudiants']): ?><p class="pedag-muted">Aucun étudiant ne correspond à cette filière, ce niveau et cette année académique.</p><?php else: ?>
    <div class="pedag-table-wrap"><table class="pedag-table"><thead><tr><th>Matricule</th><th>Étudiant</th><th>E-mail</th><th>Téléphone</th></tr></thead><tbody><?php foreach ($classeSelectionnee['etudiants'] as $etudiant): ?><tr><td><?= pedagogie_e($etudiant['matricule_ancien']) ?></td><td><?= pedagogie_e($etudiant['prenom'] . ' ' . $etudiant['nom']) ?></td><td><?= pedagogie_e($etudiant['email']) ?></td><td><?= pedagogie_e($etudiant['telephone']) ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
    <div class="pedag-links"><a class="pedag-button" href="supports.php?classe=<?= (int) $classeSelectionnee['id_classe'] ?>">Supports</a><a class="pedag-button" href="presences.php?classe=<?= (int) $classeSelectionnee['id_classe'] ?>">Présences</a><a class="pedag-button" href="annonces.php?classe=<?= (int) $classeSelectionnee['id_classe'] ?>">Annonces</a></div>
  </section>
  <?php else: ?>
  <div class="pedag-head"><div><h1>Mes classes</h1><p>Les étudiants affichés sont limités aux inscriptions validées de la filière, du niveau et de l’année concernés.</p></div></div>
  <?php if (!$classes): ?><section class="pedag-panel"><p class="pedag-muted">Aucune affectation active pour le moment. Le SAC attribuera vos classes et modules.</p></section><?php else: ?>
  <section class="pedag-grid">
    <?php foreach ($classes as $classe): ?><article class="pedag-card">
      <span class="pedag-tag"><?= pedagogie_e($classe['niveau'] . ' · ' . $classe['annee_academique']) ?></span>
      <h2 style="margin-top:12px"><?= pedagogie_e($classe['code_filiere'] . ' — ' . $classe['nomFiliere']) ?></h2>
      <p><strong><?= count($classe['etudiants']) ?></strong> étudiant(s) inscrit(s) et validé(s)</p>
      <p style="margin-top:10px"><strong>Modules :</strong> <?= pedagogie_e(implode(', ', array_values($classe['modules']))) ?></p>
      <div class="pedag-links"><a class="pedag-button" href="enseignant.php?classe=<?= (int) $classe['id_classe'] ?>">Voir la classe</a><a class="pedag-button pedag-button--muted" href="emploi-du-temps.php?classe=<?= (int) $classe['id_classe'] ?>">Planning</a></div>
    </article><?php endforeach; ?>
  </section>
  <?php endif; ?>
  <?php endif; ?>
</div></main>
</body>
</html>
