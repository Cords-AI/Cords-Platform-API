<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Service\FirebaseService;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\HttpClient\HttpClient;

final class Version20240307141346 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->connection->executeStatement("CREATE TABLE account (uid VARCHAR(255), status VARCHAR(30), PRIMARY KEY (uid))");

        $firebase = new FirebaseService(HttpClient::create());
        $rows = $firebase->getUsers();
        $uids = array_map(fn($row) => $row->uid, $rows);

        $placeholders = array_fill(0, count($uids), '(?)');
        $placeholdersString = implode(', ', $placeholders);
        $parameterTypes = array_fill(0, count($uids), \Doctrine\DBAL\ParameterType::STRING);

        $sql = "INSERT INTO account (uid) VALUES {$placeholdersString}";
        $this->connection->executeStatement($sql, $uids, $parameterTypes);
    }
}
