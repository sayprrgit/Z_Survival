<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261119072711 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE characters (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, description LONGTEXT NOT NULL, base_health INT NOT NULL, code VARCHAR(255) NOT NULL, passive_description LONGTEXT NOT NULL, damage_bonus DOUBLE PRECISION DEFAULT 0 NOT NULL, attack_speed_bonus DOUBLE PRECISION DEFAULT 0 NOT NULL, movement_speed_bonus DOUBLE PRECISION DEFAULT 0 NOT NULL, critical_chance_bonus DOUBLE PRECISION DEFAULT 0 NOT NULL, damage_reduction DOUBLE PRECISION DEFAULT 0 NOT NULL, health_regeneration DOUBLE PRECISION DEFAULT 0 NOT NULL, available_by_default TINYINT DEFAULT 0 NOT NULL, unlock_cost INT DEFAULT 0 NOT NULL, unlock_description LONGTEXT DEFAULT NULL, portrait_path VARCHAR(255) DEFAULT NULL, starting_weapon_id INT NOT NULL, UNIQUE INDEX UNIQ_3A29410E77153098 (code), INDEX IDX_3A29410E933D23AD (starting_weapon_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE difficulties (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, description LONGTEXT NOT NULL, code VARCHAR(255) NOT NULL, multiplier DOUBLE PRECISION NOT NULL, sort_order INT NOT NULL, UNIQUE INDEX UNIQ_8892266A77153098 (code), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE enemy_types (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, description LONGTEXT NOT NULL, base_health INT NOT NULL, code VARCHAR(255) NOT NULL, is_boss TINYINT DEFAULT 0 NOT NULL, base_damage DOUBLE PRECISION NOT NULL, movement_speed DOUBLE PRECISION NOT NULL, attack_range DOUBLE PRECISION NOT NULL, attack_interval DOUBLE PRECISION NOT NULL, spawn_weight INT DEFAULT 1 NOT NULL, available_from_seconds INT DEFAULT 0 NOT NULL, experience_reward INT DEFAULT 0 NOT NULL, scrap_drop_chance DOUBLE PRECISION DEFAULT 0 NOT NULL, minimum_scrap_drop INT DEFAULT 0 NOT NULL, maximum_scrap_drop INT DEFAULT 0 NOT NULL, sprite_path VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_C30A9F2377153098 (code), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE power_ups (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, description LONGTEXT NOT NULL, max_level INT DEFAULT 5 NOT NULL, code VARCHAR(255) NOT NULL, effect_type VARCHAR(50) NOT NULL, bonus_per_level DOUBLE PRECISION NOT NULL, icon_path VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_CAE24AB077153098 (code), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE runs (id INT AUTO_INCREMENT NOT NULL, started_at DATETIME NOT NULL, finished_at DATETIME DEFAULT NULL, survival_seconds INT DEFAULT 0 NOT NULL, reached_level INT DEFAULT 1 NOT NULL, kills INT DEFAULT 0 NOT NULL, scrap_earned INT DEFAULT 0 NOT NULL, result VARCHAR(255) DEFAULT NULL, status VARCHAR(255) NOT NULL, rewards_granted_at DATETIME DEFAULT NULL, user_id INT NOT NULL, character_id INT NOT NULL, difficulty_id INT NOT NULL, INDEX IDX_803A7B1FA76ED395 (user_id), INDEX IDX_803A7B1F1136BE75 (character_id), INDEX IDX_803A7B1FFCFA9DAE (difficulty_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE talents (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, description LONGTEXT NOT NULL, code VARCHAR(100) NOT NULL, branch VARCHAR(255) NOT NULL, tier INT NOT NULL, cost INT NOT NULL, effect_type VARCHAR(50) NOT NULL, effect_value DOUBLE PRECISION NOT NULL, parent_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_D6CF109A77153098 (code), INDEX IDX_D6CF109A727ACA70 (parent_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_characters (id INT AUTO_INCREMENT NOT NULL, acquired_at DATETIME NOT NULL, user_id INT NOT NULL, character_id INT NOT NULL, UNIQUE INDEX uniq_user_character (user_id, character_id), INDEX IDX_9D4E87E2A76ED395 (user_id), INDEX IDX_9D4E87E21136BE75 (character_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_talents (id INT AUTO_INCREMENT NOT NULL, acquired_at DATETIME NOT NULL, user_id INT NOT NULL, talent_id INT NOT NULL, UNIQUE INDEX uniq_user_talent (user_id, talent_id), INDEX IDX_8EC20E90A76ED395 (user_id), INDEX IDX_8EC20E9018777CEF (talent_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, scrap INT DEFAULT 0 NOT NULL, total_runs INT DEFAULT 0 NOT NULL, total_victories INT DEFAULT 0 NOT NULL, total_survival_seconds INT DEFAULT 0 NOT NULL, best_survival_seconds INT DEFAULT 0 NOT NULL, total_kills INT DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL, last_login_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_1483A5E9E7927C74 (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE weapons (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, description LONGTEXT NOT NULL, max_level INT DEFAULT 5 NOT NULL, code VARCHAR(255) NOT NULL, attack_type VARCHAR(30) NOT NULL, base_damage DOUBLE PRECISION NOT NULL, attack_interval DOUBLE PRECISION NOT NULL, `range` DOUBLE PRECISION DEFAULT 0 NOT NULL, projectile_count INT DEFAULT 1 NOT NULL, pierce_count INT DEFAULT 0 NOT NULL, area_of_effect DOUBLE PRECISION DEFAULT 0 NOT NULL, max_upgrade_description LONGTEXT NOT NULL, sprite_path VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_520EBBE177153098 (code), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id))');
        $this->addSql('ALTER TABLE characters ADD CONSTRAINT FK_3A29410E933D23AD FOREIGN KEY (starting_weapon_id) REFERENCES weapons (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE runs ADD CONSTRAINT FK_803A7B1FA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE runs ADD CONSTRAINT FK_803A7B1F1136BE75 FOREIGN KEY (character_id) REFERENCES characters (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE runs ADD CONSTRAINT FK_803A7B1FFCFA9DAE FOREIGN KEY (difficulty_id) REFERENCES difficulties (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE talents ADD CONSTRAINT FK_D6CF109A727ACA70 FOREIGN KEY (parent_id) REFERENCES talents (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE user_characters ADD CONSTRAINT FK_9D4E87E2A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_characters ADD CONSTRAINT FK_9D4E87E21136BE75 FOREIGN KEY (character_id) REFERENCES characters (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_talents ADD CONSTRAINT FK_8EC20E90A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_talents ADD CONSTRAINT FK_8EC20E9018777CEF FOREIGN KEY (talent_id) REFERENCES talents (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE characters DROP FOREIGN KEY FK_3A29410E933D23AD');
        $this->addSql('ALTER TABLE runs DROP FOREIGN KEY FK_803A7B1FA76ED395');
        $this->addSql('ALTER TABLE runs DROP FOREIGN KEY FK_803A7B1F1136BE75');
        $this->addSql('ALTER TABLE runs DROP FOREIGN KEY FK_803A7B1FFCFA9DAE');
        $this->addSql('ALTER TABLE talents DROP FOREIGN KEY FK_D6CF109A727ACA70');
        $this->addSql('ALTER TABLE user_characters DROP FOREIGN KEY FK_9D4E87E2A76ED395');
        $this->addSql('ALTER TABLE user_characters DROP FOREIGN KEY FK_9D4E87E21136BE75');
        $this->addSql('ALTER TABLE user_talents DROP FOREIGN KEY FK_8EC20E90A76ED395');
        $this->addSql('ALTER TABLE user_talents DROP FOREIGN KEY FK_8EC20E9018777CEF');
        $this->addSql('DROP TABLE characters');
        $this->addSql('DROP TABLE difficulties');
        $this->addSql('DROP TABLE enemy_types');
        $this->addSql('DROP TABLE power_ups');
        $this->addSql('DROP TABLE runs');
        $this->addSql('DROP TABLE talents');
        $this->addSql('DROP TABLE user_characters');
        $this->addSql('DROP TABLE user_talents');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE weapons');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
