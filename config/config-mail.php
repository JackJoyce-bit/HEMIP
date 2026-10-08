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

return [

    // ===== RÉGLAGES ACTIFS =====

    // true  = envoi par SMTP (recommandé)
    // false = ancienne méthode mail() de PHP (non fiable sous XAMPP)
    'smtp' => true,

    'hote' => 'smtp.gmail.com',
    'port' => 587,

    // '' = aucune, 'tls' = STARTTLS (port 587), 'ssl' = SSL (port 465)
    'securite' => 'tls',

    'utilisateur' => 'joycemondza@gmail.com',
    'mot_de_passe' => 'tlml tfrg selr etko',

    // Adresse qui apparaît comme expéditeur.
    // Avec Gmail, mettez la même adresse que 'utilisateur'.
    'expediteur_email' => 'joycemondza@gmail.com',
    'expediteur_nom' => 'HEMIP',

    // Mettez false seulement si vous voyez l'erreur
    // « certificate verify failed » sous XAMPP
    'verifier_certificat' => true

];
