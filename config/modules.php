<?php
require_once __DIR__ . '/includes/pedagogie.php';
hemip_start_session();
require_once __DIR__ . '/connexion.php';
pedagogie_exiger_sac($connexion);
hemip_private_headers();

$filieres = pedagogie_filieres($connexion);
$flash = $_SESSION['flash_pedagogie'] ?? null;
unset($_SESSION['flash_pedagogie']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = '';
    if (!hemip_csrf_valid($_POST['csrf'] ?? null)) {
        $message = 'Session expirée. Rechargez la page puis réessayez.';
    } else {
        $action = $_POST['action'] ?? '';
        if ($action === 'ajouter') {
            $idFiliere = filter_var($_POST['id_filiere'] ?? null, FILTER_VALIDATE_INT) ?: 0;
            $niveauSaisi = $_POST['niveau'] ?? '';
            $niveau = is_string($niveauSaisi) ? pedagogie_normaliser_niveau($niveauSaisi) : null;
            $nomSaisi = $_POST['nom_module'] ?? '';
            $nom = is_string($nomSaisi) ? trim($nomSaisi) : '';
            $descriptionSaisie = $_POST['description'] ?? '';
            $description = is_string($descriptionSaisie) ? trim($descriptionSaisie) : '';

            if ($idFiliere < 1 || $niveau === null || mb_strlen($nom) < 2 || mb_strlen($nom) > 150 || mb_strlen($description) > 5000) {
                $message = 'Vérifiez la filière, le niveau et les longueurs des champs.';
            } else {
                try {
                    $insert = $connexion->prepare(
                        'INSERT INTO module_pedagogique (id_filiere, niveau, nom_module, description, actif)
                         VALUES (?, ?, ?, ?, 1)
                         ON DUPLICATE KEY UPDATE description = VALUES(description), actif = 1'
                    );
                    $insert->execute([$idFiliere, $niveau, $nom, $description !== '' ? $description : null]);
                    $message = 'Module ajouté ou réactivé.';
                } catch (PDOException $exception) {
                    $message = 'Le module n’a pas pu être enregistré. Vérifiez la filière sélectionnée.';
                }
            }
        } elseif (in_array($action, ['archiver', 'reactiver'], true)) {
            $idModule = filter_var($_POST['id_module'] ?? null, FILTER_VALIDATE_INT) ?: 0;
            if ($idModule < 1) {
                $message = 'Module invalide.';
            } else {
                $actif = $action === 'reactiver' ? 1 : 0;
                $maj = $connexion->prepare('UPDATE module_pedagogique SET actif = ? WHERE id_module = ?');
                $maj->execute([$actif, $idModule]);
                $message = $maj->rowCount() ? ($actif ? 'Module réactivé.' : 'Module archivé.') : 'Aucun changement effectué.';
            }
        } else {
            $message = 'Action inconnue.';
        }
    }
    $_SESSION['flash_pedagogie'] = $message;
    header('Location: modules.php');
    exit;
}

$query = $connexion->query(
    'SELECT m.id_module, m.nom_module, m.description, m.niveau, m.actif,
            f.idFiliere, f.code_filiere, f.nomFiliere
     FROM module_pedagogique m
     INNER JOIN filiere f ON f.idFiliere = m.id_filiere
     ORDER BY f.nomFiliere, FIELD(m.niveau,"L1","L2","L3","Master 1","Master 2"), m.nom_module'
);
$modules = $query->fetchAll(PDO::FETCH_ASSOC);
$niveaux = PEDAGOGIE_NIVEAUX;
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Modules pédagogiques | HEMIP</title>
  <link rel="stylesheet" href="assets/css/hemip-theme.css">
  <link rel="stylesheet" href="assets/css/espace-pedagogique.css">
  <link rel="stylesheet" href="assets/vendor/remixicon/remixicon.css">
</head>
<body>
<header class="pedag-topbar">
  <a class="pedag-brand" href="sac.php"><img src="assets/img/logo-hemip.jpg" alt="">HEMIP · SAC</a>
  <nav class="pedag-nav" aria-label="Navigation SAC"><a href="sac.php">Accueil</a><a href="modules.php" aria-current="page">Modules</a><a href="affectations.php">Affectations</a><a href="emploi-du-temps.php">Planning</a></nav>
  <a href="deconnexion.php">Déconnexion</a>
</header>
<main class="pedag-page"><div class="pedag-wrap">
  <div class="pedag-head"><div><h1>Modules pédagogiques</h1><p>Référentiel maintenu par filière et niveau.</p></div></div>
  <?php if ($flash): ?><div class="pedag-alert" role="status"><?= pedagogie_e($flash) ?></div><?php endif; ?>
  <section class="pedag-panel">
    <h2>Ajouter ou réactiver un module</h2>
    <form class="pedag-form" method="post">
      <?= hemip_csrf_input() ?><input type="hidden" name="action" value="ajouter">
      <div class="pedag-field"><label for="id-filiere">Filière</label><select id="id-filiere" name="id_filiere" required><option value="">Choisir</option><?php foreach ($filieres as $filiere): ?><option value="<?= (int) $filiere['idFiliere'] ?>"><?= pedagogie_e($filiere['code_filiere'] . ' — ' . $filiere['nomFiliere']) ?></option><?php endforeach; ?></select></div>
      <div class="pedag-field"><label for="niveau">Niveau</label><select id="niveau" name="niveau" required><option value="">Choisir</option><?php foreach ($niveaux as $niveau): ?><option value="<?= pedagogie_e($niveau) ?>"><?= pedagogie_e($niveau) ?></option><?php endforeach; ?></select></div>
      <div class="pedag-field"><label for="nom-module">Nom du module</label><input id="nom-module" name="nom_module" type="text" maxlength="150" required></div>
      <div class="pedag-field"><label for="description">Description (facultative)</label><input id="description" name="description" type="text" maxlength="5000"></div>
      <button class="pedag-button" type="submit">Enregistrer</button>
    </form>
  </section>
  <section class="pedag-panel" style="margin-top:18px">
    <h2>Modules enregistrés (<?= count($modules) ?>)</h2>
    <?php if (!$modules): ?><p class="pedag-muted">Aucun module pour le moment.</p><?php else: ?>
    <div class="pedag-table-wrap"><table class="pedag-table"><thead><tr><th>Filière</th><th>Niveau</th><th>Module</th><th>État</th><th>Action</th></tr></thead><tbody>
      <?php foreach ($modules as $module): ?><tr>
        <td><?= pedagogie_e($module['code_filiere'] . ' — ' . $module['nomFiliere']) ?></td><td><?= pedagogie_e($module['niveau']) ?></td>
        <td><strong><?= pedagogie_e($module['nom_module']) ?></strong><?php if ($module['description']): ?><br><span class="pedag-muted"><?= pedagogie_e($module['description']) ?></span><?php endif; ?></td>
        <td><span class="pedag-tag"><?= (int) $module['actif'] === 1 ? 'Actif' : 'Archivé' ?></span></td>
        <td><form method="post"><?= hemip_csrf_input() ?><input type="hidden" name="id_module" value="<?= (int) $module['id_module'] ?>"><input type="hidden" name="action" value="<?= (int) $module['actif'] === 1 ? 'archiver' : 'reactiver' ?>"><button class="pedag-button <?= (int) $module['actif'] === 1 ? 'pedag-button--muted' : '' ?>" type="submit"><?= (int) $module['actif'] === 1 ? 'Archiver' : 'Réactiver' ?></button></form></td>
      </tr><?php endforeach; ?>
    </tbody></table></div><?php endif; ?>
  </section>
</div></main>
</body>
</html>
