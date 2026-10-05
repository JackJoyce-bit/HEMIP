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
    ?string $erreur
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

    $ligne .= $message . "\n" . str_repeat('-', 60) . "\n";

    @file_put_contents($dossier . '/emails.log', $ligne, FILE_APPEND | LOCK_EX);
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
        );
    }

    return $reponse;
}


/**
 * Envoi par SMTP (sans bibliothèque externe).
 * Lance une exception en cas de problème.
 */
function smtp_envoyer(array $config, string $destinataire, string $sujet, string $message): void
{
    $hote = $config['hote'];
    $port = (int) $config['port'];
    $securite = strtolower((string) $config['securite']);
    $verifier = !empty($config['verifier_certificat']);

    $contexte = stream_context_create([
        'ssl' => [
            'verify_peer' => $verifier,
            'verify_peer_name' => $verifier,
            'allow_self_signed' => !$verifier
        ]
    ]);

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

            if (!@stream_socket_enable_crypto($flux, true, $methode)) {

                $aide = $verifier
                    ? " Si l'erreur parle de « certificate », mettez 'verifier_certificat' => false dans config-mail.php."
                    : '';

                throw new RuntimeException(
                    "Impossible de sécuriser la connexion (TLS)." . $aide
                );
            }

            smtp_commande($flux, 'EHLO localhost', [250], 'EHLO');
        }

        // Identification
        if ($config['utilisateur'] !== '') {

            smtp_commande($flux, 'AUTH LOGIN', [334], 'AUTH LOGIN');

            smtp_commande(
                $flux,
                base64_encode($config['utilisateur']),
                [334],
                'nom d\'utilisateur'
            );

            smtp_commande(
                $flux,
                base64_encode($config['mot_de_passe']),
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
    ?string &$erreur = null
): bool {

    $erreur = null;

    $config = config_email();

    $destinataire = trim($destinataire);
    $sujet = str_replace(["\r", "\n"], ' ', $sujet);

    try {

        if (!filter_var($destinataire, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException("Adresse email invalide : « $destinataire ».");
        }

        if (!empty($config['smtp'])) {

            smtp_envoyer($config, $destinataire, $sujet, $message);

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

        journaliser_email(true, $destinataire, $sujet, $message, null);

        return true;

    } catch (Throwable $e) {

        $erreur = $e->getMessage();

        journaliser_email(false, $destinataire, $sujet, $message, $erreur);

        return false;
    }
}
