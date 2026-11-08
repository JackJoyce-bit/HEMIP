<?php
/**
 * Diagnostic de la base de données (administrateurs connectés uniquement).
 * Aide à comprendre pourquoi le site en ligne se comporte autrement qu'en local
 * (accents abîmés, statuts vides...).
 * À supprimer une fois le problème réglé.
 */

session_start();

if (
    empty($_SESSION['admin_connecte']) ||
    $_SESSION['admin_connecte'] !== true
) {
    header("Location: login.php");
    exit();
}

require_once "connexion.php";

function h($texte): string
{
    return htmlspecialchars((string) $texte, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Exécute une requête de lecture ; retourne [lignes, erreur]. */
function lire(PDO $connexion, string $sql): array
{
    try {
        return [$connexion->query($sql)->fetchAll(PDO::FETCH_ASSOC), null];
    } catch (Throwable $e) {
        return [[], $e->getMessage()];
    }
}

$problemes = [];
$conseils = [];


// ==========================
// SERVEUR ET CONNEXION
// ==========================

[$version] = lire($connexion, "SELECT VERSION() AS version");

[$connexionInfos] = lire($connexion, "
    SELECT
        @@character_set_client     AS client,
        @@character_set_connection AS connexion,
        @@character_set_results    AS resultats,
        @@collation_connection     AS collation,
        @@sql_mode                 AS mode
");

$infos = $connexionInfos[0] ?? [];

$charsetOk = in_array(
    strtolower((string) ($infos['resultats'] ?? '')),
    ['utf8mb4', 'utf8', 'utf8mb3'],
    true
);

if (!$charsetOk) {
    $problemes[] = "La connexion à la base n'utilise pas l'UTF-8 (résultats en « "
        . ($infos['resultats'] ?? '?') . " »). C'est la cause la plus fréquente des accents abîmés.";
    $conseils[] = "Ouvrez connexion.php sur l'hébergeur : la connexion doit se terminer par "
        . "charset=utf8mb4 et contenir SET NAMES utf8mb4 (voir le fichier connexion.php fourni).";
}


// ==========================
// TABLES
// ==========================

[$tables, $erreurTables] = lire($connexion, "
    SELECT TABLE_NAME, TABLE_COLLATION
    FROM information_schema.TABLES
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME IN ('candidat','preinscription','filiere','document','notification','administrateur')
    ORDER BY TABLE_NAME
");


// ==========================
// COLONNE « STATUT »
// ==========================

[$colonne] = lire($connexion, "
    SELECT HEX(COLUMN_TYPE) AS hex, COLUMN_TYPE AS type, CHARACTER_SET_NAME AS jeu
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'preinscription'
      AND COLUMN_NAME = 'statut'
");

$colonneInfos = $colonne[0] ?? null;

if ($colonneInfos) {

    $hexType = strtoupper((string) $colonneInfos['hex']);

    // Un ENUM doit contenir exactement « Validée » et « Rejetée » (accents en UTF-8)
    $estEnum = strpos($hexType, '656E756D') === 0;
    $valideeOk = strpos($hexType, bin2hex('Validée')) !== false;
    $rejeteeOk = strpos($hexType, bin2hex('Rejetée')) !== false;

    if ($estEnum && (!$valideeOk || !$rejeteeOk)) {
        $problemes[] = "La colonne « statut » est un ENUM dont les valeurs accentuées "
            . "(Validée, Rejetée) sont mal enregistrées. Les décisions ne peuvent pas être sauvegardées.";
        $conseils[] = "Dans phpMyAdmin, modifiez la colonne statut de la table preinscription pour "
            . "qu'elle accepte exactement : En attente, Validée, Rejetée (avec les accents), "
            . "ou bien choisissez le type VARCHAR(30).";
    }
}


// ==========================
// FILIÈRES
// ==========================

[$filieres, $erreurFilieres] = lire($connexion, "
    SELECT idFiliere, nomFiliere, HEX(nomFiliere) AS hex
    FROM filiere
    ORDER BY idFiliere
");

foreach ($filieres as $i => $ligne) {

    $octets = (string) hex2bin((string) $ligne['hex']);

    if (strpos($octets, "\xEF\xBF\xBD") !== false) {

        $filieres[$i]['etat'] = 'Accent perdu (caractère �)';
        $problemes[] = "La filière « " . $ligne['nomFiliere'] . " » contient un caractère de "
            . "remplacement (�) : l'accent a été perdu à l'import de la base.";
        $conseils[] = "Dans phpMyAdmin, ouvrez la table filiere et retapez les noms avec leurs accents "
            . "(par exemple « Génie Logiciel »).";

    } elseif (!mb_check_encoding($octets, 'UTF-8')) {

        $filieres[$i]['etat'] = 'Ancien format (latin1)';
        $problemes[] = "La filière « " . $ligne['nomFiliere'] . " » est enregistrée dans un ancien format "
            . "(latin1) au lieu de l'UTF-8.";
        $conseils[] = "Convertissez la table filiere en utf8mb4 dans phpMyAdmin "
            . "(Opérations, Collation : utf8mb4_general_ci, en cochant « Changer la collation de toutes les colonnes »).";

    } else {

        $filieres[$i]['etat'] = 'OK';
    }
}


// ==========================
// DEMANDES (STATUTS)
// ==========================

[$demandes, $erreurDemandes] = lire($connexion, "
    SELECT idPreinscription, type_demande, statut, HEX(statut) AS hex
    FROM preinscription
    ORDER BY idPreinscription DESC
    LIMIT 15
");

$statutsVides = 0;

foreach ($demandes as $ligne) {

    if (trim((string) $ligne['statut']) === '') {
        $statutsVides++;
    }
}

if ($statutsVides > 0) {
    $problemes[] = "$statutsVides demande(s) ont un statut vide. Cela arrive quand la base refuse une valeur "
        . "accentuée (par exemple « Validée »).";
    $conseils[] = "Après avoir corrigé l'encodage, remettez le bon statut : dans phpMyAdmin, onglet SQL, "
        . "UPDATE preinscription SET statut = 'En attente' WHERE statut = '';";
}

$conseils = array_values(array_unique($conseils));
$problemes = array_values(array_unique($problemes));

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnostic de la base | HEMIP</title>

    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 28px 15px 50px;
            font-family: Arial, Helvetica, sans-serif;
            background: #f1f5f9;
            color: #1e293b;
        }

        .page { max-width: 900px; margin: 0 auto; }

        h1 { margin: 0 0 6px; font-size: 24px; }

        .intro { margin: 0 0 22px; color: #64748b; line-height: 1.5; }

        .carte {
            margin-bottom: 18px;
            padding: 22px 24px;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 18px rgba(15, 23, 42, 0.07);
        }

        .carte h2 { margin: 0 0 14px; font-size: 17px; }

        table { width: 100%; border-collapse: collapse; font-size: 14px; }

        th, td {
            padding: 9px 10px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: top;
            word-break: break-word;
        }

        th { font-size: 12px; text-transform: uppercase; letter-spacing: .4px; color: #64748b; }

        code { font-family: Consolas, monospace; font-size: 12.5px; color: #475569; }

        .verdict {
            padding: 14px 18px;
            border-left: 5px solid;
            border-radius: 8px;
            line-height: 1.6;
            font-size: 15px;
        }

        .verdict.ok { background: #e6f6ec; border-color: #16a34a; color: #14532d; }
        .verdict.ko { background: #fee2e2; border-color: #dc2626; color: #7f1d1d; }

        ul.liste { margin: 10px 0 0; padding-left: 20px; line-height: 1.7; }

        .etat-ok { color: #16a34a; font-weight: bold; }
        .etat-ko { color: #dc2626; font-weight: bold; }

        .erreur { color: #b91c1c; font-size: 14px; }

        a { color: #2563eb; }
    </style>
</head>

<body>
<div class="page">

    <h1>Diagnostic de la base de données</h1>

    <p class="intro">
        Cette page montre comment le site voit votre base de données.
        Elle sert à comprendre pourquoi le site en ligne ne se comporte pas comme en local.
        <a href="dashboard.php">Retour au tableau de bord</a>
    </p>


    <div class="carte">

        <h2>Résultat</h2>

        <?php if (empty($problemes)): ?>

            <div class="verdict ok">
                Aucun problème d'encodage détecté sur les points vérifiés.
                Si le problème persiste, envoyez-moi une capture de cette page.
            </div>

        <?php else: ?>

            <div class="verdict ko">
                <strong>Problèmes détectés :</strong>
                <ul class="liste">
                    <?php foreach ($problemes as $probleme): ?>
                        <li><?= h($probleme) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="verdict ok" style="margin-top: 14px;">
                <strong>Comment corriger :</strong>
                <ul class="liste">
                    <?php foreach ($conseils as $conseil): ?>
                        <li><?= h($conseil) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>

        <?php endif; ?>

    </div>


    <div class="carte">

        <h2>Serveur et connexion</h2>

        <table>
            <tr><th>PHP</th><td><?= h(PHP_VERSION) ?></td></tr>
            <tr><th>MySQL</th><td><?= h($version[0]['version'] ?? '?') ?></td></tr>
            <tr>
                <th>Encodage de la connexion</th>
                <td>
                    client : <code><?= h($infos['client'] ?? '?') ?></code>,
                    connexion : <code><?= h($infos['connexion'] ?? '?') ?></code>,
                    résultats : <code><?= h($infos['resultats'] ?? '?') ?></code>
                    <br>
                    <?php if ($charsetOk): ?>
                        <span class="etat-ok">UTF-8 : OK</span>
                    <?php else: ?>
                        <span class="etat-ko">Pas en UTF-8</span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr><th>Mode SQL</th><td><code><?= h($infos['mode'] ?? '(vide)') ?: '(vide)' ?></code></td></tr>
        </table>

    </div>


    <div class="carte">

        <h2>Colonne « statut » des demandes</h2>

        <?php if ($colonneInfos): ?>
            <table>
                <tr><th>Type</th><td><code><?= h($colonneInfos['type']) ?></code></td></tr>
                <tr><th>Jeu de caractères</th><td><code><?= h($colonneInfos['jeu'] ?? '(non texte)') ?></code></td></tr>
            </table>
        <?php else: ?>
            <p class="erreur">Colonne introuvable.</p>
        <?php endif; ?>

    </div>


    <div class="carte">

        <h2>Filières enregistrées</h2>

        <?php if ($erreurFilieres): ?>
            <p class="erreur"><?= h($erreurFilieres) ?></p>
        <?php else: ?>
            <table>
                <tr><th>N°</th><th>Nom</th><th>État</th></tr>
                <?php foreach ($filieres as $ligne): ?>
                    <tr>
                        <td><?= h($ligne['idFiliere']) ?></td>
                        <td><?= h($ligne['nomFiliere']) ?></td>
                        <td class="<?= $ligne['etat'] === 'OK' ? 'etat-ok' : 'etat-ko' ?>">
                            <?= h($ligne['etat']) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>

    </div>


    <div class="carte">

        <h2>Dernières demandes</h2>

        <?php if ($erreurDemandes): ?>
            <p class="erreur"><?= h($erreurDemandes) ?></p>
        <?php else: ?>
            <table>
                <tr><th>N°</th><th>Type</th><th>Statut</th></tr>
                <?php foreach ($demandes as $ligne): ?>
                    <tr>
                        <td><?= h($ligne['idPreinscription']) ?></td>
                        <td><?= h($ligne['type_demande']) ?></td>
                        <td>
                            <?php if (trim((string) $ligne['statut']) === ''): ?>
                                <span class="etat-ko">(vide)</span>
                            <?php else: ?>
                                <?= h($ligne['statut']) ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>

    </div>


    <?php if (!empty($tables)): ?>
    <div class="carte">

        <h2>Tables</h2>

        <table>
            <tr><th>Table</th><th>Collation</th></tr>
            <?php foreach ($tables as $ligne): ?>
                <tr>
                    <td><?= h($ligne['TABLE_NAME']) ?></td>
                    <td><code><?= h($ligne['TABLE_COLLATION']) ?></code></td>
                </tr>
            <?php endforeach; ?>
        </table>

    </div>
    <?php endif; ?>

    <p class="intro">Supprimez ce fichier (diagnostic.php) une fois le problème réglé.</p>

</div>
</body>
</html>
