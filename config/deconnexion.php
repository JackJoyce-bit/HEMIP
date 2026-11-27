<?php
require_once __DIR__ . '/includes/role-guard.php';
hemip_start_session();

// Supprimer toutes les données de session
$_SESSION = [];

// Expirer le cookie avant de détruire la session côté serveur.
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', [
        'expires' => time() - 42000,
        'path' => $params['path'],
        'domain' => $params['domain'],
        'secure' => $params['secure'],
        'httponly' => $params['httponly'],
        'samesite' => $params['samesite'] ?? 'Lax',
    ]);
}
session_destroy();

// Empêcher le navigateur de conserver les pages en cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

// Retour vers la page d'accueil
header("Location: index.php");
exit();
?>
