<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260913164121 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE prestation_etape (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(120) NOT NULL, description VARCHAR(320) NOT NULL, prix NUMERIC(8, 2) DEFAULT NULL, icone VARCHAR(40) NOT NULL, ordre INT NOT NULL, prestation_id INT NOT NULL, INDEX IDX_EAD43E5C9E45C554 (prestation_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 ENGINE = InnoDB');
        $this->addSql('ALTER TABLE prestation_etape ADD CONSTRAINT FK_EAD43E5C9E45C554 FOREIGN KEY (prestation_id) REFERENCES prestation (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE prestation_etape DROP FOREIGN KEY FK_EAD43E5C9E45C554');
        $this->addSql('DROP TABLE prestation_etape');
    }
}
