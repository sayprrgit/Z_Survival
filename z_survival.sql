-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : ven. 20 nov. 2026 à 10:29
-- Version du serveur : 8.4.7
-- Version de PHP : 8.5.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `z_survival`
--

-- --------------------------------------------------------

--
-- Structure de la table `characters`
--

DROP TABLE IF EXISTS `characters`;
CREATE TABLE IF NOT EXISTS `characters` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` longtext NOT NULL,
  `base_health` int NOT NULL,
  `code` varchar(255) NOT NULL,
  `passive_description` longtext NOT NULL,
  `damage_bonus` double NOT NULL DEFAULT '0',
  `attack_speed_bonus` double NOT NULL DEFAULT '0',
  `movement_speed_bonus` double NOT NULL DEFAULT '0',
  `critical_chance_bonus` double NOT NULL DEFAULT '0',
  `damage_reduction` double NOT NULL DEFAULT '0',
  `health_regeneration` double NOT NULL DEFAULT '0',
  `available_by_default` tinyint NOT NULL DEFAULT '0',
  `unlock_cost` int NOT NULL DEFAULT '0',
  `unlock_description` longtext,
  `portrait_path` varchar(255) DEFAULT NULL,
  `starting_weapon_id` int NOT NULL,
  `healing_effectiveness_bonus` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_3A29410E77153098` (`code`),
  KEY `IDX_3A29410E933D23AD` (`starting_weapon_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `characters`
--

INSERT INTO `characters` (`id`, `name`, `description`, `base_health`, `code`, `passive_description`, `damage_bonus`, `attack_speed_bonus`, `movement_speed_bonus`, `critical_chance_bonus`, `damage_reduction`, `health_regeneration`, `available_by_default`, `unlock_cost`, `unlock_description`, `portrait_path`, `starting_weapon_id`, `healing_effectiveness_bonus`) VALUES
(1, 'Mason', 'Un soldat entraîné, adapté à toutes les situations.', 120, 'mason', '+15 % de dégâts et +10 % de cadence de tir.', 0.15, 0.1, 0, 0, 0, 0, 1, 0, NULL, NULL, 2, 0),
(2, 'Raven', 'Une éclaireuse rapide qui mise sur les coups critiques.', 80, 'raven', '+25 % de vitesse et +15 points de chance critique.', 0, 0, 0.25, 0.15, 0, 0, 0, 250, 'Personnage déblocable avec de la ferraille (coût indicatif MVP).', NULL, 1, 0),
(3, 'Boris', 'Un mécanicien robuste qui encaisse les attaques.', 180, 'boris', '-20 % de vitesse et -20 % de dégâts subis.', 0, 0, -0.2, 0, 0.2, 0, 0, 350, 'Coût de déblocage indicatif, à équilibrer.', NULL, 4, 0),
(4, 'Eva', 'Une médecin capable de récupérer et de renforcer les soins.', 100, 'eva', 'Régénération naturelle et +25 % d’efficacité des soins.', 0, 0, 0, 0, 0, 0.2, 0, 350, 'Coût de déblocage indicatif, à équilibrer.', NULL, 5, 0.25);

-- --------------------------------------------------------

--
-- Structure de la table `difficulties`
--

DROP TABLE IF EXISTS `difficulties`;
CREATE TABLE IF NOT EXISTS `difficulties` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` longtext NOT NULL,
  `code` varchar(255) NOT NULL,
  `multiplier` double NOT NULL,
  `sort_order` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_8892266A77153098` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `difficulties`
--

INSERT INTO `difficulties` (`id`, `name`, `description`, `code`, `multiplier`, `sort_order`) VALUES
(1, 'Normal', 'Le mode de difficulté de départ.', 'normal', 1, 1),
(2, 'Difficile', 'Mode de difficulté du jeu.', 'hard', 1.3, 2),
(3, 'Cauchemar', 'Mode de difficulté du jeu.', 'nightmare', 1.6, 3);

-- --------------------------------------------------------

--
-- Structure de la table `doctrine_migration_versions`
--

DROP TABLE IF EXISTS `doctrine_migration_versions`;
CREATE TABLE IF NOT EXISTS `doctrine_migration_versions` (
  `version` varchar(191) NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int DEFAULT NULL,
  PRIMARY KEY (`version`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `doctrine_migration_versions`
--

INSERT INTO `doctrine_migration_versions` (`version`, `executed_at`, `execution_time`) VALUES
('DoctrineMigrations\\Version20261119072711', '2026-11-19 07:27:37', 1786),
('DoctrineMigrations\\Version20261119084349', '2026-11-19 08:44:14', 52),
('DoctrineMigrations\\Version20261119091022', '2026-11-19 09:11:04', 60);

-- --------------------------------------------------------

--
-- Structure de la table `enemy_types`
--

DROP TABLE IF EXISTS `enemy_types`;
CREATE TABLE IF NOT EXISTS `enemy_types` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` longtext NOT NULL,
  `base_health` int NOT NULL,
  `code` varchar(255) NOT NULL,
  `is_boss` tinyint NOT NULL DEFAULT '0',
  `base_damage` double NOT NULL,
  `movement_speed` double NOT NULL,
  `attack_range` double NOT NULL,
  `attack_interval` double NOT NULL,
  `spawn_weight` int NOT NULL DEFAULT '1',
  `available_from_seconds` int NOT NULL DEFAULT '0',
  `experience_reward` int NOT NULL DEFAULT '0',
  `scrap_drop_chance` double NOT NULL DEFAULT '0',
  `minimum_scrap_drop` int NOT NULL DEFAULT '0',
  `maximum_scrap_drop` int NOT NULL DEFAULT '0',
  `sprite_path` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_C30A9F2377153098` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `enemy_types`
--

INSERT INTO `enemy_types` (`id`, `name`, `description`, `base_health`, `code`, `is_boss`, `base_damage`, `movement_speed`, `attack_range`, `attack_interval`, `spawn_weight`, `available_from_seconds`, `experience_reward`, `scrap_drop_chance`, `minimum_scrap_drop`, `maximum_scrap_drop`, `sprite_path`) VALUES
(1, 'Walker', 'Un zombie lent qui attaque au contact.', 25, 'walker', 0, 8, 32, 24, 1.4, 10, 0, 5, 0.08, 1, 2, NULL),
(2, 'Runner', 'Un zombie rapide et fragile.', 15, 'runner', 0, 7, 68, 22, 1, 5, 120, 7, 0.1, 1, 3, NULL),
(3, 'Brute', 'Un zombie massif, lent et résistant.', 150, 'brute', 0, 24, 20, 28, 1.8, 2, 240, 20, 0.25, 3, 8, NULL),
(4, 'Le Colosse', 'Un boss qui charge et frappe dans une large zone.', 1000, 'colossus', 1, 35, 38, 42, 1.6, 0, 300, 100, 0, 0, 0, NULL),
(5, 'Spitter', 'Un zombie qui attaque à distance.', 50, 'spitter', 0, 12, 22, 180, 2.2, 2, 420, 12, 0.12, 2, 5, NULL),
(6, 'Patient Zéro', 'Le boss final invoque des zombies et lance des attaques renforcées.', 4000, 'patient_zero', 1, 40, 30, 220, 1.8, 0, 900, 250, 0, 0, 0, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `messenger_messages`
--

DROP TABLE IF EXISTS `messenger_messages`;
CREATE TABLE IF NOT EXISTS `messenger_messages` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `body` longtext NOT NULL,
  `headers` longtext NOT NULL,
  `queue_name` varchar(190) NOT NULL,
  `created_at` datetime NOT NULL,
  `available_at` datetime NOT NULL,
  `delivered_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750` (`queue_name`,`available_at`,`delivered_at`,`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `power_ups`
--

DROP TABLE IF EXISTS `power_ups`;
CREATE TABLE IF NOT EXISTS `power_ups` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` longtext NOT NULL,
  `max_level` int NOT NULL DEFAULT '5',
  `code` varchar(255) NOT NULL,
  `effect_type` varchar(50) NOT NULL,
  `bonus_per_level` double NOT NULL,
  `icon_path` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_CAE24AB077153098` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `power_ups`
--

INSERT INTO `power_ups` (`id`, `name`, `description`, `max_level`, `code`, `effect_type`, `bonus_per_level`, `icon_path`) VALUES
(1, 'Munitions renforcées', '+10 % de dégâts par niveau.', 5, 'reinforced_ammo', 'damage_bonus', 0.1, NULL),
(2, 'Réflexes', '+8 % de cadence par niveau.', 5, 'reflexes', 'attack_speed_bonus', 0.08, NULL),
(3, 'Gilet renforcé', '+15 PV maximum par niveau.', 5, 'reinforced_vest', 'max_health', 15, NULL),
(4, 'Adrénaline', '+5 % de vitesse par niveau.', 5, 'adrenaline', 'movement_speed_bonus', 0.05, NULL),
(5, 'Précision', '+5 points de chance critique par niveau.', 5, 'precision', 'critical_chance_bonus', 0.05, NULL),
(6, 'Régénération', '+0,3 PV/s par niveau.', 5, 'regeneration', 'health_regeneration', 0.3, NULL),
(7, 'Aimant', '+15 % de portée de collecte par niveau.', 5, 'magnet', 'pickup_range_bonus', 0.15, NULL),
(8, 'Instinct de survie', '+10 % d’expérience par niveau.', 5, 'survival_instinct', 'experience_bonus', 0.1, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `runs`
--

DROP TABLE IF EXISTS `runs`;
CREATE TABLE IF NOT EXISTS `runs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `started_at` datetime NOT NULL,
  `finished_at` datetime DEFAULT NULL,
  `survival_seconds` int NOT NULL DEFAULT '0',
  `reached_level` int NOT NULL DEFAULT '1',
  `kills` int NOT NULL DEFAULT '0',
  `scrap_earned` int NOT NULL DEFAULT '0',
  `result` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL,
  `rewards_granted_at` datetime DEFAULT NULL,
  `user_id` int NOT NULL,
  `character_id` int NOT NULL,
  `difficulty_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_803A7B1FA76ED395` (`user_id`),
  KEY `IDX_803A7B1F1136BE75` (`character_id`),
  KEY `IDX_803A7B1FFCFA9DAE` (`difficulty_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `talents`
--

DROP TABLE IF EXISTS `talents`;
CREATE TABLE IF NOT EXISTS `talents` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` longtext NOT NULL,
  `code` varchar(100) NOT NULL,
  `branch` varchar(255) NOT NULL,
  `tier` int NOT NULL,
  `cost` int NOT NULL,
  `effect_type` varchar(50) NOT NULL,
  `effect_value` double NOT NULL,
  `parent_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_D6CF109A77153098` (`code`),
  KEY `IDX_D6CF109A727ACA70` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `talents`
--

INSERT INTO `talents` (`id`, `name`, `description`, `code`, `branch`, `tier`, `cost`, `effect_type`, `effect_value`, `parent_id`) VALUES
(1, 'Endurance', '+10 PV de base.', 'endurance', 'survival', 1, 100, 'max_health', 10, NULL),
(2, 'Armure', '-3 % de dégâts subis.', 'armor', 'survival', 2, 175, 'damage_reduction', 0.03, 1),
(3, 'Guérison', '+0,2 PV/s.', 'healing', 'survival', 3, 250, 'health_regeneration', 0.2, 2),
(4, 'Puissance', '+5 % de dégâts.', 'power', 'combat', 1, 100, 'damage_bonus', 0.05, NULL),
(5, 'Cadence', '+5 % de cadence.', 'cadence', 'combat', 2, 175, 'attack_speed_bonus', 0.05, 4),
(6, 'Critique', '+3 points de chance critique.', 'critical', 'combat', 3, 250, 'critical_chance_bonus', 0.03, 5),
(7, 'Agilité', '+5 % de vitesse.', 'agility', 'utility', 1, 100, 'movement_speed_bonus', 0.05, NULL),
(8, 'Collecte', '+10 % de portée de collecte.', 'collection', 'utility', 2, 175, 'pickup_range_bonus', 0.1, 7),
(9, 'Chance', '+5 % de probabilité de butin.', 'luck', 'utility', 3, 250, 'loot_chance_bonus', 0.05, 8),
(10, 'Résistance', 'Réduction supplémentaire des dégâts (valeur indicative).', 'resistance', 'survival', 4, 325, 'damage_reduction', 0.05, 3),
(11, 'Survivant', '+25 PV de base.', 'survivor', 'survival', 5, 400, 'max_health', 25, 10),
(12, 'Perforation', '+10 % de dégâts.', 'perforation', 'combat', 4, 325, 'damage_bonus', 0.1, 6),
(13, 'Exécuteur', '+15 % de dégâts.', 'executor', 'combat', 5, 400, 'damage_bonus', 0.15, 12),
(14, 'Expérience', '+10 % d’expérience.', 'experience', 'utility', 4, 325, 'experience_bonus', 0.1, 9),
(15, 'Pillard', '+20 % de ferraille.', 'raider', 'utility', 5, 400, 'scrap_bonus', 0.2, 14);

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(180) NOT NULL,
  `roles` json NOT NULL,
  `password` varchar(255) NOT NULL,
  `scrap` int NOT NULL DEFAULT '0',
  `total_runs` int NOT NULL DEFAULT '0',
  `total_victories` int NOT NULL DEFAULT '0',
  `total_survival_seconds` int NOT NULL DEFAULT '0',
  `best_survival_seconds` int NOT NULL DEFAULT '0',
  `total_kills` int NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL,
  `last_login_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_1483A5E9E7927C74` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `user_characters`
--

DROP TABLE IF EXISTS `user_characters`;
CREATE TABLE IF NOT EXISTS `user_characters` (
  `id` int NOT NULL AUTO_INCREMENT,
  `acquired_at` datetime NOT NULL,
  `user_id` int NOT NULL,
  `character_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_user_character` (`user_id`,`character_id`),
  KEY `IDX_9D4E87E2A76ED395` (`user_id`),
  KEY `IDX_9D4E87E21136BE75` (`character_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `user_talents`
--

DROP TABLE IF EXISTS `user_talents`;
CREATE TABLE IF NOT EXISTS `user_talents` (
  `id` int NOT NULL AUTO_INCREMENT,
  `acquired_at` datetime NOT NULL,
  `user_id` int NOT NULL,
  `talent_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_user_talent` (`user_id`,`talent_id`),
  KEY `IDX_8EC20E90A76ED395` (`user_id`),
  KEY `IDX_8EC20E9018777CEF` (`talent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `weapons`
--

DROP TABLE IF EXISTS `weapons`;
CREATE TABLE IF NOT EXISTS `weapons` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` longtext NOT NULL,
  `max_level` int NOT NULL DEFAULT '5',
  `code` varchar(255) NOT NULL,
  `attack_type` varchar(30) NOT NULL,
  `base_damage` double NOT NULL,
  `attack_interval` double NOT NULL,
  `attack_range` double NOT NULL DEFAULT '0',
  `projectile_count` int NOT NULL DEFAULT '1',
  `pierce_count` int NOT NULL DEFAULT '0',
  `area_of_effect` double NOT NULL DEFAULT '0',
  `max_upgrade_description` longtext NOT NULL,
  `sprite_path` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_520EBBE177153098` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `weapons`
--

INSERT INTO `weapons` (`id`, `name`, `description`, `max_level`, `code`, `attack_type`, `base_damage`, `attack_interval`, `attack_range`, `projectile_count`, `pierce_count`, `area_of_effect`, `max_upgrade_description`, `sprite_path`) VALUES
(1, 'Pistolet', 'Une arme de poing fiable pour les courtes et moyennes distances.', 5, 'pistol', 'targeted', 15, 0.8, 380, 1, 0, 0, 'Tire un projectile supplémentaire.', NULL),
(2, 'Fusil d’assaut', 'Une arme automatique polyvalente.', 5, 'assault_rifle', 'rapid', 10, 0.3, 440, 1, 0, 0, 'Les projectiles perforent les ennemis.', NULL),
(3, 'Fusil à pompe', 'Tire plusieurs plombs en éventail à courte portée.', 5, 'shotgun', 'spread', 40, 1.5, 260, 5, 0, 0, 'Élargit la dispersion.', NULL),
(4, 'Masse', 'Une arme lourde qui frappe les ennemis autour du survivant.', 5, 'mace', 'melee_area', 35, 1.8, 120, 1, 0, 120, 'Déclenche une onde de choc.', NULL),
(5, 'Couteau', 'Une arme rapide de très courte portée.', 5, 'knife', 'melee', 12, 0.5, 90, 1, 0, 0, 'Les attaques appliquent un saignement.', NULL),
(6, 'Molotov', 'Enflamme une zone et inflige des dégâts persistants.', 5, 'molotov', 'area_damage', 8, 4, 320, 1, 0, 150, 'Agrandit la zone de feu.', NULL),
(7, 'Sniper', 'Très puissant et perforant, mais lent.', 5, 'sniper', 'targeted', 120, 2.4, 720, 1, 4, 0, 'Perfore davantage.', NULL),
(8, 'Lance-roquettes', 'Explosion dévastatrice, un seul tir et rechargement long.', 5, 'rocket_launcher', 'area_damage', 210, 5.5, 500, 1, 0, 220, 'Zone explosive renforcée.', NULL),
(9, 'Minigun', 'Cadence extrême et énorme chargeur, très gourmande en munitions.', 5, 'minigun', 'rapid', 14, 0.075, 480, 1, 1, 0, 'Tirs perforants.', NULL);

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `characters`
--
ALTER TABLE `characters`
  ADD CONSTRAINT `FK_3A29410E933D23AD` FOREIGN KEY (`starting_weapon_id`) REFERENCES `weapons` (`id`) ON DELETE RESTRICT;

--
-- Contraintes pour la table `runs`
--
ALTER TABLE `runs`
  ADD CONSTRAINT `FK_803A7B1F1136BE75` FOREIGN KEY (`character_id`) REFERENCES `characters` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `FK_803A7B1FA76ED395` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_803A7B1FFCFA9DAE` FOREIGN KEY (`difficulty_id`) REFERENCES `difficulties` (`id`) ON DELETE RESTRICT;

--
-- Contraintes pour la table `talents`
--
ALTER TABLE `talents`
  ADD CONSTRAINT `FK_D6CF109A727ACA70` FOREIGN KEY (`parent_id`) REFERENCES `talents` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `user_characters`
--
ALTER TABLE `user_characters`
  ADD CONSTRAINT `FK_9D4E87E21136BE75` FOREIGN KEY (`character_id`) REFERENCES `characters` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_9D4E87E2A76ED395` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `user_talents`
--
ALTER TABLE `user_talents`
  ADD CONSTRAINT `FK_8EC20E9018777CEF` FOREIGN KEY (`talent_id`) REFERENCES `talents` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_8EC20E90A76ED395` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
