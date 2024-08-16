<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240702140057 extends AbstractMigration
{

    public function up(Schema $schema): void
    {
        $rows = $this->connection->fetchAllAssociative('SELECT id, filters FROM log 
                   WHERE filters IS NOT NULL AND JSON_LENGTH(filters) > 0');

        $insertSql = "INSERT INTO search_filter (name, log_id) VALUES ";

        $values = [];

        foreach ($rows as $row) {
            foreach (json_decode($row['filters']) as $filter) {
                $id = $row['id'];
                $values[] = "('$filter', $id)";
            }
        }

        $insertSql .= implode(', ', $values);
        $insertSql .= ";";

        if (!count($rows)) {
            $insertSql = "";
        }

        $this->addSql("CREATE TABLE search_filter
            (id INT AUTO_INCREMENT, name VARCHAR(255), log_id INT, PRIMARY KEY (id), FOREIGN KEY (log_id) REFERENCES log(id))
            DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB; 
            $insertSql
            ALTER TABLE log DROP COLUMN FILTERS");
    }
}
