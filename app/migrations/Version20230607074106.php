<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230607074106 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE hand (id INT AUTO_INCREMENT NOT NULL, holdem_no_limit VARCHAR(255) NOT NULL, played_at DATETIME NOT NULL, players_position JSON NOT NULL, preflop_action VARCHAR(255) NOT NULL, flop_action VARCHAR(255) NOT NULL, turn_action VARCHAR(255) NOT NULL, river_action VARCHAR(255) NOT NULL, showdown VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE hand_player (hand_id INT NOT NULL, player_id INT NOT NULL, INDEX IDX_83F31F30EDDBB459 (hand_id), INDEX IDX_83F31F3099E6F5DF (player_id), PRIMARY KEY(hand_id, player_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE player (id INT AUTO_INCREMENT NOT NULL, pseudo VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE hand_player ADD CONSTRAINT FK_83F31F30EDDBB459 FOREIGN KEY (hand_id) REFERENCES hand (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE hand_player ADD CONSTRAINT FK_83F31F3099E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE hand_player DROP FOREIGN KEY FK_83F31F30EDDBB459');
        $this->addSql('ALTER TABLE hand_player DROP FOREIGN KEY FK_83F31F3099E6F5DF');
        $this->addSql('DROP TABLE hand');
        $this->addSql('DROP TABLE hand_player');
        $this->addSql('DROP TABLE player');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
