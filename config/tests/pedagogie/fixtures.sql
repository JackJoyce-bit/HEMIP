-- Fixtures synthétiques UNIQUEMENT pour config/tests/pedagogie/migration-test.php.
-- À exécuter dans une base vide dont le nom commence par hemip_test_.

CREATE TABLE `administrateur` (
  `idAdmin` INT(11) NOT NULL AUTO_INCREMENT,
  `nom` VARCHAR(100) NOT NULL,
  `prenom` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `motdepasse` VARCHAR(255) NOT NULL,
  `role` ENUM('SuperAdmin','Administrateur') NOT NULL DEFAULT 'Administrateur',
  `date_creation` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idAdmin`),
  UNIQUE KEY `uq_fixture_administrateur_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `filiere` (
  `idFiliere` INT(11) NOT NULL AUTO_INCREMENT,
  `code_filiere` VARCHAR(20) NOT NULL,
  `nomFiliere` VARCHAR(150) NOT NULL,
  `description` TEXT NOT NULL,
  PRIMARY KEY (`idFiliere`),
  UNIQUE KEY `uq_fixture_filiere_code` (`code_filiere`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `administrateur` (`idAdmin`,`nom`,`prenom`,`email`,`motdepasse`,`role`) VALUES
  (901,'FIXTURE','SuperAdmin','superadmin.fixture@example.invalid','fixture-not-a-real-password-hash','SuperAdmin'),
  (902,'FIXTURE','Administrateur','admin.fixture@example.invalid','fixture-not-a-real-password-hash','Administrateur');

INSERT INTO `filiere` (`idFiliere`,`code_filiere`,`nomFiliere`,`description`) VALUES
  (901,'TST','Filiere de test','Donnée synthétique réservée aux tests pédagogiques.');
