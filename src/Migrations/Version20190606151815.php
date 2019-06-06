<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190606151815 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE room (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(255) NOT NULL, width INT NOT NULL, height INT NOT NULL, size INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE person (id INT AUTO_INCREMENT NOT NULL, firstname VARCHAR(255) NOT NULL, lastname VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE desk (id INT AUTO_INCREMENT NOT NULL, room_id INT NOT NULL, number INT NOT NULL, x INT NOT NULL, y INT NOT NULL, color VARCHAR(7) NOT NULL, INDEX IDX_56E246654177093 (room_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE wall (id INT AUTO_INCREMENT NOT NULL, room_id INT NOT NULL, x INT NOT NULL, y INT NOT NULL, color VARCHAR(7) NOT NULL, INDEX IDX_13F5EFF654177093 (room_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE booking (id INT AUTO_INCREMENT NOT NULL, desk_id INT NOT NULL, person_id INT DEFAULT NULL, start DATETIME NOT NULL, end DATETIME NOT NULL, INDEX IDX_E00CEDDE71F9DF5E (desk_id), INDEX IDX_E00CEDDE217BBB47 (person_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE desk ADD CONSTRAINT FK_56E246654177093 FOREIGN KEY (room_id) REFERENCES room (id)');
        $this->addSql('ALTER TABLE wall ADD CONSTRAINT FK_13F5EFF654177093 FOREIGN KEY (room_id) REFERENCES room (id)');
        $this->addSql('ALTER TABLE booking ADD CONSTRAINT FK_E00CEDDE71F9DF5E FOREIGN KEY (desk_id) REFERENCES desk (id)');
        $this->addSql('ALTER TABLE booking ADD CONSTRAINT FK_E00CEDDE217BBB47 FOREIGN KEY (person_id) REFERENCES person (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE desk DROP FOREIGN KEY FK_56E246654177093');
        $this->addSql('ALTER TABLE wall DROP FOREIGN KEY FK_13F5EFF654177093');
        $this->addSql('ALTER TABLE booking DROP FOREIGN KEY FK_E00CEDDE217BBB47');
        $this->addSql('ALTER TABLE booking DROP FOREIGN KEY FK_E00CEDDE71F9DF5E');
        $this->addSql('DROP TABLE room');
        $this->addSql('DROP TABLE person');
        $this->addSql('DROP TABLE desk');
        $this->addSql('DROP TABLE wall');
        $this->addSql('DROP TABLE booking');
    }
}
