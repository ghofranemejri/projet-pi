<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250226175725 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout de la colonne disponibilite_id dans la table rendez_vous avec gestion de la valeur par défaut pour la contrainte étrangère';
    }

    public function up(Schema $schema): void
    {
        // 1. Ajouter la colonne en autorisant temporairement NULL
        if (!$this->columnExists($schema, 'rendez_vous', 'disponibilite_id')) {
            $this->addSql('ALTER TABLE rendez_vous ADD disponibilite_id INT DEFAULT NULL');
        }

        // 2. Vérifier si une ligne par défaut existe dans "disponibilite" avec id = 1
        $defaultDisponibiliteId = 1;
        $count = $this->connection->executeQuery(
            "SELECT COUNT(*) FROM disponibilite WHERE id = ?",
            [$defaultDisponibiliteId]
        )->fetchOne();

        if ((int)$count === 0) {
            // Si la ligne n'existe pas, insérer une ligne par défaut.
            // ATTENTION : adaptez cette insertion selon la structure réelle de votre table "disponibilite".
            $this->addSql("INSERT INTO disponibilite (id) VALUES ($defaultDisponibiliteId)");
        }

        // 3. Mettre à jour les enregistrements existants pour leur assigner la valeur par défaut
        $this->addSql("UPDATE rendez_vous SET disponibilite_id = $defaultDisponibiliteId WHERE disponibilite_id IS NULL");

        // 4. Modifier la colonne pour qu'elle ne puisse plus contenir de NULL
        $this->addSql('ALTER TABLE rendez_vous MODIFY disponibilite_id INT NOT NULL');

        // 5. Ajouter l'index si il n'existe pas
        if (!$this->indexExists('rendez_vous', 'IDX_65E8AA0A2B9D6493')) {
            $this->addSql('CREATE INDEX IDX_65E8AA0A2B9D6493 ON rendez_vous (disponibilite_id)');
        }

        // 6. Ajouter la contrainte de clé étrangère si elle n'existe pas
        if (!$this->foreignKeyExists('rendez_vous', 'FK_65E8AA0A2B9D6493')) {
            $this->addSql('ALTER TABLE rendez_vous ADD CONSTRAINT FK_65E8AA0A2B9D6493 FOREIGN KEY (disponibilite_id) REFERENCES disponibilite (id)');
        }
    }

    public function down(Schema $schema): void
    {
        // Supprimer la contrainte de clé étrangère
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

    // Méthode utilitaire pour vérifier si une contrainte de clé étrangère existe
    private function foreignKeyExists(string $tableName, string $constraintName): bool
    {
        $sql = "SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_NAME = :tableName 
                AND CONSTRAINT_NAME = :constraintName";
        $count = $this->connection->executeQuery($sql, [
            'tableName'      => $tableName,
            'constraintName' => $constraintName,
        ])->fetchOne();
        
        return (int)$count > 0;
    }

    // Méthode utilitaire pour vérifier si un index existe
    private function indexExists(string $tableName, string $indexName): bool
    {
        $sql = "SHOW INDEX FROM {$tableName} WHERE Key_name = :indexName";
        $rows = $this->connection->executeQuery($sql, [
            'indexName' => $indexName,
        ])->fetchAllAssociative();

        return count($rows) > 0;
    }
}
