<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250106155756 extends AbstractMigration
{

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE term 
            (
                id INT AUTO_INCREMENT NOT NULL, 
                name VARCHAR(500) NOT NULL, 
                version VARCHAR(25) NOT NULL,
                title_en TEXT, 
                title_fr TEXT, 
                url_en VARCHAR(1000), 
                url_fr VARCHAR(1000), 
                created_date DATETIME NOT NULL,
                PRIMARY KEY(id)
            ) 
                DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql("INSERT INTO term (name, version, title_en, title_fr, url_en, url_fr, created_date)
                    VALUES
                     (
                        'terms-of-use',
                        1,
                        'CORDS Platform Partner Terms of Use',
                        'Conditions d\’utilisation de la plateforme CORDS pour les Partenaires',
                        'https://wp.cords.ai/en/agreements/terms-of-use/v1',
                        'https://wp.cords.ai/fr/agreements/terms-of-use/v1',
                        CURRENT_TIMESTAMP
                     ),
                     (
                        'terms-of-use',
                        0,
                        NULL,
                        NULL,
                        NULL,
                        NULL,
                        CURRENT_TIMESTAMP
                     )
                     ");
    }
}
