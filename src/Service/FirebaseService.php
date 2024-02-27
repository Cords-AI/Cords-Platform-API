<?php

namespace App\Service;

use stdClass;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FirebaseService
{
    private string $baseUrl;

    public function __construct(
        private HttpClientInterface $client,
    )
    {
        $this->baseUrl = $_ENV['FIREBASE_SERVICE_URL'];
    }

    public function getUsers(): array
    {
        $result = $this->client->request('GET', "{$this->baseUrl}/users");
        $body = json_decode($result->getContent());
        return $body->users;
    }
}
