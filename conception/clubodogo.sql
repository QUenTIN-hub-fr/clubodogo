-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:8889
-- Généré le : mer. 16 sep. 2026 à 09:07
-- Version du serveur : 8.0.44
-- Version de PHP : 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `clubodogo`
--

-- --------------------------------------------------------

--
-- Structure de la table `chien`
--

CREATE TABLE `chien` (
  `id` int NOT NULL,
  `nom_chien` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_naissance` date NOT NULL,
  `sexe` enum('male','femelle') COLLATE utf8mb4_unicode_ci NOT NULL,
  `num_puce` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_utilisateur` int NOT NULL,
  `id_race` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `chien`
--

INSERT INTO `chien` (`id`, `nom_chien`, `date_naissance`, `sexe`, `num_puce`, `id_utilisateur`, `id_race`) VALUES
(1, 'Rex', '2024-03-15', 'male', '250269801234567', 4, 1),
(2, 'Luna', '2025-06-20', 'femelle', '250269801234568', 4, 2),
(3, 'Nala', '2023-01-10', 'femelle', '250269801234569', 5, 3),
(4, 'Tyson', '2026-02-05', 'male', '250269801234570', 5, 5);

-- --------------------------------------------------------

--
-- Structure de la table `cours`
--

CREATE TABLE `cours` (
  `id` int NOT NULL,
  `titre` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `capacite_max` int NOT NULL,
  `age_min_mois` int NOT NULL,
  `age_max_mois` int DEFAULT NULL,
  `date_cours` date NOT NULL,
  `heure_debut` time NOT NULL,
  `heure_fin` time NOT NULL,
  `id_utilisateur` int NOT NULL,
  `id_type_cours` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `cours`
--

INSERT INTO `cours` (`id`, `titre`, `description`, `capacite_max`, `age_min_mois`, `age_max_mois`, `date_cours`, `heure_debut`, `heure_fin`, `id_utilisateur`, `id_type_cours`) VALUES
(1, 'Sociabilisation debutant', 'Premiere approche du groupe pour les chiens peu habitues aux autres.', 8, 6, 36, '2026-09-26', '10:00:00', '11:30:00', 2, 1),
(2, 'Dressage niveau 1', 'Apprentissage des ordres de base : assis, couche, rappel.', 6, 12, NULL, '2026-09-27', '14:00:00', '15:30:00', 2, 2),
(3, 'Parcours sportif agility', 'Parcours d obstacles pour chiens sportifs et endurants.', 5, 18, NULL, '2026-10-03', '09:00:00', '11:00:00', 3, 3),
(4, 'Education chiot', 'Seance dediee aux tres jeunes chiens, proprete et premiers reperes.', 10, 2, 8, '2026-10-04', '10:00:00', '11:00:00', 3, 4);

-- --------------------------------------------------------

--
-- Structure de la table `inscription`
--

CREATE TABLE `inscription` (
  `id` int NOT NULL,
  `date_inscription` date NOT NULL,
  `statut` enum('en_attente','confirme','annule') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'en_attente',
  `id_cours` int NOT NULL,
  `id_chien` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `inscription`
--

INSERT INTO `inscription` (`id`, `date_inscription`, `statut`, `id_cours`, `id_chien`) VALUES
(1, '2026-09-16', 'confirme', 1, 1),
(2, '2026-09-16', 'confirme', 2, 1),
(3, '2026-09-16', 'en_attente', 1, 3);

-- --------------------------------------------------------

--
-- Structure de la table `race`
--

CREATE TABLE `race` (
  `id` int NOT NULL,
  `libelle_race` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `race`
--

INSERT INTO `race` (`id`, `libelle_race`) VALUES
(1, 'Berger allemand'),
(2, 'Border collie'),
(3, 'Labrador'),
(4, 'Golden retriever'),
(5, 'Jack russell'),
(6, 'Malinois'),
(7, 'Croise');

-- --------------------------------------------------------

--
-- Structure de la table `type_cours`
--

CREATE TABLE `type_cours` (
  `id` int NOT NULL,
  `libelle_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `type_cours`
--

INSERT INTO `type_cours` (`id`, `libelle_type`) VALUES
(1, 'Sociabilisation'),
(2, 'Dressage'),
(3, 'Parcours sportif'),
(4, 'Education chiot');

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

CREATE TABLE `utilisateur` (
  `id` int NOT NULL,
  `nom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prenom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('responsable','proprietaire','coach') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'proprietaire'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `utilisateur`
--

INSERT INTO `utilisateur` (`id`, `nom`, `prenom`, `email`, `password`, `role`) VALUES
(1, 'Martin', 'Sophie', 'responsable@clubodogo.fr', '$2y$12$Qw8WvJ1H6nT9Xk3rLzYuEeVqK5sM2pN7bC4dF6gH8jI0kL1mN2oPa', 'responsable'),
(2, 'Durand', 'Lucas', 'coach.lucas@clubodogo.fr', '$2y$12$Qw8WvJ1H6nT9Xk3rLzYuEeVqK5sM2pN7bC4dF6gH8jI0kL1mN2oPa', 'coach'),
(3, 'Petit', 'Emma', 'coach.emma@clubodogo.fr', '$2y$12$Qw8WvJ1H6nT9Xk3rLzYuEeVqK5sM2pN7bC4dF6gH8jI0kL1mN2oPa', 'coach'),
(4, 'Bernard', 'Thomas', 'thomas.bernard@email.fr', '$2y$12$Qw8WvJ1H6nT9Xk3rLzYuEeVqK5sM2pN7bC4dF6gH8jI0kL1mN2oPa', 'proprietaire'),
(5, 'Leroy', 'Julie', 'julie.leroy@email.fr', '$2y$12$Qw8WvJ1H6nT9Xk3rLzYuEeVqK5sM2pN7bC4dF6gH8jI0kL1mN2oPa', 'proprietaire');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `chien`
--
ALTER TABLE `chien`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `num_puce` (`num_puce`),
  ADD KEY `id_utilisateur` (`id_utilisateur`),
  ADD KEY `id_race` (`id_race`);

--
-- Index pour la table `cours`
--
ALTER TABLE `cours`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_utilisateur` (`id_utilisateur`),
  ADD KEY `id_type_cours` (`id_type_cours`);

--
-- Index pour la table `inscription`
--
ALTER TABLE `inscription`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_cours` (`id_cours`,`id_chien`),
  ADD KEY `id_chien` (`id_chien`);

--
-- Index pour la table `race`
--
ALTER TABLE `race`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `type_cours`
--
ALTER TABLE `type_cours`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `chien`
--
ALTER TABLE `chien`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `cours`
--
ALTER TABLE `cours`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `inscription`
--
ALTER TABLE `inscription`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `race`
--
ALTER TABLE `race`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `type_cours`
--
ALTER TABLE `type_cours`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `chien`
--
ALTER TABLE `chien`
  ADD CONSTRAINT `chien_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id`),
  ADD CONSTRAINT `chien_ibfk_2` FOREIGN KEY (`id_race`) REFERENCES `race` (`id`);

--
-- Contraintes pour la table `cours`
--
ALTER TABLE `cours`
  ADD CONSTRAINT `cours_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id`),
  ADD CONSTRAINT `cours_ibfk_2` FOREIGN KEY (`id_type_cours`) REFERENCES `type_cours` (`id`);

--
-- Contraintes pour la table `inscription`
--
ALTER TABLE `inscription`
  ADD CONSTRAINT `inscription_ibfk_1` FOREIGN KEY (`id_cours`) REFERENCES `cours` (`id`),
  ADD CONSTRAINT `inscription_ibfk_2` FOREIGN KEY (`id_chien`) REFERENCES `chien` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
