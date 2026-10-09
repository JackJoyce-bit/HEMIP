<?php
$scenario = $argv[1] ?? '';
$allowed = ['method', 'csrf', 'invalid', 'honeypot', 'rate-limit'];
if (!in_array($scenario, $allowed, true)) {
    fwrite(STDERR, "Scénario de test inconnu.\n");
    exit(2);
}

$sessionDirectory = __DIR__ . '/../contact-test-sessions';
if (!is_dir($sessionDirectory) && !mkdir($sessionDirectory, 0777, true) && !is_dir($sessionDirectory)) {
    fwrite(STDERR, "Impossible de créer le dossier de sessions de test.\n");
    exit(2);
}
$resolvedSessionDirectory = realpath($sessionDirectory);
if ($resolvedSessionDirectory === false) {
    fwrite(STDERR, "Impossible de résoudre le dossier de sessions de test.\n");
    exit(2);
}
session_save_path($resolvedSessionDirectory);
session_id('hemipcontact' . substr(hash('sha256', $scenario), 0, 16));
session_start();
$_SESSION = ['contact_csrf' => str_repeat('a', 64)];
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = [
    'csrf_token' => str_repeat('a', 64),
    'prenom' => 'Test',
    'nom' => 'Local',
    'email' => 'test@example.invalid',
    'telephone' => '',
    'objet' => 'Vérification locale',
    'message' => 'Message synthétique de test — aucun envoi ne doit être tenté.',
    'consentement' => 'yes',
    'website' => ''
];

switch ($scenario) {
    case 'method':
        $_SERVER['REQUEST_METHOD'] = 'GET';
        break;
    case 'csrf':
        $_POST['csrf_token'] = 'token-invalide';
        break;
    case 'invalid':
        $_POST['email'] = 'adresse invalide';
        break;
    case 'honeypot':
        $_POST['website'] = 'filled-by-test-bot';
        break;
    case 'rate-limit':
        $_SESSION['contact_last_submission'] = time();
        break;
}

session_write_close();

register_shutdown_function(function (): void {
    $flash = $_SESSION['contact_flash'] ?? '';
    echo PHP_EOL . 'RESULT status=' . http_response_code() . ' flash=' . $flash . PHP_EOL;
});

require dirname(__DIR__, 2) . '/config/contact-submit.php';
