<?php
/**
 * Réglages SMTP réels du site HEMIP — FICHIER LOCAL, NON VERSIONNÉ.
 *
 * Ce fichier contient des identifiants sensibles. Il est ignoré par Git
 * (voir .gitignore) : il ne doit jamais être envoyé sur un dépôt public.
 *
 * Il surcharge les valeurs par défaut de config-mail.php.
 * Sur l'hébergement de production, remplacez-le par des variables
 * d'environnement (HEMIP_SMTP_UTILISATEUR, HEMIP_SMTP_MOT_DE_PASSE, ...)
 * ou par ce même fichier déposé à la main sur le serveur.
 */

return [

    'smtp' => true,

    'hote' => 'smtp.gmail.com',
    'port' => 587,
    'securite' => 'tls',

    'utilisateur' => 'joycemondza@gmail.com',
    'mot_de_passe' => 'tlml tfrg selr etko',

    'expediteur_email' => 'joycemondza@gmail.com',
    'expediteur_nom' => 'HEMIP',

    'verifier_certificat' => true

];
