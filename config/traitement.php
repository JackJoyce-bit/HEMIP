<?php

session_start();

require_once "connexion.php";


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


// Déterminer le nouveau statut
if ($action === 'accepter') {

    $statut = 'Validée';
    $typeNotification = 'Validation';

    $message =
        'Félicitations ! Votre candidature a été acceptée par HEMIP. '
        . 'Vous êtes désormais admis(e) au sein de l’établissement.';

} elseif ($action === 'refuser') {

    $statut = 'Rejetée';
    $typeNotification = 'Rejet';

    $message =
        'Nous vous informons que votre candidature à HEMIP '
        . 'n’a pas été retenue. Nous vous remercions pour votre candidature.';

} else {

    header("Location: dashboard.php");
    exit();
}


// Rechercher le candidat
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


// ==========================================
// RÈGLES SUR LES DÉCISIONS DÉJÀ PRISES
// ==========================================

// Une candidature validée ne peut plus être refusée
if ($action === 'refuser' && $candidat['statut_actuel'] === 'Validée') {

    $_SESSION['flash'] = [
        'type' => 'erreur',
        'titre' => 'Action impossible',
        'message' => "La candidature de $nomComplet est déjà validée : "
            . "elle ne peut plus être refusée."
    ];

    header("Location: dashboard.php");
    exit();
}

// Inutile de valider deux fois (et de régénérer un matricule)
if ($action === 'accepter' && $candidat['statut_actuel'] === 'Validée') {

    $_SESSION['flash'] = [
        'type' => 'info',
        'titre' => 'Déjà validée',
        'message' => "La candidature de $nomComplet est déjà validée."
    ];

    header("Location: dashboard.php");
    exit();
}

// Inutile de refuser deux fois
if ($action === 'refuser' && $candidat['statut_actuel'] === 'Rejetée') {

    $_SESSION['flash'] = [
        'type' => 'info',
        'titre' => 'Déjà refusée',
        'message' => "La candidature de $nomComplet est déjà refusée."
    ];

    header("Location: dashboard.php");
    exit();
}


// ==========================================
// SI LA CANDIDATURE EST ACCEPTÉE
// ==========================================

if ($action === 'accepter') {

    // Générer le matricule étudiant
    $matriculeAncien =
        'HEMIP-' .
        date('Y') .
        '-' .
        strtoupper(
            substr(
                bin2hex(random_bytes(4)),
                0,
                6
            )
        );


    // Enregistrer le matricule dans candidat
    $requeteMatricule = $connexion->prepare("
        UPDATE candidat
        SET matricule_ancien = :matricule_ancien
        WHERE idCandidat = :idCandidat
    ");

    $requeteMatricule->execute([
        ':matricule_ancien' => $matriculeAncien,
        ':idCandidat' => $candidat['idCandidat']
    ]);
}


// ==========================================
// MODIFIER LE STATUT DE LA CANDIDATURE
// ==========================================

$requeteStatut = $connexion->prepare("
    UPDATE preinscription
    SET statut = :statut
    WHERE idPreinscription = :idPreinscription
");

$requeteStatut->execute([
    ':statut' => $statut,
    ':idPreinscription' => $candidat['idPreinscription']
]);


// ==========================================
// CRÉER LA NOTIFICATION
// ==========================================

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


// ==========================================
// ENVOYER L'EMAIL
// ==========================================

$sujet = 'HEMIP - Résultat de votre candidature';

$headers = "From: HEMIP <noreply@hemip.com>\r\n";
$headers .= "Reply-To: noreply@hemip.com\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

// Le @ évite qu'un avertissement PHP n'empêche la redirection
$emailEnvoye = @mail(
    $candidat['email'],
    $sujet,
    $message,
    $headers
);

$noteEmail = $emailEnvoye
    ? " Un email lui a été envoyé."
    : " L'email n'a pas pu être envoyé.";


// ==========================================
// MESSAGE DE RÉSULTAT POUR LE TABLEAU DE BORD
// ==========================================

if ($action === 'accepter') {

    $_SESSION['flash'] = [
        'type' => 'succes',
        'titre' => 'Candidature validée',
        'message' => "$nomComplet est admis(e). Matricule : $matriculeAncien."
            . $noteEmail
    ];

} else {

    $_SESSION['flash'] = [
        'type' => 'rejet',
        'titre' => 'Candidature refusée',
        'message' => "La candidature de $nomComplet a été refusée."
            . $noteEmail
    ];
}


// Retour au tableau de bord
header("Location: dashboard.php");
exit();

?>