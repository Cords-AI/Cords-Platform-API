<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250508151026 extends AbstractMigration
{

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE log ADD COLUMN sid VARCHAR(36) DEFAULT NULL");
    }
}
