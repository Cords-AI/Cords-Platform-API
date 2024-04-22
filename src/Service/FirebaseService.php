<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class FirebaseService
{
    private string $baseUrl;

    public static function getMatchingFirebaseUser(array $users, string $uid): ?\stdClass {
        foreach ($users as $user) {
            if ($user->uid === $uid) {
                return $user;
            }
        }
        return null;
    }

    public function __construct(
        private HttpClientInterface $client,
    ) {
        $this->baseUrl = $_ENV['FIREBASE_SERVICE_URL'];
    }

    public function getUsers(): array
    {
        $result = $this->client->request('GET', "{$this->baseUrl}/users");
        $body = json_decode($result->getContent());
        return $body->users;
    }
}
