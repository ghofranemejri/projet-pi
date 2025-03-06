<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250220094757 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE prescription (id INT AUTO_INCREMENT NOT NULL, medecin_id INT NOT NULL, patient_id INT NOT NULL, datedeb DATETIME NOT NULL, datefin DATETIME NOT NULL, created_at DATETIME NOT NULL, address VARCHAR(255) NOT NULL, INDEX IDX_1FBFB8D94F31A84 (medecin_id), INDEX IDX_1FBFB8D96B899279 (patient_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE traitement (id INT AUTO_INCREMENT NOT NULL, prescription_id INT NOT NULL, medicament VARCHAR(255) NOT NULL, dose INT NOT NULL, description VARCHAR(255) NOT NULL, INDEX IDX_2A356D2793DB413D (prescription_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE utilisateur (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, adress VARCHAR(255) NOT NULL, num_tel INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE prescription ADD CONSTRAINT FK_1FBFB8D94F31A84 FOREIGN KEY (medecin_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE prescription ADD CONSTRAINT FK_1FBFB8D96B899279 FOREIGN KEY (patient_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE traitement ADD CONSTRAINT FK_2A356D2793DB413D FOREIGN KEY (prescription_id) REFERENCES prescription (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE prescription DROP FOREIGN KEY FK_1FBFB8D94F31A84');
        $this->addSql('ALTER TABLE prescription DROP FOREIGN KEY FK_1FBFB8D96B899279');
        $this->addSql('ALTER TABLE traitement DROP FOREIGN KEY FK_2A356D2793DB413D');
        $this->addSql('DROP TABLE prescription');
        $this->addSql('DROP TABLE traitement');
        $this->addSql('DROP TABLE utilisateur');
    }
}
