<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240320151644 extends AbstractMigration
{

    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TABLE log
            (id INT AUTO_INCREMENT NOT NULL, api_key VARCHAR(255), search_string VARCHAR(1000), ip VARCHAR(50), type VARCHAR(255),
            latitude DOUBLE, longitude DOUBLE, province VARCHAR(50), created_date DATETIME,
            PRIMARY KEY (id))  DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
    }
}
