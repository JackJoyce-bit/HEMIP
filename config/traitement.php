<?php

session_start();

require_once "connexion.php";
require_once "historique.php";


// Vérifier que l'administrateur est connecté
if (
    !isset($_SESSION['admin_connecte']) ||
    $_SESSION['admin_connecte'] !== true
) {
    header("Location: login.php");
    exit();
}


// Vérifier les données reçues
if (
    $_SERVER['REQUEST_METHOD'] !== 'POST' ||
    !isset($_POST['codeUnique']) ||
    !isset($_POST['action'])
) {
    header("Location: dashboard.php");
    exit();
}


$codeUnique = $_POST['codeUnique'];
$action = $_POST['action'];

$actionsValides = [
    'accepter',             // accepter la candidature
    'refuser',              // refuser la candidature
    'accepter_inscription', // accepter la demande d'inscription (Préinscriptions)
    'refuser_inscription',  // refuser la demande d'inscription (Préinscriptions)
    'effacer'               // effacer une candidature refusée (Candidatures)
];

if (!in_array($action, $actionsValides, true)) {
    header("Location: dashboard.php");
    exit();
}

// Les décisions sur l'inscription se prennent dans « Préinscriptions »
$section = in_array($action, ['accepter_inscription', 'refuser_inscription'], true)
    ? 'preinscription'
    : 'candidatures';


/**
 * Enregistre le message à afficher sur le tableau de bord
 * puis retourne au tableau de bord.
 */
function terminer(string $type, string $titre, string $message, string $section): void
{
    global $connexion;

    // Garder une trace dans « Historique des notifications »
    historique_ajouter(
        $connexion,
        (int) ($_SESSION['idAdmin'] ?? 0) ?: null,
        $type,
        $titre,
        $message
    );

    $_SESSION['flash'] = [
        'type' => $type,
        'titre' => $titre,
        'message' => $message,
        'section' => $section
    ];

    header("Location: dashboard.php");
    exit();
}


// Rechercher le candidat (sa demande d'inscription, pas une réinscription)
$requete = $connexion->prepare("
    SELECT
        c.idCandidat,
        c.nom,
        c.prenom,
        c.email,
        c.codeUnique,
        c.matricule_ancien,
        p.idPreinscription,
        p.statut AS statut_actuel
    FROM candidat c
    INNER JOIN preinscription p
        ON p.idCandidat = c.idCandidat
    WHERE c.codeUnique = :codeUnique
    AND (p.type_demande = 'Inscription' OR p.type_demande IS NULL)
    ORDER BY p.idPreinscription DESC
    LIMIT 1
");

$requete->execute([
    ':codeUnique' => $codeUnique
]);

$candidat = $requete->fetch(PDO::FETCH_ASSOC);


// Vérifier que le candidat existe
if (!$candidat) {
    header("Location: dashboard.php");
    exit();
}


$nomComplet = trim($candidat['prenom'] . ' ' . $candidat['nom']);

$statutActuel = $candidat['statut_actuel'];

$aDejaUnNumero = trim((string) ($candidat['matricule_ancien'] ?? '')) !== '';


// ==========================================
// RÈGLES SUR LES DÉCISIONS DÉJÀ PRISES
// ==========================================

if ($action === 'refuser') {

    // Une candidature validée ne peut plus être refusée
    if ($statutActuel === 'Validée') {
        terminer(
            'erreur',
            'Action impossible',
            "La candidature de $nomComplet est déjà validée : "
                . "elle ne peut plus être refusée.",
            $section
        );
    }

    if ($statutActuel === 'Rejetée') {
        terminer(
            'info',
            'Déjà refusée',
            "La candidature de $nomComplet est déjà refusée.",
            $section
        );
    }
}

if ($action === 'accepter' && $statutActuel === 'Validée') {
    terminer(
        'info',
        'Déjà validée',
        "La candidature de $nomComplet est déjà validée.",
        $section
    );
}

if (in_array($action, ['accepter_inscription', 'refuser_inscription'], true)) {

    // La candidature doit d'abord avoir été acceptée
    if ($statutActuel !== 'Validée') {
        terminer(
            'erreur',
            'Action impossible',
            "La candidature de $nomComplet doit d'abord être acceptée.",
            $section
        );
    }

    // Le numéro étudiant n'est attribué qu'une seule fois
    if ($aDejaUnNumero) {
        terminer(
            'info',
            'Déjà inscrit(e)',
            "$nomComplet est déjà inscrit(e) "
                . "(N° étudiant : " . $candidat['matricule_ancien'] . ").",
            $section
        );
    }
}


// ==========================================
// EFFACER UNE CANDIDATURE REFUSÉE
// ==========================================

if ($action === 'effacer') {

    // Seule une candidature refusée peut être effacée
    if ($statutActuel !== 'Rejetée') {
        terminer(
            'erreur',
            'Action impossible',
            "Seule une candidature refusée peut être effacée.",
            $section
        );
    }

    // Un étudiant déjà inscrit (numéro attribué) ne s'efface pas
    if ($aDejaUnNumero) {
        terminer(
            'erreur',
            'Action impossible',
            "$nomComplet a déjà un numéro étudiant : sa candidature ne peut pas être effacée.",
            $section
        );
    }

    $cheminsFichiers = [];

    try {

        $connexion->beginTransaction();

        // Toutes les demandes de ce candidat
        $requeteIds = $connexion->prepare("
            SELECT idPreinscription
            FROM preinscription
            WHERE idCandidat = :idCandidat
        ");

        $requeteIds->execute([':idCandidat' => $candidat['idCandidat']]);

        $idsPreinscriptions = $requeteIds->fetchAll(PDO::FETCH_COLUMN);

        $marques = implode(',', array_fill(0, count($idsPreinscriptions), '?'));

        // Documents : retrouver les fichiers à supprimer sur le disque
        $conditionDocuments = "codeUnique = ?";

        if (!empty($idsPreinscriptions)) {
            $conditionDocuments .= " OR idPreinscription IN ($marques)";
        }

        $parametresDocuments = array_merge(
            [$candidat['codeUnique']],
            $idsPreinscriptions
        );

        $requeteChemins = $connexion->prepare(
            "SELECT chemin_fichier FROM document WHERE $conditionDocuments"
        );

        $requeteChemins->execute($parametresDocuments);

        $cheminsFichiers = $requeteChemins->fetchAll(PDO::FETCH_COLUMN);

        // Notifications (emails) liées à ces demandes
        if (!empty($idsPreinscriptions)) {

            $suppressionNotifications = $connexion->prepare(
                "DELETE FROM notification WHERE id_preinscription IN ($marques)"
            );

            $suppressionNotifications->execute($idsPreinscriptions);
        }

        // Documents
        $suppressionDocuments = $connexion->prepare(
            "DELETE FROM document WHERE $conditionDocuments"
        );

        $suppressionDocuments->execute($parametresDocuments);

        // Demandes (préinscriptions)
        $suppressionPreinscriptions = $connexion->prepare(
            "DELETE FROM preinscription WHERE idCandidat = :idCandidat"
        );

        $suppressionPreinscriptions->execute([
            ':idCandidat' => $candidat['idCandidat']
        ]);

        // Candidat
        $suppressionCandidat = $connexion->prepare(
            "DELETE FROM candidat WHERE idCandidat = :idCandidat"
        );

        $suppressionCandidat->execute([
            ':idCandidat' => $candidat['idCandidat']
        ]);

        $connexion->commit();

    } catch (Throwable $e) {

        if ($connexion->inTransaction()) {
            $connexion->rollBack();
        }

        error_log("Traitement HEMIP (effacer) : " . $e->getMessage());

        terminer(
            'erreur',
            'Suppression impossible',
            "La candidature de $nomComplet n'a pas pu être effacée "
                . "(elle est peut-être liée à d'autres données). Rien n'a été modifié.",
            $section
        );
    }

    // La base est à jour : on supprime aussi les fichiers déposés
    $dossierUploads = realpath(__DIR__ . '/uploads');

    foreach ($cheminsFichiers as $cheminFichier) {

        $fichierReel = realpath(__DIR__ . '/' . $cheminFichier);

        if (
            $dossierUploads !== false &&
            $fichierReel !== false &&
            strpos($fichierReel, $dossierUploads . DIRECTORY_SEPARATOR) === 0 &&
            is_file($fichierReel)
        ) {
            @unlink($fichierReel);
        }
    }

    terminer(
        'succes',
        'Candidature effacée',
        "La candidature de $nomComplet a été effacée définitivement "
            . "(dossier et documents).",
        $section
    );
}


// ==========================================
// PRÉPARER LA DÉCISION
// ==========================================

$numeroEtudiant = null;

if ($action === 'accepter') {

    // La candidature est acceptée, mais PAS encore le numéro étudiant :
    // la demande d'inscription passe « en attente » dans Préinscriptions.
    $statut = 'Validée';
    $typeNotification = 'Validation';

    $message =
        'Félicitations ! Votre candidature a été acceptée par HEMIP. '
        . 'Votre demande d’inscription est maintenant en cours de traitement : '
        . 'vous recevrez votre numéro étudiant dès que votre inscription '
        . 'sera confirmée.';

    $sujet = 'HEMIP - Votre candidature a été acceptée';

} elseif ($action === 'refuser') {

    $statut = 'Rejetée';
    $typeNotification = 'Rejet';

    $message =
        'Nous vous informons que votre candidature à HEMIP '
        . 'n’a pas été retenue. Nous vous remercions pour votre candidature.';

    $sujet = 'HEMIP - Résultat de votre candidature';

} elseif ($action === 'accepter_inscription') {

    $statut = 'Validée';
    $typeNotification = 'Validation';

    // Générer un numéro étudiant qui n'existe pas déjà
    $verifNumero = $connexion->prepare("
        SELECT COUNT(*)
        FROM candidat
        WHERE matricule_ancien = :numero
    ");

    for ($essai = 0; $essai < 10; $essai++) {

        $numeroEtudiant =
            'HEMIP-' .
            date('Y') .
            '-' .
            strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));

        $verifNumero->execute([':numero' => $numeroEtudiant]);

        if ((int) $verifNumero->fetchColumn() === 0) {
            break;
        }

        $numeroEtudiant = null;
    }

    if ($numeroEtudiant === null) {
        terminer(
            'erreur',
            'Erreur',
            "Impossible de générer un numéro étudiant. Veuillez réessayer.",
            $section
        );
    }

    $message =
        'Félicitations ! Votre inscription à HEMIP est confirmée. '
        . 'Votre numéro étudiant est : ' . $numeroEtudiant . '. '
        . 'Conservez-le précieusement : il vous sera demandé pour vos '
        . 'réinscriptions.';

    $sujet = 'HEMIP - Votre inscription est confirmée';

} else { // refuser_inscription

    // La demande est refusée : la candidature repasse en « Rejetée »
    $statut = 'Rejetée';
    $typeNotification = 'Rejet';

    $message =
        'Nous vous informons que votre demande d’inscription à HEMIP '
        . 'n’a pas pu être acceptée. Pour plus d’informations, '
        . 'vous pouvez contacter l’établissement.';

    $sujet = 'HEMIP - Votre demande d’inscription';
}


// ==========================================
// ENREGISTRER (tout ou rien)
// ==========================================

try {

    $connexion->beginTransaction();

    // Numéro étudiant (uniquement quand l'inscription est acceptée)
    if ($numeroEtudiant !== null) {

        $requeteNumero = $connexion->prepare("
            UPDATE candidat
            SET matricule_ancien = :matricule_ancien
            WHERE idCandidat = :idCandidat
        ");

        $requeteNumero->execute([
            ':matricule_ancien' => $numeroEtudiant,
            ':idCandidat' => $candidat['idCandidat']
        ]);
    }

    // Statut de la demande
    $requeteStatut = $connexion->prepare("
        UPDATE preinscription
        SET statut = :statut
        WHERE idPreinscription = :idPreinscription
    ");

    $requeteStatut->execute([
        ':statut' => $statut,
        ':idPreinscription' => $candidat['idPreinscription']
    ]);

    // Notification
    $requeteNotification = $connexion->prepare("
        INSERT INTO notification
        (
            type_notification,
            message,
            canal,
            id_preinscription
        )
        VALUES
        (
            :type_notification,
            :message,
            :canal,
            :id_preinscription
        )
    ");

    $requeteNotification->execute([
        ':type_notification' => $typeNotification,
        ':message' => $message,
        ':canal' => 'Email',
        ':id_preinscription' => $candidat['idPreinscription']
    ]);

    $connexion->commit();

} catch (Throwable $e) {

    if ($connexion->inTransaction()) {
        $connexion->rollBack();
    }

    error_log("Traitement HEMIP : " . $e->getMessage());

    terminer(
        'erreur',
        'Erreur',
        "Une erreur est survenue : rien n'a été modifié. Veuillez réessayer.",
        $section
    );
}


// ==========================================
// ENVOYER L'EMAIL
// ==========================================

require_once "mail.php";

$erreurEmail = null;

$emailEnvoye = envoyer_email(
    $candidat['email'],
    $sujet,
    $message,
    $erreurEmail
);

$noteEmail = $emailEnvoye
    ? " Un email lui a été envoyé."
    : " L'email n'a pas pu être envoyé : " . $erreurEmail;


// ==========================================
// MESSAGE DE RÉSULTAT POUR LE TABLEAU DE BORD
// ==========================================

if ($action === 'accepter') {

    terminer(
        'succes',
        'Candidature acceptée',
        "$nomComplet est admis(e). Sa demande d'inscription est maintenant "
            . "en attente dans Préinscriptions." . $noteEmail,
        $section
    );

} elseif ($action === 'refuser') {

    terminer(
        'rejet',
        'Candidature refusée',
        "La candidature de $nomComplet a été refusée." . $noteEmail,
        $section
    );

} elseif ($action === 'accepter_inscription') {

    terminer(
        'succes',
        'Inscription acceptée',
        "$nomComplet est inscrit(e). N° étudiant : $numeroEtudiant."
            . $noteEmail,
        $section
    );

} else {

    terminer(
        'rejet',
        'Inscription refusée',
        "La demande d'inscription de $nomComplet a été refusée. "
            . "Sa candidature repasse en « Rejetée »." . $noteEmail,
        $section
    );
}

?>