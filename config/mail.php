<?php
/**
 * Envoi des emails du site HEMIP.
 *
 * Utilisation :
 *     require_once "mail.php";
 *     $erreur = null;
 *     $ok = envoyer_email("candidat@exemple.com", "Sujet", "Message", $erreur);
 *
 * Chaque envoi (réussi ou non) est noté dans logs/emails.log,
 * avec le texte du message : pratique pour vérifier ce qui part.
 */


/**
 * Lit les réglages de config-mail.php (avec des valeurs par défaut).
 */
function config_email(): array
{
    $defaut = [
        'smtp' => true,
        'hote' => 'localhost',
        'port' => 1025,
        'securite' => '',
        'utilisateur' => '',
        'mot_de_passe' => '',
        'expediteur_email' => 'noreply@hemip.com',
        'expediteur_nom' => 'HEMIP',
        'verifier_certificat' => true
    ];

    $fichier = __DIR__ . '/config-mail.php';

    if (is_file($fichier)) {

        $config = require $fichier;

        if (is_array($config)) {
            return array_merge($defaut, $config);
        }
    }

    return $defaut;
}


/**
 * Enregistre l'envoi dans logs/emails.log
 */
function journaliser_email(
    bool $reussi,
    string $destinataire,
    string $sujet,
    string $message,
    ?string $erreur,
    ?string $reponseServeur = null
): void {

    $dossier = __DIR__ . '/logs';

    if (!is_dir($dossier)) {
        @mkdir($dossier, 0775, true);
    }

    // Le journal ne doit pas être lisible depuis Internet
    $protection = $dossier . '/.htaccess';

    if (!is_file($protection)) {
        @file_put_contents($protection, "Require all denied\n");
    }

    $ligne =
        '[' . date('Y-m-d H:i:s') . '] '
        . ($reussi ? 'ENVOYÉ' : 'ÉCHEC')
        . ' | à : ' . $destinataire
        . ' | sujet : ' . $sujet . "\n";

    if (!$reussi && $erreur) {
        $ligne .= 'Erreur : ' . $erreur . "\n";
    }

    if ($reussi && $reponseServeur) {
        $ligne .= 'Réponse du serveur mail : ' . $reponseServeur . "\n";
    }

    $ligne .= $message . "\n" . str_repeat('-', 60) . "\n";

    @file_put_contents($dossier . '/emails.log', $ligne, FILE_APPEND | LOCK_EX);
}


/**
 * Cherche un fichier de certificats pour vérifier la connexion sécurisée
 * (sous XAMPP, PHP n'en trouve souvent pas tout seul).
 */
function trouver_cafile(): ?string
{
    $ini = (string) ini_get('openssl.cafile');

    if ($ini !== '' && @is_file($ini)) {
        return null; // PHP utilisera déjà ce fichier
    }

    $dossiersPhp = [];

    $phpIni = php_ini_loaded_file();

    if ($phpIni) {
        $dossiersPhp[] = dirname($phpIni);
    }

    $extensions = (string) ini_get('extension_dir');

    if ($extensions !== '') {
        $dossiersPhp[] = dirname($extensions);
    }

    $candidats = [];

    $curl = (string) ini_get('curl.cainfo');

    if ($curl !== '') {
        $candidats[] = $curl;
    }

    foreach ($dossiersPhp as $dossier) {
        $candidats[] = $dossier . '/extras/ssl/cacert.pem';
        $candidats[] = dirname($dossier) . '/apache/bin/curl-ca-bundle.crt';
    }

    $candidats[] = 'C:/xampp/php/extras/ssl/cacert.pem';
    $candidats[] = 'C:/xampp/apache/bin/curl-ca-bundle.crt';
    $candidats[] = '/etc/ssl/certs/ca-certificates.crt';
    $candidats[] = '/etc/pki/tls/certs/ca-bundle.crt';

    foreach ($candidats as $fichier) {

        if (@is_file($fichier)) {
            return $fichier;
        }
    }

    return null;
}


/**
 * Traduit les refus fréquents du serveur mail (Gmail surtout) en conseil clair.
 */
function conseil_smtp(int $code, string $reponse): string
{
    $texte = strtolower($reponse);

    if (
        $code === 534 ||
        strpos($texte, 'application-specific') !== false ||
        strpos($texte, 'web login') !== false
    ) {
        return " Conseil : Google bloque cette connexion. Ouvrez ce compte Gmail dans un navigateur,"
            . " confirmez l'alerte de sécurité (« C'était bien moi ») puis réessayez."
            . " Un mot de passe d'application est obligatoire.";
    }

    if (
        $code === 535 ||
        strpos($texte, '5.7.8') !== false ||
        strpos($texte, 'username and password not accepted') !== false
    ) {
        return " Conseil : Gmail refuse l'identifiant. Vérifiez que la validation en 2 étapes est activée"
            . " sur ce compte, que le mot de passe d'application a été créé pour CE compte (et non pour"
            . " l'ancien), et que 'utilisateur' est la bonne adresse. Au besoin, créez un nouveau mot de"
            . " passe d'application.";
    }

    if ($code === 550 || $code === 553 || $code === 554) {
        return " Conseil : le serveur refuse l'adresse ou le message. Vérifiez l'adresse du destinataire"
            . " et que 'expediteur_email' est bien l'adresse du compte connecté.";
    }

    return '';
}


/**
 * Lit une réponse du serveur SMTP (peut tenir sur plusieurs lignes).
 * Retourne [code, texte].
 */
function smtp_lire($flux): array
{
    $reponse = '';

    while (($ligne = fgets($flux, 515)) !== false) {

        $reponse .= $ligne;

        // Dernière ligne de la réponse : le 4e caractère est un espace
        if (strlen($ligne) < 4 || $ligne[3] === ' ') {
            break;
        }
    }

    if ($reponse === '') {
        throw new RuntimeException(
            "Le serveur mail n'a pas répondu (délai dépassé ou connexion fermée)."
        );
    }

    return [(int) substr($reponse, 0, 3), trim($reponse)];
}


/**
 * Envoie une commande SMTP et vérifie la réponse.
 * $libelle sert dans le message d'erreur (jamais le mot de passe).
 */
function smtp_commande($flux, string $commande, array $codesAcceptes, string $libelle): string
{
    fwrite($flux, $commande . "\r\n");

    [$code, $reponse] = smtp_lire($flux);

    if (!in_array($code, $codesAcceptes, true)) {
        throw new RuntimeException(
            "Le serveur mail a refusé l'étape « $libelle » : $reponse"
            . conseil_smtp($code, $reponse)
        );
    }

    return $reponse;
}


/**
 * Envoi par SMTP (sans bibliothèque externe).
 * Retourne la dernière réponse du serveur (preuve que le message est accepté).
 * Lance une exception en cas de problème.
 */
function smtp_envoyer(array $config, string $destinataire, string $sujet, string $message): string
{
    $hote = $config['hote'];
    $port = (int) $config['port'];
    $securite = strtolower((string) $config['securite']);
    $verifier = !empty($config['verifier_certificat']);

    $optionsSsl = [
        'verify_peer' => $verifier,
        'verify_peer_name' => $verifier,
        'allow_self_signed' => !$verifier,
        'peer_name' => $hote,
        'SNI_enabled' => true
    ];

    $cafile = $verifier ? trouver_cafile() : null;

    if ($cafile !== null) {
        $optionsSsl['cafile'] = $cafile;
    }

    $contexte = stream_context_create(['ssl' => $optionsSsl]);

    // Un mot de passe d'application Gmail s'écrit sans espaces
    $utilisateur = trim((string) $config['utilisateur']);
    $motDePasse = (string) $config['mot_de_passe'];

    if (stripos($hote, 'gmail') !== false || stripos($hote, 'google') !== false) {
        $motDePasse = preg_replace('/\s+/', '', $motDePasse);
    }

    $cible = ($securite === 'ssl' ? 'ssl://' : 'tcp://') . $hote . ':' . $port;

    $numero = 0;
    $texte = '';

    $flux = @stream_socket_client(
        $cible,
        $numero,
        $texte,
        15,
        STREAM_CLIENT_CONNECT,
        $contexte
    );

    if (!$flux) {

        $aide = '';

        if ($hote === 'localhost' || $hote === '127.0.0.1') {
            $aide = " Mailpit est-il lancé (fenêtre noire mailpit.exe ouverte) ?";
        } else {
            $aide = " Vérifiez la connexion Internet et que le pare-feu ou l'antivirus"
                . " autorise le port $port (vous pouvez aussi essayer le port 465 avec"
                . " 'securite' => 'ssl').";
        }

        throw new RuntimeException(
            "Connexion impossible à $hote:$port ($texte)." . $aide
        );
    }

    stream_set_timeout($flux, 15);

    try {

        [$code, $accueil] = smtp_lire($flux);

        if ($code !== 220) {
            throw new RuntimeException("Réponse inattendue du serveur mail : $accueil");
        }

        smtp_commande($flux, 'EHLO localhost', [250], 'EHLO');

        // Connexion chiffrée STARTTLS (port 587)
        if ($securite === 'tls') {

            smtp_commande($flux, 'STARTTLS', [220], 'STARTTLS');

            $methode = STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;

            if (defined('STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT')) {
                $methode |= STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT;
            }

            error_clear_last();

            if (!@stream_socket_enable_crypto($flux, true, $methode)) {

                $derniere = error_get_last();
                $detail = $derniere['message'] ?? '';

                $aide = $verifier
                    ? " Si l'erreur parle de « certificate », mettez 'verifier_certificat' => false dans config-mail.php."
                    : '';

                throw new RuntimeException(
                    "Impossible de sécuriser la connexion (TLS)."
                    . ($detail !== '' ? " Détail : $detail." : '')
                    . $aide
                );
            }

            smtp_commande($flux, 'EHLO localhost', [250], 'EHLO');
        }

        // Identification
        if ($utilisateur !== '') {

            smtp_commande($flux, 'AUTH LOGIN', [334], 'AUTH LOGIN');

            smtp_commande(
                $flux,
                base64_encode($utilisateur),
                [334],
                'nom d\'utilisateur'
            );

            smtp_commande(
                $flux,
                base64_encode($motDePasse),
                [235],
                'mot de passe (vérifiez l\'utilisateur et le mot de passe d\'application)'
            );
        }

        $expediteur = $config['expediteur_email'];

        smtp_commande($flux, 'MAIL FROM:<' . $expediteur . '>', [250], 'MAIL FROM');
        smtp_commande($flux, 'RCPT TO:<' . $destinataire . '>', [250, 251], 'RCPT TO');
        smtp_commande($flux, 'DATA', [354], 'DATA');

        $domaine = substr(strrchr($expediteur, '@') ?: '@localhost', 1);

        $nomExpediteur = '=?UTF-8?B?' . base64_encode($config['expediteur_nom']) . '?=';
        $sujetEncode = '=?UTF-8?B?' . base64_encode($sujet) . '?=';

        $entetes =
            'Date: ' . date('r') . "\r\n"
            . 'From: ' . $nomExpediteur . ' <' . $expediteur . ">\r\n"
            . 'To: <' . $destinataire . ">\r\n"
            . 'Subject: ' . $sujetEncode . "\r\n"
            . 'Message-ID: <' . uniqid('', true) . '@' . $domaine . ">\r\n"
            . "MIME-Version: 1.0\r\n"
            . "Content-Type: text/plain; charset=UTF-8\r\n"
            . "Content-Transfer-Encoding: base64\r\n";

        $corps = chunk_split(base64_encode($message), 76, "\r\n");

        fwrite($flux, $entetes . "\r\n" . $corps . ".\r\n");

        [$code, $reponse] = smtp_lire($flux);

        if ($code !== 250) {
            throw new RuntimeException("Le serveur mail a refusé le message : $reponse");
        }

        @fwrite($flux, "QUIT\r\n");

        return $reponse;

    } finally {

        fclose($flux);
    }
}


/**
 * Envoie un email. Retourne true si le serveur mail a accepté le message.
 * En cas d'échec, $erreur contient la raison.
 */
function envoyer_email(
    string $destinataire,
    string $sujet,
    string $message,
    ?string &$erreur = null,
    ?string &$reponseServeur = null
): bool {

    $erreur = null;
    $reponseServeur = null;

    $config = config_email();

    $destinataire = trim($destinataire);
    $sujet = str_replace(["\r", "\n"], ' ', $sujet);

    try {

        if (!filter_var($destinataire, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException("Adresse email invalide : « $destinataire ».");
        }

        if (!empty($config['smtp'])) {

            $reponseServeur = smtp_envoyer($config, $destinataire, $sujet, $message);

        } else {

            $entetes =
                'From: ' . $config['expediteur_nom']
                . ' <' . $config['expediteur_email'] . ">\r\n"
                . "Content-Type: text/plain; charset=UTF-8\r\n";

            if (!@mail($destinataire, $sujet, $message, $entetes)) {
                throw new RuntimeException(
                    "La fonction mail() de PHP a échoué (elle n'est pas configurée sous XAMPP). "
                    . "Utilisez plutôt SMTP dans config-mail.php."
                );
            }
        }

        journaliser_email(true, $destinataire, $sujet, $message, null, $reponseServeur);

        return true;

    } catch (Throwable $e) {

        $erreur = $e->getMessage();

        journaliser_email(false, $destinataire, $sujet, $message, $erreur);

        return false;
    }
}