<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190606211555 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE desk_coordinate (id INT AUTO_INCREMENT NOT NULL, desk_id INT NOT NULL, INDEX IDX_8A41E1EC71F9DF5E (desk_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE wall_coordinate (id INT AUTO_INCREMENT NOT NULL, wall_id INT NOT NULL, x INT NOT NULL, y INT NOT NULL, INDEX IDX_211D01A1C33923F1 (wall_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE desk_coordinate ADD CONSTRAINT FK_8A41E1EC71F9DF5E FOREIGN KEY (desk_id) REFERENCES desk (id)');
        $this->addSql('ALTER TABLE wall_coordinate ADD CONSTRAINT FK_211D01A1C33923F1 FOREIGN KEY (wall_id) REFERENCES wall (id)');
        $this->addSql('ALTER TABLE desk DROP x, DROP y');
        $this->addSql('ALTER TABLE wall DROP x, DROP y');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE desk_coordinate');
        $this->addSql('DROP TABLE wall_coordinate');
        $this->addSql('ALTER TABLE desk ADD x INT DEFAULT NULL, ADD y INT DEFAULT NULL');
        $this->addSql('ALTER TABLE wall ADD x INT DEFAULT NULL, ADD y INT DEFAULT NULL');
    }
}
