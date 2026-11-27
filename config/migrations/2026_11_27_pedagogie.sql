-- HEMIP — migration initiale du modèle pédagogique
-- Prérequis : schéma existant contenant administrateur et filiere.
-- À exécuter une seule fois sur une base dédiée ; ne pas appliquer au dump historique.

ALTER TABLE `administrateur`
  MODIFY COLUMN `role` ENUM('SuperAdmin','Administrateur','SAC','DAAC')
  NOT NULL DEFAULT 'Administrateur';

CREATE TABLE `enseignant` (
  `id_enseignant` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nom` VARCHAR(100) NOT NULL,
  `prenom` VARCHAR(100) NOT NULL,
  `email` VARCHAR(254) NOT NULL,
  `motdepasse` VARCHAR(255) NOT NULL,
  `telephone` VARCHAR(20) DEFAULT NULL,
  `photo` VARCHAR(255) DEFAULT NULL,
  `actif` TINYINT(1) NOT NULL DEFAULT 1,
  `force_changement_mdp` TINYINT(1) NOT NULL DEFAULT 0,
  `date_creation` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_modification` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_enseignant`),
  UNIQUE KEY `uq_enseignant_email` (`email`),
  KEY `idx_enseignant_nom_prenom` (`nom`,`prenom`),
  CONSTRAINT `chk_enseignant_force_changement_mdp` CHECK (`force_changement_mdp` IN (0,1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `classe_pedagogique` (
  `id_classe` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_filiere` INT(11) NOT NULL,
  `niveau` ENUM('L1','L2','L3','Master 1','Master 2') NOT NULL,
  `annee_academique` CHAR(9) NOT NULL,
  `date_creation` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_classe`),
  UNIQUE KEY `uq_classe_filiere_niveau_annee` (`id_filiere`,`niveau`,`annee_academique`),
  UNIQUE KEY `uq_classe_id_filiere_niveau` (`id_classe`,`id_filiere`,`niveau`),
  KEY `idx_classe_annee_niveau` (`annee_academique`,`niveau`),
  CONSTRAINT `chk_classe_niveau`
    CHECK (`niveau` IN ('L1','L2','L3','Master 1','Master 2')),
  CONSTRAINT `chk_classe_annee_format`
    CHECK (`annee_academique` REGEXP '^[0-9]{4}-[0-9]{4}$'),
  CONSTRAINT `fk_classe_filiere`
    FOREIGN KEY (`id_filiere`) REFERENCES `filiere` (`idFiliere`)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `module_pedagogique` (
  `id_module` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_filiere` INT(11) NOT NULL,
  `niveau` ENUM('L1','L2','L3','Master 1','Master 2') NOT NULL,
  `nom_module` VARCHAR(150) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `actif` TINYINT(1) NOT NULL DEFAULT 1,
  `date_creation` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_module`),
  UNIQUE KEY `uq_module_filiere_niveau_nom` (`id_filiere`,`niveau`,`nom_module`),
  UNIQUE KEY `uq_module_id_filiere_niveau` (`id_module`,`id_filiere`,`niveau`),
  KEY `idx_module_filiere_niveau_actif` (`id_filiere`,`niveau`,`actif`),
  CONSTRAINT `chk_module_niveau`
    CHECK (`niveau` IN ('L1','L2','L3','Master 1','Master 2')),
  CONSTRAINT `fk_module_filiere`
    FOREIGN KEY (`id_filiere`) REFERENCES `filiere` (`idFiliere`)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `affectation_enseignant` (
  `id_affectation` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_enseignant` INT UNSIGNED NOT NULL,
  `id_classe` INT UNSIGNED NOT NULL,
  `id_module` INT UNSIGNED NOT NULL,
  `id_filiere` INT(11) NOT NULL,
  `niveau` ENUM('L1','L2','L3','Master 1','Master 2') NOT NULL,
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  `date_creation` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_affectation`),
  UNIQUE KEY `uq_affectation_enseignant_classe_module` (`id_enseignant`,`id_classe`,`id_module`),
  KEY `idx_affectation_classe_module_active` (`id_classe`,`id_module`,`active`),
  KEY `idx_affectation_module_niveau` (`id_module`,`id_filiere`,`niveau`),
  CONSTRAINT `chk_affectation_niveau`
    CHECK (`niveau` IN ('L1','L2','L3','Master 1','Master 2')),
  CONSTRAINT `fk_affectation_enseignant`
    FOREIGN KEY (`id_enseignant`) REFERENCES `enseignant` (`id_enseignant`)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_affectation_classe_compatible`
    FOREIGN KEY (`id_classe`,`id_filiere`,`niveau`)
    REFERENCES `classe_pedagogique` (`id_classe`,`id_filiere`,`niveau`)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_affectation_module_compatible`
    FOREIGN KEY (`id_module`,`id_filiere`,`niveau`)
    REFERENCES `module_pedagogique` (`id_module`,`id_filiere`,`niveau`)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
