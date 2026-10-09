<?php
/**
 * Réglages d'envoi des emails du site HEMIP.
 *
 * Choisissez UNE des options ci-dessous en modifiant les valeurs
 * de la section « RÉGLAGES ACTIFS ».
 *
 * ---------------------------------------------------------------
 * OPTION A : Mailpit (test sur votre ordinateur, rien n'est envoyé
 *            pour de vrai, vous lisez les emails dans le navigateur)
 *
 *   1. Lancez mailpit.exe (fenêtre noire à laisser ouverte)
 *   2. Ouvrez http://localhost:8025 pour voir les emails reçus
 *   3. Réglages :  hote = localhost, port = 1025,
 *                  securite = '', utilisateur = '', mot_de_passe = ''
 *
 * OPTION B : Gmail (les candidats reçoivent de vrais emails)
 *
 *   1. Sur votre compte Google : activez la validation en 2 étapes
 *   2. Créez un « mot de passe d'application » (16 caractères)
 *      (Compte Google > Sécurité > Mots de passe des applications)
 *   3. Réglages :  hote = smtp.gmail.com, port = 587, securite = 'tls',
 *                  utilisateur = votre adresse Gmail,
 *                  mot_de_passe = le mot de passe d'application,
 *                  expediteur_email = votre adresse Gmail
 *
 * Après toute modification, testez avec la page test-email.php
 * ---------------------------------------------------------------
 */

$config = [

    // ===== RÉGLAGES PAR DÉFAUT (sans secret) =====

    // true  = envoi par SMTP (recommandé)
    // false = ancienne méthode mail() de PHP (non fiable sous XAMPP)
    'smtp' => true,

    'hote' => 'localhost',
    'port' => 1025,

    // '' = aucune, 'tls' = STARTTLS (port 587), 'ssl' = SSL (port 465)
    'securite' => '',

    'utilisateur' => '',
    'mot_de_passe' => '',

    // Adresse qui apparaît comme expéditeur.
    'expediteur_email' => 'noreply@hemip.com',
    'expediteur_nom' => 'HEMIP',

    // Mettez false seulement si vous voyez l'erreur
    // « certificate verify failed » sous XAMPP
    'verifier_certificat' => true

];


/* =============================================================
   ATTENTION — AUCUN SECRET DANS CE FICHIER
   Ce fichier est versionné dans Git. Les identifiants réels se
   placent dans config-mail.local.php (ignoré par Git) ou dans des
   variables d'environnement. Voir l'en-tête ci-dessus.
   ============================================================= */


/* -------------------------------------------------------------
   1) Variables d'environnement (priorité moyenne)
   ------------------------------------------------------------- */

$variablesEnvironnement = [
    'smtp'                => 'HEMIP_SMTP',
    'hote'                => 'HEMIP_SMTP_HOTE',
    'port'                => 'HEMIP_SMTP_PORT',
    'securite'            => 'HEMIP_SMTP_SECURITE',
    'utilisateur'         => 'HEMIP_SMTP_UTILISATEUR',
    'mot_de_passe'        => 'HEMIP_SMTP_MOT_DE_PASSE',
    'expediteur_email'    => 'HEMIP_SMTP_EXPEDITEUR',
    'expediteur_nom'      => 'HEMIP_SMTP_EXPEDITEUR_NOM',
    'verifier_certificat' => 'HEMIP_SMTP_VERIFIER_CERTIFICAT'
];

foreach ($variablesEnvironnement as $cle => $nomVariable) {

    $valeur = getenv($nomVariable);

    if ($valeur === false || $valeur === '') {
        continue;
    }

    if ($cle === 'smtp' || $cle === 'verifier_certificat') {
        $config[$cle] = filter_var($valeur, FILTER_VALIDATE_BOOLEAN);
    } elseif ($cle === 'port') {
        $config[$cle] = (int) $valeur;
    } else {
        $config[$cle] = $valeur;
    }
}


/* -------------------------------------------------------------
   2) Fichier local (priorité la plus haute, ignoré par Git)
   ------------------------------------------------------------- */

$fichierLocal = __DIR__ . '/config-mail.local.php';

if (is_file($fichierLocal)) {

    $configLocale = require $fichierLocal;

    if (is_array($configLocale)) {
        $config = array_merge($config, $configLocale);
    }
}

return $config;
