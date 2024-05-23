<?php

namespace App\Entity;

use App\Repository\EnabledIpRepository;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

#[ORM\Entity(repositoryClass: EnabledIpRepository::class)]
class EnabledIp implements JsonSerializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 60, nullable: true)]
    private ?string $ip = null;

    #[ORM\ManyToOne(inversedBy: 'enabledIps')]
    #[ORM\JoinColumn(name: "api_key_id", referencedColumnName: "id")]
    private ?ApiKey $apiKey = null;

    #[ORM\Column]
    private ?int $apiKeyId = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setIp(?string $ip): static
    {
        $this->ip = $ip;

        return $this;
    }

    public function getApiKey(): ?ApiKey
    {
        return $this->apiKey;
    }

    public function setApiKey(?ApiKey $apiKey): static
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    public function getApiKeyId(): ?int
    {
        return $this->apiKeyId;
    }

    public function setApiKeyId(int $apiKeyId): static
    {
        $this->apiKeyId = $apiKeyId;

        return $this;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->id,
            'ip' => $this->ip,
            'apiKey' => $this->apiKeyId,
        ];
    }
}
