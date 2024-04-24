<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240424152239 extends AbstractMigration
{

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE log ADD country VARCHAR(500), ADD postal_code VARCHAR(15)");
    }
}
