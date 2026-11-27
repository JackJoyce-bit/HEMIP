<?php
declare(strict_types=1);

function hemip_start_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $https = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== '' && $_SERVER['HTTPS'] !== 'off';
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $https,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function hemip_private_headers(): void
{
    if (!headers_sent()) {
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');
    }
}

function hemip_clear_identity(): void
{
    foreach ([
        'admin_connecte', 'sac_connecte', 'daac_connecte', 'enseignant_connecte',
        'etudiant_connecte', 'idAdmin', 'idEnseignant', 'idCandidat', 'role',
        'nom', 'prenom', 'email', 'etudiant_nom', 'etudiant_prenom',
        'etudiant_email', 'etudiant_matricule',
    ] as $key) {
        unset($_SESSION[$key]);
    }
}

/**
 * Exige une identité précise et relit son rôle/état depuis la base à chaque requête.
 * Rôles admis : Administrateur, SuperAdmin, SAC, DAAC, Enseignant, Etudiant.
 */
function hemip_require_role(PDO $connexion, string $expectedRole): array
{
    hemip_start_session();
    hemip_private_headers();

    $map = [
        'Administrateur' => ['flag' => 'admin_connecte', 'id' => 'idAdmin', 'table' => 'administrateur', 'column' => 'idAdmin'],
        'SuperAdmin' => ['flag' => 'admin_connecte', 'id' => 'idAdmin', 'table' => 'administrateur', 'column' => 'idAdmin'],
        'SAC' => ['flag' => 'sac_connecte', 'id' => 'idAdmin', 'table' => 'administrateur', 'column' => 'idAdmin'],
        'DAAC' => ['flag' => 'daac_connecte', 'id' => 'idAdmin', 'table' => 'administrateur', 'column' => 'idAdmin'],
        'Enseignant' => ['flag' => 'enseignant_connecte', 'id' => 'idEnseignant', 'table' => 'enseignant', 'column' => 'id_enseignant'],
        'Etudiant' => ['flag' => 'etudiant_connecte', 'id' => 'idCandidat', 'table' => 'candidat', 'column' => 'idCandidat'],
    ];

    if (!isset($map[$expectedRole])) {
        throw new InvalidArgumentException('Rôle de garde inconnu.');
    }

    $identity = $map[$expectedRole];
    if (empty($_SESSION[$identity['flag']]) || empty($_SESSION[$identity['id']])) {
        header('Location: login.php');
        exit;
    }

    try {
        if ($expectedRole === 'Enseignant') {
            $query = $connexion->prepare('SELECT id_enseignant, nom, prenom, email, telephone, photo, actif, force_changement_mdp FROM enseignant WHERE id_enseignant = ? LIMIT 1');
            $query->execute([(int) $_SESSION[$identity['id']]]);
            $account = $query->fetch(PDO::FETCH_ASSOC);
            $valid = $account && (int) $account['actif'] === 1;
        } elseif ($expectedRole === 'Etudiant') {
            $query = $connexion->prepare('SELECT idCandidat, nom, prenom, email, matricule_ancien FROM candidat WHERE idCandidat = ? LIMIT 1');
            $query->execute([(int) $_SESSION[$identity['id']]]);
            $account = $query->fetch(PDO::FETCH_ASSOC);
            $valid = false;
            if ($account) {
                $inscription = $connexion->prepare("SELECT 1 FROM preinscription WHERE idCandidat = ? AND statut = 'Validée' LIMIT 1");
                $inscription->execute([(int) $account['idCandidat']]);
                $valid = (bool) $inscription->fetchColumn();
            }
        } else {
            $query = $connexion->prepare('SELECT idAdmin, nom, prenom, email, role FROM administrateur WHERE idAdmin = ? LIMIT 1');
            $query->execute([(int) $_SESSION[$identity['id']]]);
            $account = $query->fetch(PDO::FETCH_ASSOC);
            $valid = $account && $account['role'] === $expectedRole;
        }
    } catch (PDOException $exception) {
        // Une table ou une colonne encore absente ne donne jamais accès.
        $account = false;
        $valid = false;
    }

    if (
        $valid && $expectedRole === 'Enseignant'
        && (int) $account['force_changement_mdp'] === 1
        && basename((string) ($_SERVER['SCRIPT_NAME'] ?? '')) !== 'enseignant-motdepasse.php'
    ) {
        header('Location: enseignant-motdepasse.php');
        exit;
    }

    if (!$valid || !$account) {
        hemip_clear_identity();
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
        header('Location: login.php');
        exit;
    }

    if (isset($account['role'])) {
        $_SESSION['role'] = $account['role'];
    }

    return $account;
}

function hemip_require_admin(PDO $connexion): array
{
    hemip_start_session();
    hemip_private_headers();
    if (empty($_SESSION['admin_connecte']) || empty($_SESSION['idAdmin'])) {
        header('Location: login.php');
        exit;
    }

    $query = $connexion->prepare("SELECT idAdmin, nom, prenom, email, role FROM administrateur WHERE idAdmin = ? AND role IN ('Administrateur','SuperAdmin') LIMIT 1");
    $query->execute([(int) $_SESSION['idAdmin']]);
    $account = $query->fetch(PDO::FETCH_ASSOC);
    if (!$account) {
        hemip_clear_identity();
        header('Location: login.php');
        exit;
    }
    $_SESSION['role'] = $account['role'];
    return $account;
}

function hemip_require_superadmin(PDO $connexion): array
{
    $account = hemip_require_admin($connexion);
    if ($account['role'] !== 'SuperAdmin') {
        http_response_code(403);
        exit('Accès refusé.');
    }
    return $account;
}
