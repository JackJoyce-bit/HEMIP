<?php
declare(strict_types=1);

session_start();
header('Cache-Control: no-store, private');
header('X-Content-Type-Options: nosniff');
header('X-Robots-Tag: noindex, nofollow');

function contact_redirect(string $status): void
{
    $_SESSION['contact_flash'] = $status;
    $_SESSION['contact_csrf'] = bin2hex(random_bytes(32));
    header('Location: contact.php#contact', true, 303);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    header('Content-Type: text/plain; charset=UTF-8');
    echo "Cette adresse accepte uniquement l’envoi du formulaire de contact.\n";
    exit;
}

$sessionToken = $_SESSION['contact_csrf'] ?? null;
$submittedToken = $_POST['csrf_token'] ?? null;
if (
    !is_string($sessionToken)
    || !is_string($submittedToken)
    || !hash_equals($sessionToken, $submittedToken)
) {
    contact_redirect('invalid');
}

$honeypot = $_POST['website'] ?? '';
if (!is_string($honeypot)) {
    contact_redirect('invalid');
}
if (trim($honeypot) !== '') {
    // Une soumission piégée reçoit une réponse neutre ; aucun message n'est envoyé.
    contact_redirect('sent');
}

$lastSubmission = isset($_SESSION['contact_last_submission'])
    ? (int) $_SESSION['contact_last_submission']
    : 0;
if ($lastSubmission > 0 && (time() - $lastSubmission) < 45) {
    contact_redirect('too_soon');
}

function contact_field(string $name, int $maxLength, bool $allowNewlines = false): ?string
{
    $value = $_POST[$name] ?? null;
    if (!is_string($value)) {
        return null;
    }

    $value = str_replace("\0", '', trim($value));
    if ($allowNewlines) {
        $value = str_replace(["\r\n", "\r"], "\n", $value);
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value) ?? '';
    } elseif (preg_match('/[\r\n\x00-\x1F\x7F]/u', $value)) {
        return null;
    }

    $length = function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
    if ($length > $maxLength) {
        return null;
    }

    return $value;
}

$prenom = contact_field('prenom', 100);
$nom = contact_field('nom', 100);
$email = contact_field('email', 254);
$telephone = contact_field('telephone', 40);
$objet = contact_field('objet', 150);
$message = contact_field('message', 5000, true);
$consentement = $_POST['consentement'] ?? null;

$valide = $prenom !== null && $prenom !== ''
    && $nom !== null && $nom !== ''
    && $email !== null && filter_var($email, FILTER_VALIDATE_EMAIL) !== false
    && $telephone !== null
    && $objet !== null && $objet !== ''
    && $message !== null && trim($message) !== ''
    && is_string($consentement) && $consentement === 'yes';

if ($telephone !== '' && !preg_match('/\A[0-9+().\s-]{6,40}\z/u', $telephone)) {
    $valide = false;
}
if (!$valide) {
    contact_redirect('invalid');
}

// L'intervalle s'applique aussi si le fournisseur SMTP est temporairement indisponible.
$_SESSION['contact_last_submission'] = time();

$destinataire = 'hemilaperceeinformation@gmail.com';
$sujet = 'Nouveau message via le formulaire du site HEMIP';
$corps = "Nouveau message reçu depuis le formulaire de contact HEMIP.\n\n"
    . "Prénom : " . $prenom . "\n"
    . "Nom : " . $nom . "\n"
    . "E-mail de réponse : " . $email . "\n"
    . "Téléphone : " . ($telephone !== '' ? $telephone : 'Non renseigné') . "\n"
    . "Objet : " . $objet . "\n\n"
    . "Message :\n" . $message . "\n";

require_once __DIR__ . '/mail.php';
$erreurEnvoi = null;
if (envoyer_email($destinataire, $sujet, $corps, $erreurEnvoi, false)) {
    contact_redirect('sent');
}

contact_redirect('failed');
