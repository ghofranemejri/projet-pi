<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250226175725 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout de la colonne disponibilite_id dans la table rendez_vous';
    }

    public function up(Schema $schema): void
    {
        // Vérifier si la colonne n'existe pas avant de l'ajouter
        if (!$this->columnExists($schema, 'rendez_vous', 'disponibilite_id')) {
            $this->addSql('ALTER TABLE rendez_vous ADD disponibilite_id INT NOT NULL');
        }

        // Ajouter la contrainte de clé étrangère si elle n'existe pas
        if (!$this->foreignKeyExists('rendez_vous', 'FK_65E8AA0A2B9D6493')) {
            $this->addSql('ALTER TABLE rendez_vous ADD CONSTRAINT FK_65E8AA0A2B9D6493 FOREIGN KEY (disponibilite_id) REFERENCES disponibilite (id)');
        }

        // Ajouter l'index si il n'existe pas
        if (!$this->indexExists('rendez_vous', 'IDX_65E8AA0A2B9D6493')) {
            $this->addSql('CREATE INDEX IDX_65E8AA0A2B9D6493 ON rendez_vous (disponibilite_id)');
        }
    }

    public function down(Schema $schema): void
    {
        // Supprimer la clé étrangère si elle existe
        $this->addSql('ALTER TABLE rendez_vous DROP FOREIGN KEY FK_65E8AA0A2B9D6493');
        // Supprimer l'index
        $this->addSql('DROP INDEX IDX_65E8AA0A2B9D6493 ON rendez_vous');
        // Supprimer la colonne
        $this->addSql('ALTER TABLE rendez_vous DROP disponibilite_id');
    }

    // Méthode utilitaire pour vérifier si une colonne existe
    private function columnExists(Schema $schema, string $tableName, string $columnName): bool
    {
        $table = $schema->getTable($tableName);
        return $table->hasColumn($columnName);
    }

    // Méthode utilitaire pour vérifier si une clé étrangère existe
    private function foreignKeyExists(string $tableName, string $constraintName): bool
    {
        $sql = "SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_NAME = :tableName 
                AND CONSTRAINT_NAME = :constraintName";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('tableName', $tableName);
        $stmt->bindValue('constraintName', $constraintName);
        $stmt->execute();

        // Utiliser fetchColumn() qui est disponible dans DBAL 2.x et DBAL 3.x
        return (int)$stmt->fetchColumn() > 0;
    }

    // Méthode utilitaire pour vérifier si un index existe
    private function indexExists(string $tableName, string $indexName): bool
    {
        $sql = "SHOW INDEX FROM {$tableName} WHERE Key_name = :indexName";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('indexName', $indexName);
        $stmt->execute();

        // Utiliser fetchColumn() qui est disponible dans DBAL 2.x et DBAL 3.x
        return (bool)$stmt->fetchColumn();
    }
}
