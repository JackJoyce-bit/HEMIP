<?php
/**
 * Affiche ou télécharge un document déposé par un candidat.
 *
 *   document.php?f=uploads/xxx.pdf            -> page d'aperçu (le document s'affiche)
 *   document.php?f=uploads/xxx.pdf&raw=1      -> le fichier lui-même, affiché dans le navigateur
 *   document.php?f=uploads/xxx.pdf&dl=1       -> téléchargement
 *
 * Réservé aux administrateurs connectés.
 */

session_start();

if (
    empty($_SESSION['admin_connecte']) ||
    $_SESSION['admin_connecte'] !== true
) {
    http_response_code(403);
    exit("Accès refusé.");
}

// On n'a plus besoin de la session : évite de bloquer les autres pages
session_write_close();

require_once "connexion.php";

$chemin = $_GET['f'] ?? '';

if ($chemin === '') {
    http_response_code(400);
    exit("Document non précisé.");
}

if (($_GET['dl'] ?? '') === '1') {
    $mode = 'telecharger';
} elseif (($_GET['raw'] ?? '') === '1') {
    $mode = 'brut';
} else {
    $mode = 'voir';
}

// Le fichier doit être enregistré dans la table document
$requete = $connexion->prepare("
    SELECT nom_document, type_document
    FROM document
    WHERE chemin_fichier = :chemin
    LIMIT 1
");

$requete->execute([':chemin' => $chemin]);

$document = $requete->fetch(PDO::FETCH_ASSOC);

if (!$document) {
    http_response_code(404);
    exit("Document introuvable.");
}

// Le fichier doit bien se trouver dans le dossier uploads
$dossier = realpath(__DIR__ . '/uploads');
$fichier = realpath(__DIR__ . '/' . $chemin);

if (
    $dossier === false ||
    $fichier === false ||
    strpos($fichier, $dossier . DIRECTORY_SEPARATOR) !== 0 ||
    !is_file($fichier)
) {
    http_response_code(404);
    exit("Le fichier n'existe plus sur le serveur.");
}


// ==========================
// TYPE DU FICHIER
// ==========================

$extension = strtolower(pathinfo($fichier, PATHINFO_EXTENSION));

$typesParExtension = [
    'pdf'  => 'application/pdf',
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'jfif' => 'image/jpeg',
    'png'  => 'image/png',
    'gif'  => 'image/gif',
    'webp' => 'image/webp'
];

// On regarde le contenu réel du fichier (pas seulement son extension)
$typeMime = '';

if (function_exists('finfo_open')) {

    $info = finfo_open(FILEINFO_MIME_TYPE);

    if ($info !== false) {
        $typeMime = (string) finfo_file($info, $fichier);
        finfo_close($info);
    }
}

if ($typeMime === '' || $typeMime === 'application/octet-stream') {
    $typeMime = $typesParExtension[$extension] ?? 'application/octet-stream';
}

$typesAffichables = [
    'application/pdf',
    'image/jpeg',
    'image/png',
    'image/gif',
    'image/webp'
];

$affichable = in_array($typeMime, $typesAffichables, true);


// ==========================
// NOM DU FICHIER
// ==========================

$nomAffiche = trim((string) ($document['nom_document'] ?? ''));

if ($nomAffiche === '') {
    $nomAffiche = basename($fichier);
}

$nomAffiche = basename(str_replace(["\r", "\n", '"', '\\'], '', $nomAffiche));

if ($extension !== '' && strtolower(pathinfo($nomAffiche, PATHINFO_EXTENSION)) === '') {
    $nomAffiche .= '.' . $extension;
}


// ==========================
// TÉLÉCHARGEMENT
// ==========================

if ($mode === 'telecharger') {

    header("Content-Type: " . $typeMime);
    header("Content-Length: " . filesize($fichier));
    header(
        "Content-Disposition: attachment; filename=\"" . $nomAffiche . "\"; "
        . "filename*=UTF-8''" . rawurlencode($nomAffiche)
    );
    header("X-Content-Type-Options: nosniff");
    header("Cache-Control: private, no-store");

    readfile($fichier);
    exit();
}


// ==========================
// FICHIER AFFICHÉ DANS LE NAVIGATEUR
// ==========================

if ($mode === 'brut') {

    if (!$affichable) {
        http_response_code(415);
        exit("Ce type de fichier ne peut pas être affiché dans le navigateur.");
    }

    header("Content-Type: " . $typeMime);
    header("Content-Length: " . filesize($fichier));
    header("Content-Disposition: inline; filename=\"" . $nomAffiche . "\"");
    header("X-Content-Type-Options: nosniff");
    header("X-Frame-Options: SAMEORIGIN");
    header("Cache-Control: private, no-store");

    readfile($fichier);
    exit();
}


// ==========================
// PAGE D'APERÇU
// ==========================

$adresse = 'document.php?f=' . rawurlencode($chemin);

$titre = trim((string) ($document['type_document'] ?? ''));

if ($titre === '') {
    $titre = $nomAffiche;
}

header("Content-Type: text/html; charset=UTF-8");
header("Cache-Control: private, no-store");

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= htmlspecialchars($titre) ?> | Aperçu | HEMIP</title>

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        html, body {
            height: 100%;
            font-family: Arial, Helvetica, sans-serif;
            background: var(--hemip-text);
        }

        .barre {
            height: 56px;

            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;

            padding: 0 20px;

            background: var(--hemip-text);
            color: var(--hemip-bg);
        }

        .barre .nom {
            min-width: 0;
        }

        .barre .nom strong {
            display: block;
            font-size: 15px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .barre .nom span {
            display: block;
            font-size: 12px;
            color: var(--hemip-muted);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .btn {
            flex-shrink: 0;

            padding: 9px 16px;

            background: var(--hemip-primary-strong);
            color: var(--hemip-bg);

            border-radius: 6px;

            font-size: 14px;
            font-weight: bold;
            text-decoration: none;
        }

        .btn:hover { background: var(--hemip-primary-strong); }

        .zone {
            height: calc(100% - 56px);
        }

        .zone iframe {
            width: 100%;
            height: 100%;
            border: none;
            background: var(--hemip-bg);
        }

        .zone.image {
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: auto;
            padding: 20px;
        }

        .zone.image img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            background: var(--hemip-bg);
        }

        .indisponible {
            max-width: 480px;
            margin: 80px auto 0;
            padding: 30px;

            background: var(--hemip-bg);
            border-radius: 12px;

            text-align: center;
            color: var(--hemip-muted);
            line-height: 1.6;
        }

        .indisponible p { margin-bottom: 20px; }
    </style>
  <link rel="stylesheet" href="assets/css/hemip-theme.css">
</head>

<body>

    <div class="barre">

        <div class="nom">
            <strong><?= htmlspecialchars($titre) ?></strong>
            <span><?= htmlspecialchars($nomAffiche) ?></span>
        </div>

        <a class="btn" href="<?= htmlspecialchars($adresse . '&dl=1') ?>">
            Télécharger
        </a>

    </div>

    <?php if ($affichable && $typeMime === 'application/pdf'): ?>

        <div class="zone">
            <iframe
                src="<?= htmlspecialchars($adresse . '&raw=1') ?>"
                title="<?= htmlspecialchars($titre) ?>"
            ></iframe>
        </div>

    <?php elseif ($affichable): ?>

        <div class="zone image">
            <img
                src="<?= htmlspecialchars($adresse . '&raw=1') ?>"
                alt="<?= htmlspecialchars($titre) ?>"
            >
        </div>

    <?php else: ?>

        <div class="indisponible">
            <p>
                Ce type de fichier (<strong><?= htmlspecialchars($extension !== '' ? '.' . $extension : 'inconnu') ?></strong>)
                ne peut pas être affiché dans le navigateur.
            </p>

            <a class="btn" href="<?= htmlspecialchars($adresse . '&dl=1') ?>">
                Télécharger le document
            </a>
        </div>

    <?php endif; ?>

</body>
</html>
