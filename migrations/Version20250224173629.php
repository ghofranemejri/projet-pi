<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250224173629 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE share ADD form_post_id INT DEFAULT NULL, ADD user_id INT DEFAULT NULL, ADD date_time DATETIME NOT NULL');
        $this->addSql('ALTER TABLE share ADD CONSTRAINT FK_EF069D5A7AE56317 FOREIGN KEY (form_post_id) REFERENCES form_post (id)');
        $this->addSql('ALTER TABLE share ADD CONSTRAINT FK_EF069D5AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_EF069D5A7AE56317 ON share (form_post_id)');
        $this->addSql('CREATE INDEX IDX_EF069D5AA76ED395 ON share (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE share DROP FOREIGN KEY FK_EF069D5A7AE56317');
        $this->addSql('ALTER TABLE share DROP FOREIGN KEY FK_EF069D5AA76ED395');
        $this->addSql('DROP INDEX IDX_EF069D5A7AE56317 ON share');
        $this->addSql('DROP INDEX IDX_EF069D5AA76ED395 ON share');
        $this->addSql('ALTER TABLE share DROP form_post_id, DROP user_id, DROP date_time');
    }
}
