<?php
require_once __DIR__ . '/includes/role-guard.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/pedagogie.php';
hemip_start_session();
require_once __DIR__ . '/connexion.php';
$superadmin = hemip_require_superadmin($connexion);
hemip_private_headers();

$flash = $_SESSION['flash_enseignants'] ?? null;
unset($_SESSION['flash_enseignants']);
$identifiantsUneFois = $_SESSION['enseignant_identifiants_une_fois'] ?? null;
unset($_SESSION['enseignant_identifiants_une_fois']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = '';
    if (!hemip_csrf_valid($_POST['csrf'] ?? null)) {
        $message = 'Session expirée. Rechargez la page puis réessayez.';
    } else {
        $action = $_POST['action'] ?? '';
        if ($action === 'creer') {
            $nomSaisi = $_POST['nom'] ?? '';
            $prenomSaisi = $_POST['prenom'] ?? '';
            $emailSaisi = $_POST['email'] ?? '';
            $telephoneSaisi = $_POST['telephone'] ?? '';
            $nom = is_string($nomSaisi) ? trim($nomSaisi) : '';
            $prenom = is_string($prenomSaisi) ? trim($prenomSaisi) : '';
            $email = is_string($emailSaisi) ? trim($emailSaisi) : '';
            $telephone = is_string($telephoneSaisi) ? trim($telephoneSaisi) : '';

            if (mb_strlen($nom) < 1 || mb_strlen($nom) > 100 || mb_strlen($prenom) < 1 || mb_strlen($prenom) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 254 || mb_strlen($telephone) > 20) {
                $message = 'Vérifiez le nom, le prénom, l’adresse e-mail et le téléphone.';
            } else {
                $temporaire = bin2hex(random_bytes(16));
                try {
                    $insert = $connexion->prepare(
                        'INSERT INTO enseignant (nom, prenom, email, motdepasse, telephone, actif, force_changement_mdp)
                         VALUES (?, ?, ?, ?, ?, 1, 1)'
                    );
                    $insert->execute([$nom, $prenom, $email, password_hash($temporaire, PASSWORD_DEFAULT), $telephone !== '' ? $telephone : null]);
                    $_SESSION['enseignant_identifiants_une_fois'] = [
                        'nom' => $prenom . ' ' . $nom,
                        'email' => $email,
                        'motdepasse' => $temporaire,
                    ];
                    $message = 'Compte enseignant créé. Le mot de passe temporaire ci-dessous est visible une seule fois.';
                } catch (PDOException $exception) {
                    $message = 'Création impossible : vérifiez que cette adresse e-mail n’est pas déjà utilisée.';
                }
            }
        } elseif (in_array($action, ['suspendre', 'reactiver'], true)) {
            $idEnseignant = filter_var($_POST['id_enseignant'] ?? null, FILTER_VALIDATE_INT) ?: 0;
            if ($idEnseignant < 1) {
                $message = 'Compte enseignant invalide.';
            } else {
                $actif = $action === 'reactiver' ? 1 : 0;
                $update = $connexion->prepare('UPDATE enseignant SET actif = ? WHERE id_enseignant = ?');
                $update->execute([$actif, $idEnseignant]);
                $message = $update->rowCount() ? ($actif ? 'Compte réactivé.' : 'Compte suspendu.') : 'Aucun changement effectué.';
            }
        } else {
            $message = 'Action inconnue.';
        }
    }
    $_SESSION['flash_enseignants'] = $message;
    header('Location: enseignants.php');
    exit;
}

$liste = $connexion->query(
    'SELECT e.id_enseignant, e.nom, e.prenom, e.email, e.telephone, e.actif, e.force_changement_mdp, e.date_creation,
            COUNT(DISTINCT CASE WHEN a.active = 1 THEN a.id_affectation END) AS nb_affectations
     FROM enseignant e LEFT JOIN affectation_enseignant a ON a.id_enseignant = e.id_enseignant
     GROUP BY e.id_enseignant, e.nom, e.prenom, e.email, e.telephone, e.actif, e.force_changement_mdp, e.date_creation
     ORDER BY e.actif DESC, e.nom, e.prenom'
)->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Comptes enseignants | HEMIP</title>
  <link rel="stylesheet" href="assets/css/hemip-theme.css"><link rel="stylesheet" href="assets/css/espace-pedagogique.css"><link rel="stylesheet" href="assets/vendor/remixicon/remixicon.css">
</head>
<body>
<header class="pedag-topbar"><a class="pedag-brand" href="dashboard.php"><img src="assets/img/logo-hemip.jpg" alt="">HEMIP · SuperAdmin</a><nav class="pedag-nav" aria-label="Navigation"><a href="dashboard.php">Tableau de bord</a><a href="administrateurs.php">Administrateurs</a><a href="enseignants.php" aria-current="page">Enseignants</a></nav><a href="deconnexion.php">Déconnexion</a></header>
<main class="pedag-page"><div class="pedag-wrap">
  <div class="pedag-head"><div><h1>Comptes enseignants</h1><p>Création réservée au SuperAdmin ; les enseignants n’ont pas d’inscription publique.</p></div></div>
  <?php if ($flash): ?><div class="pedag-alert" role="status"><?= pedagogie_e($flash) ?></div><?php endif; ?>
  <?php if (is_array($identifiantsUneFois)): ?>
    <section class="pedag-panel" aria-live="polite" style="margin-bottom:18px">
      <h2>Accès temporaire — à remettre directement à l’enseignant</h2>
      <p><strong><?= pedagogie_e($identifiantsUneFois['nom']) ?></strong> · <?= pedagogie_e($identifiantsUneFois['email']) ?></p>
      <p>Mot de passe temporaire (affiché une seule fois) : <code><?= pedagogie_e($identifiantsUneFois['motdepasse']) ?></code></p>
      <p class="pedag-muted">À la première connexion, l’enseignant devra le remplacer avant d’accéder à son espace. Aucun courriel n’a été envoyé.</p>
    </section>
  <?php endif; ?>
  <section class="pedag-panel">
    <h2>Créer un compte enseignant</h2>
    <form class="pedag-form" method="post">
      <?= hemip_csrf_input() ?><input type="hidden" name="action" value="creer">
      <div class="pedag-field"><label for="nom">Nom</label><input id="nom" name="nom" maxlength="100" required></div>
      <div class="pedag-field"><label for="prenom">Prénom</label><input id="prenom" name="prenom" maxlength="100" required></div>
      <div class="pedag-field"><label for="email">E-mail</label><input id="email" name="email" type="email" maxlength="254" required></div>
      <div class="pedag-field"><label for="telephone">Téléphone (facultatif)</label><input id="telephone" name="telephone" type="tel" maxlength="20"></div>
      <button class="pedag-button" type="submit">Créer et générer un accès temporaire</button>
    </form>
  </section>
  <section class="pedag-panel" style="margin-top:18px">
    <h2>Liste des enseignants (<?= count($liste) ?>)</h2>
    <?php if (!$liste): ?><p class="pedag-muted">Aucun compte enseignant.</p><?php else: ?>
    <div class="pedag-table-wrap"><table class="pedag-table"><thead><tr><th>Enseignant</th><th>Contact</th><th>Affectations</th><th>Compte</th><th>Action</th></tr></thead><tbody>
    <?php foreach ($liste as $enseignant): ?><tr>
      <td><?= pedagogie_e($enseignant['prenom'] . ' ' . $enseignant['nom']) ?><br><span class="pedag-muted">Créé le <?= pedagogie_e(date('d/m/Y', strtotime($enseignant['date_creation']))) ?></span></td>
      <td><?= pedagogie_e($enseignant['email']) ?><br><span class="pedag-muted"><?= pedagogie_e($enseignant['telephone'] ?? '—') ?></span></td>
      <td><?= (int) $enseignant['nb_affectations'] ?></td>
      <td><span class="pedag-tag"><?= (int) $enseignant['actif'] === 1 ? 'Actif' : 'Suspendu' ?></span><?php if ((int) $enseignant['force_changement_mdp'] === 1): ?><br><span class="pedag-muted">Mot de passe à remplacer</span><?php endif; ?></td>
      <td><form method="post"><?= hemip_csrf_input() ?><input type="hidden" name="id_enseignant" value="<?= (int) $enseignant['id_enseignant'] ?>"><input type="hidden" name="action" value="<?= (int) $enseignant['actif'] === 1 ? 'suspendre' : 'reactiver' ?>"><button class="pedag-button <?= (int) $enseignant['actif'] === 1 ? 'pedag-button--danger' : '' ?>" type="submit"><?= (int) $enseignant['actif'] === 1 ? 'Suspendre' : 'Réactiver' ?></button></form></td>
    </tr><?php endforeach; ?>
    </tbody></table></div><?php endif; ?>
  </section>
</div></main>
</body>
</html>
