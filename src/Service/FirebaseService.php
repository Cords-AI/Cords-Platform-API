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

    public function getUser($id)
    {
        $result = $this->client->request('GET', "{$this->baseUrl}/users/$id");
        $body = json_decode($result->getContent());
        return $body;
    }

    public function getUsers(): array
    {
        $result = $this->client->request('GET', "{$this->baseUrl}/users");
        $body = json_decode($result->getContent());
        return $body->users;
    }

    public function manageAdminRole(string $uid, string $action): void
    {
        $this->client->request('POST', "{$this->baseUrl}/users/$uid/admin", [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'action' => $action,
            ],
        ]);
    }
}
