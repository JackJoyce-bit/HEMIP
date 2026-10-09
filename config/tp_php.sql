-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mer. 30 sep. 2026 à 12:23
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `tp_php`
--

-- --------------------------------------------------------

--
-- Structure de la table `actualite`
--

CREATE TABLE `actualite` (
  `idActualite` int(11) NOT NULL,
  `titre` varchar(255) NOT NULL,
  `contenu` text NOT NULL,
  `image` varchar(255) NOT NULL,
  `statut` enum('Publiée','Brouillon') NOT NULL DEFAULT 'Brouillon',
  `date_publication` timestamp NOT NULL DEFAULT current_timestamp(),
  `idAdmin` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `administrateur`
--

CREATE TABLE `administrateur` (
  `idAdmin` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `motdepasse` varchar(255) NOT NULL,
  `role` enum('SuperAdmin','Administrateur') NOT NULL DEFAULT 'Administrateur',
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `administrateur`
--

INSERT INTO `administrateur` (`idAdmin`, `nom`, `prenom`, `email`, `motdepasse`, `role`, `date_creation`) VALUES
(3, 'MONDZALI', 'Keith Laurent Joyce', 'joycemondza@gmail.com', '$2y$10$lZ9HdAYHSUGIeeF6pvXxoek/Og0iHohhVjcOcu/fqyOeabRdFbBfy', 'SuperAdmin', '2026-09-10 22:01:00'),
(4, 'MALOUMBI', 'Dora Divine', 'maloumbidora@gmail.com', '$2y$10$Y2mlBv9KDh4ijzNqGPq0p.tAul0NxupiRvyCpyDvNBJoLt69Vml4u', 'Administrateur', '2026-09-29 18:24:21');

-- --------------------------------------------------------

--
-- Structure de la table `candidat`
--

CREATE TABLE `candidat` (
  `idCandidat` int(11) NOT NULL,
  `codeUnique` varchar(50) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `date_naissance` date NOT NULL,
  `email` varchar(150) NOT NULL,
  `motdepasse` varchar(255) NOT NULL,
  `telephone` varchar(20) NOT NULL,
  `sexe` enum('Masculin','Féminin') NOT NULL,
  `adresse` varchar(255) NOT NULL,
  `matricule_ancien` varchar(50) NOT NULL,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `document`
--

CREATE TABLE `document` (
  `idDocument` int(11) NOT NULL,
  `nom_document` varchar(150) NOT NULL,
  `type_document` enum('Photo','Acte de naissance','Diplôme','Relevé de notes','Certificat médical','Assurance') NOT NULL,
  `chemin_fichier` varchar(255) NOT NULL,
  `date_upload` timestamp NOT NULL DEFAULT current_timestamp(),
  `codeUnique` varchar(50) NOT NULL,
  `idPreinscription` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `filiere`
--

CREATE TABLE `filiere` (
  `idFiliere` int(11) NOT NULL,
  `code_filiere` varchar(20) NOT NULL,
  `nomFiliere` varchar(150) NOT NULL,
  `description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `filiere`
--

INSERT INTO `filiere` (`idFiliere`, `code_filiere`, `nomFiliere`, `description`) VALUES
(1, 'GL', 'Génie Logiciel', 'Formation en génie logiciel'),
(2, 'GP', 'Génie Pétrolier', 'Formation en génie pétrolier'),
(3, 'RT', 'Réseaux et Télécommunications', 'Formation en réseaux et télécommunications'),
(4, 'MI', 'Maintenance Industrielle', 'Formation en maintenance industrielle'),
(5, 'AII', 'Automatisation et Informatique Industriel', 'Formation en automatisation et informatique industriel'),
(6, 'EE', 'Electrotechnique et Electronique', 'Formation en électrotechnique et électronique'),
(7, 'DAE', 'Droit des Affaires et des Entreprises', 'Formation en droit des affaires et des entreprises'),
(8, 'LT', 'Logistique et Transport', 'Formation en logistique et transport'),
(9, 'BFA', 'Banque et Finance des Assurances', 'Formation en banque et finance des assurances'),
(10, 'CIT', 'Commerce International et Transit', 'Formation en commerce international et transit'),
(11, 'GFC', 'Gestion Finance et Comptabilité', 'Formation en gestion finance et comptabilité'),
(12, 'MRH', 'Management des Ressources Humaines', 'Formation en management des ressources humaines');

-- --------------------------------------------------------

--
-- Structure de la table `message_contact`
--

CREATE TABLE `message_contact` (
  `idMessage` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `sujet` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `date_envoi` timestamp NOT NULL DEFAULT current_timestamp(),
  `idAdmin` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `notification`
--

CREATE TABLE `notification` (
  `id_notification` int(11) NOT NULL,
  `type_notification` enum('Réception','Validation','Rejet') NOT NULL,
  `message` text NOT NULL,
  `canal` enum('Email','SMS') NOT NULL,
  `date_envoi` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_preinscription` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `preinscription`
--

CREATE TABLE `preinscription` (
  `idPreinscription` int(11) NOT NULL,
  `date_demande` date NOT NULL,
  `annee_academique` varchar(9) NOT NULL,
  `type_demande` enum('Inscription','Réinscription') NOT NULL,
  `statut` enum('En attente','Validée','Rejetée') NOT NULL DEFAULT 'En attente',
  `idFiliere` int(11) NOT NULL,
  `idAdmin` int(11) DEFAULT NULL,
  `idCandidat` int(11) NOT NULL,
  `niveau` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `recepisse`
--

CREATE TABLE `recepisse` (
  `idRecepisse` int(11) NOT NULL,
  `numero_recepisse` varchar(50) NOT NULL,
  `date_generation` timestamp NOT NULL DEFAULT current_timestamp(),
  `idPreinscription` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `utilisateurs`
--

CREATE TABLE `utilisateurs` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `motdepasse` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `utilisateurs`
--

INSERT INTO `utilisateurs` (`id`, `nom`, `prenom`, `email`, `motdepasse`) VALUES
(7, 'MONDZALI', 'Keith Laurent Joyce', 'joycemondza@gmail.com', '');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `actualite`
--
ALTER TABLE `actualite`
  ADD PRIMARY KEY (`idActualite`),
  ADD KEY `fk_actualite_admin` (`idAdmin`);

--
-- Index pour la table `administrateur`
--
ALTER TABLE `administrateur`
  ADD PRIMARY KEY (`idAdmin`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `email_2` (`email`);

--
-- Index pour la table `candidat`
--
ALTER TABLE `candidat`
  ADD PRIMARY KEY (`idCandidat`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `telephone` (`telephone`),
  ADD UNIQUE KEY `codeUnique` (`codeUnique`);

--
-- Index pour la table `document`
--
ALTER TABLE `document`
  ADD PRIMARY KEY (`idDocument`),
  ADD KEY `fk_document_candidat` (`codeUnique`),
  ADD KEY `fk_document_preinscription` (`idPreinscription`);

--
-- Index pour la table `filiere`
--
ALTER TABLE `filiere`
  ADD PRIMARY KEY (`idFiliere`),
  ADD UNIQUE KEY `code_filiere` (`code_filiere`);

--
-- Index pour la table `message_contact`
--
ALTER TABLE `message_contact`
  ADD PRIMARY KEY (`idMessage`),
  ADD KEY `fk_message_admin` (`idAdmin`);

--
-- Index pour la table `notification`
--
ALTER TABLE `notification`
  ADD PRIMARY KEY (`id_notification`),
  ADD KEY `fk_notification_preinscription` (`id_preinscription`);

--
-- Index pour la table `preinscription`
--
ALTER TABLE `preinscription`
  ADD PRIMARY KEY (`idPreinscription`),
  ADD KEY `fk_preinscription_candidat` (`idCandidat`),
  ADD KEY `fk_preinscription_admin` (`idAdmin`),
  ADD KEY `fk_preinscription_filiere` (`idFiliere`);

--
-- Index pour la table `recepisse`
--
ALTER TABLE `recepisse`
  ADD PRIMARY KEY (`idRecepisse`),
  ADD UNIQUE KEY `numeroRecepisse` (`numero_recepisse`),
  ADD UNIQUE KEY `idPreinscription` (`idPreinscription`);

--
-- Index pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `actualite`
--
ALTER TABLE `actualite`
  MODIFY `idActualite` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `administrateur`
--
ALTER TABLE `administrateur`
  MODIFY `idAdmin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `candidat`
--
ALTER TABLE `candidat`
  MODIFY `idCandidat` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `document`
--
ALTER TABLE `document`
  MODIFY `idDocument` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `filiere`
--
ALTER TABLE `filiere`
  MODIFY `idFiliere` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT pour la table `message_contact`
--
ALTER TABLE `message_contact`
  MODIFY `idMessage` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `notification`
--
ALTER TABLE `notification`
  MODIFY `id_notification` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `preinscription`
--
ALTER TABLE `preinscription`
  MODIFY `idPreinscription` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `recepisse`
--
ALTER TABLE `recepisse`
  MODIFY `idRecepisse` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `actualite`
--
ALTER TABLE `actualite`
  ADD CONSTRAINT `fk_actualite_admin` FOREIGN KEY (`idAdmin`) REFERENCES `administrateur` (`idAdmin`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `document`
--
ALTER TABLE `document`
  ADD CONSTRAINT `fk_document_preinscription` FOREIGN KEY (`idPreinscription`) REFERENCES `preinscription` (`idPreinscription`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `message_contact`
--
ALTER TABLE `message_contact`
  ADD CONSTRAINT `fk_message_admin` FOREIGN KEY (`idAdmin`) REFERENCES `administrateur` (`idAdmin`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Contraintes pour la table `notification`
--
ALTER TABLE `notification`
  ADD CONSTRAINT `fk_notification_preinscription` FOREIGN KEY (`id_preinscription`) REFERENCES `preinscription` (`idPreinscription`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `preinscription`
--
ALTER TABLE `preinscription`
  ADD CONSTRAINT `fk_preinscription_admin` FOREIGN KEY (`idAdmin`) REFERENCES `administrateur` (`idAdmin`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_preinscription_candidat` FOREIGN KEY (`idCandidat`) REFERENCES `candidat` (`idCandidat`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_preinscription_filiere` FOREIGN KEY (`idFiliere`) REFERENCES `filiere` (`idFiliere`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `recepisse`
--
ALTER TABLE `recepisse`
  ADD CONSTRAINT `fk_recepisse_preinscription` FOREIGN KEY (`idPreinscription`) REFERENCES `preinscription` (`idPreinscription`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
