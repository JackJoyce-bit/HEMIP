<?php
/**
 * Page de test de l'envoi d'emails (administrateurs connectés uniquement).
 */

session_start();

if (
    empty($_SESSION['admin_connecte']) ||
    $_SESSION['admin_connecte'] !== true
) {
    header("Location: login.php");
    exit();
}

require_once "mail.php";

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

$config = config_email();

$resultat = null;
$erreur = null;
$adresse = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $adresse = trim($_POST['email'] ?? '');

    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {

        $resultat = false;
        $erreur = "Session expirée. Rechargez la page et réessayez.";

    } else {

        $resultat = envoyer_email(
            $adresse,
            'HEMIP - Test d\'envoi d\'email',
            "Bonjour,\n\nCeci est un email de test envoyé depuis le site HEMIP.\n"
            . "Si vous lisez ce message, l'envoi des emails fonctionne.\n",
            $erreur
        );
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test d'envoi d'email | HEMIP</title>

    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 30px 15px;
            font-family: Arial, Helvetica, sans-serif;
            background: var(--hemip-bg);
            color: var(--hemip-text);
        }

        .carte {
            max-width: 560px;
            margin: 0 auto;
            padding: 28px;
            background: var(--hemip-bg);
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(var(--hemip-text-rgb), 0.08);
        }

        h1 { margin: 0 0 6px; font-size: 22px; }

        p { line-height: 1.5; }

        .reglages {
            margin: 18px 0;
            padding: 12px 14px;
            background: var(--hemip-bg);
            border: 1px solid var(--hemip-neutral-soft);
            border-radius: 8px;
            font-size: 14px;
            line-height: 1.7;
        }

        label { display: block; margin: 16px 0 6px; font-weight: bold; font-size: 14px; }

        input[type=email] {
            width: 100%;
            padding: 11px 12px;
            border: 1px solid var(--hemip-neutral);
            border-radius: 8px;
            font-size: 15px;
        }

        button {
            margin-top: 16px;
            padding: 11px 20px;
            background: var(--hemip-primary-strong);
            color: var(--hemip-bg);
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
        }

        button:hover { background: var(--hemip-primary-strong); }

        .message {
            margin: 18px 0 0;
            padding: 12px 14px;
            border-left: 4px solid;
            border-radius: 6px;
            font-size: 14px;
            line-height: 1.5;
        }

        .message.ok { background: var(--hemip-primary-soft); border-color: var(--hemip-primary-strong); color: var(--hemip-primary-strong); }
        .message.ko { background: var(--hemip-accent-soft); border-color: var(--hemip-accent); color: var(--hemip-accent-strong); }

        a { color: var(--hemip-primary-strong); }
    </style>
  <link rel="stylesheet" href="assets/css/hemip-theme.css">
</head>

<body>

<div class="carte">

    <h1>Test d'envoi d'email</h1>

    <p>Envoyez un email de test pour vérifier vos réglages.</p>

    <div class="reglages">
        Méthode : <strong><?= !empty($config['smtp']) ? 'SMTP' : 'mail() de PHP' ?></strong><br>
        <?php if (!empty($config['smtp'])): ?>
            Serveur : <strong><?= htmlspecialchars($config['hote']) ?>:<?= (int) $config['port'] ?></strong>
            (sécurité : <?= $config['securite'] !== '' ? htmlspecialchars($config['securite']) : 'aucune' ?>)<br>
            Identifiant : <?= $config['utilisateur'] !== '' ? htmlspecialchars($config['utilisateur']) : '(aucun)' ?><br>
        <?php endif; ?>
        Expéditeur : <?= htmlspecialchars($config['expediteur_email']) ?>
    </div>

    <form method="POST">

        <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">

        <label for="email">Envoyer le test à cette adresse</label>

        <input
            type="email"
            id="email"
            name="email"
            required
            value="<?= htmlspecialchars($adresse) ?>"
            placeholder="vous@exemple.com"
        >

        <button type="submit">Envoyer le test</button>

    </form>

    <?php if ($resultat === true): ?>

        <div class="message ok">
            Email accepté par le serveur mail et envoyé à
            <strong><?= htmlspecialchars($adresse) ?></strong>.
            Avec Mailpit, ouvrez
            <a href="http://localhost:8025" target="_blank" rel="noopener">http://localhost:8025</a>
            pour le lire. Avec Gmail, regardez aussi vos courriers indésirables.
        </div>

    <?php elseif ($resultat === false): ?>

        <div class="message ko">
            L'email n'a pas pu être envoyé.<br>
            <strong><?= htmlspecialchars((string) $erreur) ?></strong>
        </div>

    <?php endif; ?>

    <p style="margin-top: 22px; font-size: 13px; color: var(--hemip-muted);">
        Tous les envois sont notés dans <code>logs/emails.log</code>.
        <br><a href="dashboard.php">Retour au tableau de bord</a>
    </p>

</div>

</body>
</html>
