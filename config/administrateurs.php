<?php
session_start();

require_once "connexion.php";

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

/* ==========================================
   ACCÈS : SuperAdmin uniquement
   (le rôle est relu en base à chaque requête,
   pour qu'un changement de rôle soit immédiat)
========================================== */

if (empty($_SESSION['admin_connecte']) || empty($_SESSION['idAdmin'])) {
    header("Location: login.php");
    exit();
}

$idMoi = (int) $_SESSION['idAdmin'];

$requete = $connexion->prepare("SELECT role FROM administrateur WHERE idAdmin = ?");
$requete->execute([$idMoi]);
$roleReel = $requete->fetchColumn();

if ($roleReel === false) {
    // Le compte n'existe plus
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

$_SESSION['role'] = $roleReel;

if ($roleReel !== 'SuperAdmin') {
    header("Location: dashboard.php");
    exit();
}

/* ==========================================
   JETON CSRF
========================================== */

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

$rolesAutorises = ['Administrateur', 'SuperAdmin'];

/* ==========================================
   ACTIONS (modifier le rôle / supprimer)
========================================== */

function flash(string $type, string $texte): void
{
    $_SESSION['flash'] = ['type' => $type, 'texte' => $texte];
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $token = $_POST['csrf'] ?? '';
    $action = $_POST['action'] ?? '';
    $idCible = (int) ($_POST['idAdmin'] ?? 0);

    if (!hash_equals($_SESSION['csrf'], $token)) {

        flash('erreur', "Session expirée. Veuillez réessayer.");

    } elseif ($idCible === $idMoi) {

        flash('erreur', "Vous ne pouvez pas modifier ou supprimer votre propre compte.");

    } else {

        $verif = $connexion->prepare("SELECT idAdmin, prenom, nom FROM administrateur WHERE idAdmin = ?");
        $verif->execute([$idCible]);
        $cible = $verif->fetch(PDO::FETCH_ASSOC);

        if (!$cible) {

            flash('erreur', "Administrateur introuvable.");

        } elseif ($action === 'role') {

            $nouveauRole = $_POST['role'] ?? '';

            if (!in_array($nouveauRole, $rolesAutorises, true)) {

                flash('erreur', "Rôle invalide.");

            } else {

                $maj = $connexion->prepare("UPDATE administrateur SET role = ? WHERE idAdmin = ?");
                $maj->execute([$nouveauRole, $idCible]);

                flash('succes', "Le rôle de {$cible['prenom']} {$cible['nom']} est maintenant « {$nouveauRole} ».");
            }

        } elseif ($action === 'supprimer') {

            try {

                $suppr = $connexion->prepare("DELETE FROM administrateur WHERE idAdmin = ?");
                $suppr->execute([$idCible]);

                flash('succes', "Le compte de {$cible['prenom']} {$cible['nom']} a été supprimé.");

            } catch (PDOException $e) {

                flash('erreur', "Suppression impossible : ce compte est lié à d'autres données.");
            }

        } else {

            flash('erreur', "Action inconnue.");
        }
    }

    header("Location: administrateurs.php");
    exit();
}

/* ==========================================
   MESSAGE FLASH + LISTE
========================================== */

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$liste = $connexion->query(
    "SELECT idAdmin, nom, prenom, email, role
     FROM administrateur
     ORDER BY role = 'SuperAdmin' DESC, nom ASC, prenom ASC"
)->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des administrateurs | HEMIP</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.9.1/fonts/remixicon.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="assets/img/logo-hemip.jpg">

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', Arial, sans-serif;
            background: #f5f8fc;
            color: #071a33;
        }

        .topbar {
            background: #101a23;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 14px 30px;
        }

        .topbar a {
            color: #fff;
            text-decoration: none;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .topbar a:hover { color: #9cc3ff; }

        .container {
            max-width: 1000px;
            margin: 35px auto;
            padding: 0 20px;
        }

        .head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 22px;
        }

        .head h1 { font-size: 24px; }
        .head p  { color: #4b5563; font-size: 14px; margin-top: 4px; }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            background: #0b4ea2;
            color: #fff;
            padding: 10px 18px;
            border-radius: 25px;
            font: 500 14px 'Inter', Arial, sans-serif;
            transition: background .3s;
        }

        .btn:hover { background: #073570; }

        .btn-small { padding: 7px 13px; font-size: 13px; }

        .btn-danger { background: #c62828; }
        .btn-danger:hover { background: #8e1c1c; }

        .alert {
            padding: 12px 14px;
            border-radius: 8px;
            margin-bottom: 18px;
            font-size: 14px;
            border-left: 4px solid;
        }

        .alert.succes { background: #e6f6ec; color: #14683d; border-color: #14683d; }
        .alert.erreur { background: #ffe5e5; color: #b00020; border-color: #b00020; }

        .card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 4px 18px rgba(7, 26, 51, .08);
            overflow-x: auto;
        }

        table { width: 100%; border-collapse: collapse; min-width: 640px; }

        th, td {
            text-align: left;
            padding: 14px 18px;
            font-size: 14px;
            border-bottom: 1px solid #e5eaf1;
            vertical-align: middle;
        }

        th {
            background: #eaf3ff;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: #073570;
        }

        tr:last-child td { border-bottom: none; }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge.SuperAdmin    { background: #ede7ff; color: #4c2de0; }
        .badge.Administrateur { background: #eaf3ff; color: #0b4ea2; }
        .badge.moi           { background: #e6f6ec; color: #14683d; margin-left: 6px; }

        .actions { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }

        .actions form { display: flex; align-items: center; gap: 6px; }

        select {
            padding: 7px 8px;
            border: 1px solid #c9d3e0;
            border-radius: 8px;
            font: 13px 'Inter', Arial, sans-serif;
            background: #fff;
        }

        .vide { padding: 30px; text-align: center; color: #4b5563; }

        @media (max-width: 600px) {
            .topbar { padding: 12px 15px; }
            .container { margin: 22px auto; }
        }
    </style>
</head>

<body>

<header class="topbar">
    <a href="dashboard.php"><i class="ri-arrow-left-line"></i> Retour au tableau de bord</a>
    <a href="deconnexion.php" title="Se déconnecter"><i class="ri-global-off-line"></i> Déconnexion</a>
</header>

<main class="container">

    <div class="head">
        <div>
            <h1>Gestion des administrateurs</h1>
            <p><?= count($liste) ?> compte(s). Modifiez les rôles ou supprimez un accès.</p>
        </div>

        <a href="ajouter.php" class="btn">
            <i class="ri-user-add-line"></i> Ajouter un administrateur
        </a>
    </div>

    <?php if ($flash): ?>
        <div class="alert <?= $flash['type'] === 'succes' ? 'succes' : 'erreur' ?>" role="alert">
            <?= htmlspecialchars($flash['texte']) ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <?php if (empty($liste)): ?>

            <p class="vide">Aucun administrateur.</p>

        <?php else: ?>

        <table>
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($liste as $admin): ?>
                <?php $estMoi = ((int) $admin['idAdmin'] === $idMoi); ?>
                <tr>
                    <td><?= htmlspecialchars($admin['prenom'] . ' ' . $admin['nom']) ?></td>
                    <td><?= htmlspecialchars($admin['email']) ?></td>
                    <td>
                        <span class="badge <?= htmlspecialchars($admin['role']) ?>">
                            <?= htmlspecialchars($admin['role']) ?>
                        </span>
                        <?php if ($estMoi): ?><span class="badge moi">Vous</span><?php endif; ?>
                    </td>
                    <td>
                        <?php if ($estMoi): ?>
                            —
                        <?php else: ?>
                        <div class="actions">

                            <!-- Modifier le rôle -->
                            <form method="POST">
                                <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
                                <input type="hidden" name="action" value="role">
                                <input type="hidden" name="idAdmin" value="<?= (int) $admin['idAdmin'] ?>">

                                <select name="role" aria-label="Rôle">
                                    <?php foreach ($rolesAutorises as $r): ?>
                                        <option value="<?= $r ?>" <?= $admin['role'] === $r ? 'selected' : '' ?>>
                                            <?= $r ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>

                                <button type="submit" class="btn btn-small">Modifier</button>
                            </form>

                            <!-- Supprimer -->
                            <form method="POST"
                                  onsubmit="return confirm('Supprimer définitivement le compte de <?= htmlspecialchars(addslashes($admin['prenom'] . ' ' . $admin['nom']), ENT_QUOTES) ?> ?');">
                                <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
                                <input type="hidden" name="action" value="supprimer">
                                <input type="hidden" name="idAdmin" value="<?= (int) $admin['idAdmin'] ?>">

                                <button type="submit" class="btn btn-small btn-danger" title="Supprimer">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            </form>

                        </div>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <?php endif; ?>
    </div>

</main>

</body>
</html>
