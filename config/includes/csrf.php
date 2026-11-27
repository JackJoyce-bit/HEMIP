<?php
declare(strict_types=1);

require_once __DIR__ . '/role-guard.php';

function hemip_csrf_token(): string
{
    hemip_start_session();
    if (empty($_SESSION['_hemip_csrf'])) {
        $_SESSION['_hemip_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_hemip_csrf'];
}

function hemip_csrf_valid($token): bool
{
    hemip_start_session();
    return is_string($token)
        && isset($_SESSION['_hemip_csrf'])
        && is_string($_SESSION['_hemip_csrf'])
        && hash_equals($_SESSION['_hemip_csrf'], $token);
}

function hemip_csrf_input(string $name = 'csrf'): string
{
    $safeName = htmlspecialchars($name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $safeToken = htmlspecialchars(hemip_csrf_token(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    return '<input type="hidden" name="' . $safeName . '" value="' . $safeToken . '">';
}
