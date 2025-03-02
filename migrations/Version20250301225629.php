<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250301225629 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE form_post_dislikes DROP FOREIGN KEY FK_31C8708FA76ED395');
        $this->addSql('ALTER TABLE form_post_dislikes DROP FOREIGN KEY FK_31C8708F7AE56317');
        $this->addSql('ALTER TABLE form_post_likes DROP FOREIGN KEY FK_D7168E06A76ED395');
        $this->addSql('ALTER TABLE form_post_likes DROP FOREIGN KEY FK_D7168E067AE56317');
        $this->addSql('DROP TABLE form_post_dislikes');
        $this->addSql('DROP TABLE form_post_likes');
        $this->addSql('ALTER TABLE form_post ADD likes INT NOT NULL, ADD dislikes INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE form_post_dislikes (form_post_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_31C8708F7AE56317 (form_post_id), INDEX IDX_31C8708FA76ED395 (user_id), PRIMARY KEY(form_post_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE form_post_likes (form_post_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_D7168E067AE56317 (form_post_id), INDEX IDX_D7168E06A76ED395 (user_id), PRIMARY KEY(form_post_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE form_post_dislikes ADD CONSTRAINT FK_31C8708FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE form_post_dislikes ADD CONSTRAINT FK_31C8708F7AE56317 FOREIGN KEY (form_post_id) REFERENCES form_post (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE form_post_likes ADD CONSTRAINT FK_D7168E06A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE form_post_likes ADD CONSTRAINT FK_D7168E067AE56317 FOREIGN KEY (form_post_id) REFERENCES form_post (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE form_post DROP likes, DROP dislikes');
    }
}
