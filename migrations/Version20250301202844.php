<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250301202844 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE form_post_likes (form_post_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_D7168E067AE56317 (form_post_id), INDEX IDX_D7168E06A76ED395 (user_id), PRIMARY KEY(form_post_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE form_post_dislikes (form_post_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_31C8708F7AE56317 (form_post_id), INDEX IDX_31C8708FA76ED395 (user_id), PRIMARY KEY(form_post_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE form_post_likes ADD CONSTRAINT FK_D7168E067AE56317 FOREIGN KEY (form_post_id) REFERENCES form_post (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE form_post_likes ADD CONSTRAINT FK_D7168E06A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE form_post_dislikes ADD CONSTRAINT FK_31C8708F7AE56317 FOREIGN KEY (form_post_id) REFERENCES form_post (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE form_post_dislikes ADD CONSTRAINT FK_31C8708FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE share DROP FOREIGN KEY FK_EF069D5A7AE56317');
        $this->addSql('ALTER TABLE share DROP FOREIGN KEY FK_EF069D5AA76ED395');
        $this->addSql('DROP TABLE share');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE share (id INT AUTO_INCREMENT NOT NULL, form_post_id INT DEFAULT NULL, user_id INT DEFAULT NULL, date_time DATETIME NOT NULL, INDEX IDX_EF069D5AA76ED395 (user_id), INDEX IDX_EF069D5A7AE56317 (form_post_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE share ADD CONSTRAINT FK_EF069D5A7AE56317 FOREIGN KEY (form_post_id) REFERENCES form_post (id)');
        $this->addSql('ALTER TABLE share ADD CONSTRAINT FK_EF069D5AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE form_post_likes DROP FOREIGN KEY FK_D7168E067AE56317');
        $this->addSql('ALTER TABLE form_post_likes DROP FOREIGN KEY FK_D7168E06A76ED395');
        $this->addSql('ALTER TABLE form_post_dislikes DROP FOREIGN KEY FK_31C8708F7AE56317');
        $this->addSql('ALTER TABLE form_post_dislikes DROP FOREIGN KEY FK_31C8708FA76ED395');
        $this->addSql('DROP TABLE form_post_likes');
        $this->addSql('DROP TABLE form_post_dislikes');
    }
}
