<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250106200542 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE account MODIFY uid VARCHAR(255)  CHARACTER SET utf8mb4  COLLATE utf8mb4_unicode_ci NOT NULL;");
        $this->addSql('CREATE TABLE agreement 
                            (
                                id INT AUTO_INCREMENT NOT NULL, 
                                account_uid VARCHAR(255) NOT NULL, 
                                term_id INT NOT NULL, 
                                accepted_date DATETIME DEFAULT NULL, 
                                valid_until DATETIME DEFAULT NULL, 
                                INDEX IDX_2E655A249B6B5FBA (account_uid),
                                INDEX FK_2E655A24E2C35FC (term_id), 
                                PRIMARY KEY(id)
                            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE agreement ADD CONSTRAINT FK_2E655A249B6B5FBA FOREIGN KEY (account_uid) REFERENCES account (uid)');
        $this->addSql('ALTER TABLE agreement ADD CONSTRAINT FK_2E655A24E2C35FC FOREIGN KEY (term_id) REFERENCES term (id)');

        $usersSql = "SELECT uid FROM account";
        $usersResult = $this->connection->executeQuery($usersSql);
        $users = $usersResult->fetchFirstColumn();

        $termSql = "SELECT id FROM term WHERE name = 'terms-of-use' AND version = 0";
        $termsResult = $this->connection->executeQuery($termSql);
        $termId = $termsResult->fetchOne();

        $agreementSql = "INSERT INTO agreement (account_uid, term_id, valid_until) VALUES ";
        $usersLength = count($users) -1;

        foreach ($users as $index => $userId) {
            $commaOrSemicolon = $index === $usersLength ? ';' : ',' ;
            $agreementSql .= "('$userId', '$termId', DATE_ADD(NOW(), INTERVAL 60 DAY))$commaOrSemicolon";
        }

        if (count($users)) {
            $this->addSql($agreementSql);
        }
    }
}
