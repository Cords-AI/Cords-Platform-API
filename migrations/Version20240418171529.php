<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240418171529 extends AbstractMigration
{

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE api_key ADD name VARCHAR(1000), ADD type VARCHAR(100)");
    }
}
