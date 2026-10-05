<?php
/**
 * Historique des notifications du tableau de bord.
 *
 * Ce sont les messages qui s'affichent en haut à droite de l'écran
 * (« Candidature validée », « Inscription refusée », etc.), et non les
 * emails envoyés aux candidats.
 *
 * La table est créée automatiquement au premier usage.
 * Si votre utilisateur MySQL n'a pas le droit de créer des tables,
 * exécutez une fois ce SQL dans phpMyAdmin :
 *
 *   CREATE TABLE IF NOT EXISTS historique_notification (
 *       idHistorique  INT AUTO_INCREMENT PRIMARY KEY,
 *       idAdmin       INT NULL,
 *       type          VARCHAR(20)  NOT NULL,
 *       titre         VARCHAR(150) NOT NULL,
 *       message       TEXT         NOT NULL,
 *       date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
 *       INDEX (idAdmin)
 *   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
 */


/**
 * Crée la table de l'historique si elle n'existe pas encore.
 */
function historique_assurer_table(PDO $connexion): bool
{
    if (!empty($_SESSION['historique_table_ok'])) {
        return true;
    }

    try {

        $connexion->exec("
            CREATE TABLE IF NOT EXISTS historique_notification (
                idHistorique  INT AUTO_INCREMENT PRIMARY KEY,
                idAdmin       INT NULL,
                type          VARCHAR(20)  NOT NULL,
                titre         VARCHAR(150) NOT NULL,
                message       TEXT         NOT NULL,
                date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX (idAdmin)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        $_SESSION['historique_table_ok'] = true;

        return true;

    } catch (Throwable $e) {

        error_log("Historique HEMIP (création de la table) : " . $e->getMessage());

        return false;
    }
}


/**
 * Ajoute une notification à l'historique.
 * Ne bloque jamais l'application en cas de problème.
 */
function historique_ajouter(
    PDO $connexion,
    ?int $idAdmin,
    string $type,
    string $titre,
    string $message
): void {

    if (!historique_assurer_table($connexion)) {
        return;
    }

    try {

        $requete = $connexion->prepare("
            INSERT INTO historique_notification
                (idAdmin, type, titre, message)
            VALUES
                (:idAdmin, :type, :titre, :message)
        ");

        $requete->execute([
            ':idAdmin' => $idAdmin,
            ':type' => substr($type, 0, 20),
            ':titre' => substr($titre, 0, 150),
            ':message' => $message
        ]);

    } catch (Throwable $e) {

        error_log("Historique HEMIP (ajout) : " . $e->getMessage());
    }
}


/**
 * Lit les dernières notifications d'un administrateur (les plus récentes d'abord).
 */
function historique_lire(PDO $connexion, int $idAdmin, int $limite = 50): array
{
    if (!historique_assurer_table($connexion)) {
        return [];
    }

    try {

        $requete = $connexion->prepare("
            SELECT type, titre, message, date_creation
            FROM historique_notification
            WHERE idAdmin = :idAdmin
            ORDER BY idHistorique DESC
            LIMIT " . (int) $limite
        );

        $requete->execute([':idAdmin' => $idAdmin]);

        return $requete->fetchAll(PDO::FETCH_ASSOC);

    } catch (Throwable $e) {

        error_log("Historique HEMIP (lecture) : " . $e->getMessage());

        return [];
    }
}
