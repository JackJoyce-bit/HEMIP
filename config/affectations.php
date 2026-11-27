<?php
require_once __DIR__ . '/includes/pedagogie.php';
hemip_start_session();
require_once __DIR__ . '/connexion.php';
pedagogie_exiger_sac($connexion);
hemip_private_headers();

$flash = $_SESSION['flash_pedagogie'] ?? null;
unset($_SESSION['flash_pedagogie']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = '';
    if (!hemip_csrf_valid($_POST['csrf'] ?? null)) {
        $message = 'Session expirée. Rechargez la page puis réessayez.';
    } else {
        $action = $_POST['action'] ?? '';
        if ($action === 'affecter') {
            $idEnseignant = filter_var($_POST['id_enseignant'] ?? null, FILTER_VALIDATE_INT) ?: 0;
            $idFiliere = filter_var($_POST['id_filiere'] ?? null, FILTER_VALIDATE_INT) ?: 0;
            $idModule = filter_var($_POST['id_module'] ?? null, FILTER_VALIDATE_INT) ?: 0;
            $niveauSaisi = $_POST['niveau'] ?? '';
            $niveau = is_string($niveauSaisi) ? pedagogie_normaliser_niveau($niveauSaisi) : null;
            $anneeSaisie = $_POST['annee_academique'] ?? '';
            $annee = is_string($anneeSaisie) ? trim($anneeSaisie) : '';

            if ($idEnseignant < 1 || $idFiliere < 1 || $idModule < 1 || $niveau === null || !pedagogie_valider_annee($annee)) {
                $message = 'Vérifiez les champs de l’affectation et l’année académique.';
            } else {
                $enseignant = $connexion->prepare('SELECT 1 FROM enseignant WHERE id_enseignant = ? AND actif = 1 LIMIT 1');
                $enseignant->execute([$idEnseignant]);
                $module = $connexion->prepare('SELECT 1 FROM module_pedagogique WHERE id_module = ? AND id_filiere = ? AND niveau = ? AND actif = 1 LIMIT 1');
                $module->execute([$idModule, $idFiliere, $niveau]);

                if (!$enseignant->fetchColumn()) {
                    $message = 'L’enseignant est introuvable ou désactivé.';
                } elseif (!$module->fetchColumn()) {
                    $message = 'Le module doit être actif et correspondre exactement à la filière et au niveau choisis.';
                } else {
                    try {
                        $connexion->beginTransaction();
                        $idClasse = pedagogie_ensure_class($connexion, $idFiliere, $niveau, $annee);
                        $insert = $connexion->prepare(
                            'INSERT INTO affectation_enseignant (id_enseignant, id_classe, id_module, id_filiere, niveau, active)
                             VALUES (?, ?, ?, ?, ?, 1)
                             ON DUPLICATE KEY UPDATE active = 1'
                        );
                        $insert->execute([$idEnseignant, $idClasse, $idModule, $idFiliere, $niveau]);
                        $connexion->commit();
                        $message = 'Affectation enregistrée.';
                    } catch (Throwable $exception) {
                        if ($connexion->inTransaction()) {
                            $connexion->rollBack();
                        }
                        $message = 'L’affectation n’a pas pu être enregistrée. Vérifiez la cohérence des données.';
                    }
                }
            }
        } elseif ($action === 'revoker') {
            $idAffectation = filter_var($_POST['id_affectation'] ?? null, FILTER_VALIDATE_INT) ?: 0;
            if ($idAffectation < 1) {
                $message = 'Affectation invalide.';
            } else {
                $update = $connexion->prepare('UPDATE affectation_enseignant SET active = 0 WHERE id_affectation = ? AND active = 1');
                $update->execute([$idAffectation]);
                $message = $update->rowCount() ? 'Affectation révoquée.' : 'Aucune affectation active correspondante.';
            }
        } else {
            $message = 'Action inconnue.';
        }
    }
    $_SESSION['flash_pedagogie'] = $message;
    header('Location: affectations.php');
    exit;
}

$filieres = pedagogie_filieres($connexion);
$niveaux = PEDAGOGIE_NIVEAUX;
$annees = pedagogie_options_annees($connexion);
$enseignants = $connexion->query('SELECT id_enseignant, nom, prenom, email FROM enseignant WHERE actif = 1 ORDER BY nom, prenom')->fetchAll(PDO::FETCH_ASSOC);
$modules = $connexion->query(
    'SELECT m.id_module, m.id_filiere, m.niveau, m.nom_module, f.code_filiere, f.nomFiliere
     FROM module_pedagogique m INNER JOIN filiere f ON f.idFiliere = m.id_filiere
     WHERE m.actif = 1 ORDER BY f.nomFiliere, FIELD(m.niveau,"L1","L2","L3","Master 1","Master 2"), m.nom_module'
)->fetchAll(PDO::FETCH_ASSOC);
$affectations = $connexion->query(
    'SELECT a.id_affectation, a.active, c.niveau, c.annee_academique,
            f.code_filiere, f.nomFiliere, m.nom_module, e.nom, e.prenom, e.email
     FROM affectation_enseignant a
     INNER JOIN classe_pedagogique c ON c.id_classe = a.id_classe
     INNER JOIN filiere f ON f.idFiliere = c.id_filiere
     INNER JOIN module_pedagogique m ON m.id_module = a.id_module
     INNER JOIN enseignant e ON e.id_enseignant = a.id_enseignant
     ORDER BY a.active DESC, c.annee_academique DESC, f.nomFiliere, e.nom, m.nom_module'
)->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Affectations pédagogiques | HEMIP</title>
  <link rel="stylesheet" href="assets/css/hemip-theme.css"><link rel="stylesheet" href="assets/css/espace-pedagogique.css"><link rel="stylesheet" href="assets/vendor/remixicon/remixicon.css">
</head>
<body>
<header class="pedag-topbar">
  <a class="pedag-brand" href="sac.php"><img src="assets/img/logo-hemip.jpg" alt="">HEMIP · SAC</a>
  <nav class="pedag-nav" aria-label="Navigation SAC"><a href="sac.php">Accueil</a><a href="modules.php">Modules</a><a href="affectations.php" aria-current="page">Affectations</a><a href="emploi-du-temps.php">Planning</a></nav>
  <a href="deconnexion.php">Déconnexion</a>
</header>
<main class="pedag-page"><div class="pedag-wrap">
  <div class="pedag-head"><div><h1>Affectations</h1><p>Chaque affectation relie un enseignant, une classe annuelle et un module compatible.</p></div></div>
  <?php if ($flash): ?><div class="pedag-alert" role="status"><?= pedagogie_e($flash) ?></div><?php endif; ?>
  <section class="pedag-panel">
    <h2>Nouvelle affectation</h2>
    <?php if (!$enseignants): ?><div class="pedag-alert pedag-alert--error">Aucun compte enseignant actif. Le SuperAdmin doit d’abord créer un compte.</div><?php endif; ?>
    <?php if (!$modules): ?><div class="pedag-alert pedag-alert--error">Aucun module actif. Créez d’abord les modules de la filière et du niveau concernés.</div><?php endif; ?>
    <form class="pedag-form" method="post">
      <?= hemip_csrf_input() ?><input type="hidden" name="action" value="affecter">
      <div class="pedag-field"><label for="id-enseignant">Enseignant actif</label><select id="id-enseignant" name="id_enseignant" required><option value="">Choisir</option><?php foreach ($enseignants as $enseignant): ?><option value="<?= (int) $enseignant['id_enseignant'] ?>"><?= pedagogie_e($enseignant['prenom'] . ' ' . $enseignant['nom'] . ' — ' . $enseignant['email']) ?></option><?php endforeach; ?></select></div>
      <div class="pedag-field"><label for="id-filiere">Filière</label><select id="id-filiere" name="id_filiere" required><option value="">Choisir</option><?php foreach ($filieres as $filiere): ?><option value="<?= (int) $filiere['idFiliere'] ?>"><?= pedagogie_e($filiere['code_filiere'] . ' — ' . $filiere['nomFiliere']) ?></option><?php endforeach; ?></select></div>
      <div class="pedag-field"><label for="niveau">Niveau</label><select id="niveau" name="niveau" required><option value="">Choisir</option><?php foreach ($niveaux as $niveau): ?><option value="<?= pedagogie_e($niveau) ?>"><?= pedagogie_e($niveau) ?></option><?php endforeach; ?></select></div>
      <div class="pedag-field"><label for="annee-academique">Année académique</label><select id="annee-academique" name="annee_academique" required><?php foreach ($annees as $annee): ?><option value="<?= pedagogie_e($annee) ?>" <?= $annee === '2026-2027' ? 'selected' : '' ?>><?= pedagogie_e($annee) ?></option><?php endforeach; ?></select></div>
      <div class="pedag-field"><label for="id-module">Module actif compatible</label><select id="id-module" name="id_module" required><option value="">Choisir</option><?php foreach ($modules as $module): ?><option value="<?= (int) $module['id_module'] ?>"><?= pedagogie_e($module['code_filiere'] . ' · ' . $module['niveau'] . ' · ' . $module['nom_module']) ?></option><?php endforeach; ?></select></div>
      <button class="pedag-button" type="submit" <?= (!$enseignants || !$modules) ? 'disabled' : '' ?>>Affecter</button>
    </form>
    <p class="pedag-muted" style="margin:14px 0 0">La classe est créée une seule fois pour cette combinaison filière/niveau/année. Le serveur refuse tout module incompatible.</p>
  </section>
  <section class="pedag-panel" style="margin-top:18px">
    <h2>Affectations enregistrées (<?= count($affectations) ?>)</h2>
    <?php if (!$affectations): ?><p class="pedag-muted">Aucune affectation pour le moment.</p><?php else: ?>
    <div class="pedag-table-wrap"><table class="pedag-table"><thead><tr><th>Enseignant</th><th>Classe</th><th>Module</th><th>État</th><th>Action</th></tr></thead><tbody>
      <?php foreach ($affectations as $affectation): ?><tr><td><?= pedagogie_e($affectation['prenom'] . ' ' . $affectation['nom']) ?><br><span class="pedag-muted"><?= pedagogie_e($affectation['email']) ?></span></td><td><?= pedagogie_e($affectation['code_filiere'] . ' · ' . $affectation['niveau'] . ' · ' . $affectation['annee_academique']) ?></td><td><?= pedagogie_e($affectation['nom_module']) ?></td><td><span class="pedag-tag"><?= (int) $affectation['active'] === 1 ? 'Active' : 'Révoquée' ?></span></td><td><?php if ((int) $affectation['active'] === 1): ?><form method="post"><?= hemip_csrf_input() ?><input type="hidden" name="action" value="revoker"><input type="hidden" name="id_affectation" value="<?= (int) $affectation['id_affectation'] ?>"><button class="pedag-button pedag-button--danger" type="submit">Révoquer</button></form><?php else: ?><span class="pedag-muted">—</span><?php endif; ?></td></tr><?php endforeach; ?>
    </tbody></table></div><?php endif; ?>
  </section>
</div></main>
</body>
</html>
