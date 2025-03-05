<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250305194601 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE disponibilite CHANGE jour jour DATE NOT NULL');
        $this->addSql('ALTER TABLE patient DROP email');
        $this->addSql('ALTER TABLE rendez_vous DROP FOREIGN KEY FK_65E8AA0A2B9D6493');
        $this->addSql('DROP INDEX FK_65E8AA0A2B9D6493 ON rendez_vous');
        $this->addSql('ALTER TABLE rendez_vous ADD email VARCHAR(255) NOT NULL, DROP disponibilite_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE disponibilite CHANGE jour jour VARCHAR(10) DEFAULT NULL');
        $this->addSql('ALTER TABLE patient ADD email VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE rendez_vous ADD disponibilite_id INT NOT NULL, DROP email');
        $this->addSql('ALTER TABLE rendez_vous ADD CONSTRAINT FK_65E8AA0A2B9D6493 FOREIGN KEY (disponibilite_id) REFERENCES disponibilite (id)');
        $this->addSql('CREATE INDEX FK_65E8AA0A2B9D6493 ON rendez_vous (disponibilite_id)');
    }
}
