<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240313144725 extends AbstractMigration
{

    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TABLE enabled_url 
            (id INT AUTO_INCREMENT, url VARCHAR(500), api_key_id INT, PRIMARY KEY (id), FOREIGN KEY (api_key_id) REFERENCES api_key(id)) 
            DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
    }
}
