<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Service\FirebaseService;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\HttpClient\HttpClient;

final class Version20240419183556 extends AbstractMigration
{

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE log ADD email VARCHAR(500)");

        $firebase = new FirebaseService(HttpClient::create());
        $rows = $firebase->getUsers();
        foreach ($rows as $row) {
            $email = $row->email;
            $uid = $row->uid;
            $sql = "UPDATE log SET email = :email WHERE api_key IN(SELECT api_key FROM api_key apiKeyTable WHERE uid = :uid)";
            $this->addSql($sql, ['email' => $email, 'uid' => $uid]);
        }
    }
}
