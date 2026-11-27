<?php
require_once __DIR__ . '/includes/pedagogie.php';
hemip_start_session();
require_once __DIR__ . '/connexion.php';
$enseignant = pedagogie_exiger_enseignant($connexion);
hemip_private_headers();

if ((int) $enseignant['force_changement_mdp'] !== 1) {
    header('Location: enseignant.php');
    exit;
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nouveau = $_POST['nouveau_motdepasse'] ?? '';
    $confirmation = $_POST['confirmation_motdepasse'] ?? '';
    if (!hemip_csrf_valid($_POST['csrf'] ?? null)) {
        $message = 'Session expirée. Rechargez la page puis réessayez.';
    } elseif (!is_string($nouveau) || !is_string($confirmation) || strlen($nouveau) < 12 || strlen($nouveau) > 200) {
        $message = 'Le nouveau mot de passe doit contenir au moins 12 caractères.';
    } elseif (!hash_equals($nouveau, $confirmation)) {
        $message = 'Les deux mots de passe ne correspondent pas.';
    } else {
        $hash = password_hash($nouveau, PASSWORD_DEFAULT);
        $update = $connexion->prepare('UPDATE enseignant SET motdepasse = ?, force_changement_mdp = 0 WHERE id_enseignant = ? AND actif = 1 AND force_changement_mdp = 1');
        $update->execute([$hash, (int) $enseignant['id_enseignant']]);
        if ($update->rowCount() !== 1) {
            $message = 'Le changement n’a pas pu être enregistré. Veuillez vous reconnecter.';
        } else {
            session_regenerate_id(true);
            unset($_SESSION['_hemip_csrf']);
            header('Location: enseignant.php');
            exit;
        }
    }
}
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Changer le mot de passe | HEMIP</title>
  <link rel="stylesheet" href="assets/css/hemip-theme.css"><link rel="stylesheet" href="assets/css/espace-pedagogique.css">
</head>
<body>
<header class="pedag-topbar"><a class="pedag-brand" href="enseignant-motdepasse.php"><img src="assets/img/logo-hemip.jpg" alt="">HEMIP · Espace enseignant</a><a href="deconnexion.php">Déconnexion</a></header>
<main class="pedag-page"><div class="pedag-wrap">
  <section class="pedag-panel" style="max-width:640px;margin:4vh auto">
    <h1>Définir votre mot de passe personnel</h1>
    <p class="pedag-muted">Pour protéger votre compte, le mot de passe temporaire doit être remplacé avant d’accéder à l’espace enseignant. Choisissez au moins 12 caractères.</p>
    <?php if ($message !== ''): ?><div class="pedag-alert pedag-alert--error" role="alert"><?= pedagogie_e($message) ?></div><?php endif; ?>
    <form method="post" class="pedag-form" autocomplete="new-password">
      <?= hemip_csrf_input() ?>
      <div class="pedag-field"><label for="nouveau">Nouveau mot de passe</label><input id="nouveau" type="password" name="nouveau_motdepasse" minlength="12" maxlength="200" autocomplete="new-password" required></div>
      <div class="pedag-field"><label for="confirmation">Confirmer le mot de passe</label><input id="confirmation" type="password" name="confirmation_motdepasse" minlength="12" maxlength="200" autocomplete="new-password" required></div>
      <button class="pedag-button" type="submit">Enregistrer et continuer</button>
    </form>
  </section>
</div></main>
</body>
</html>
