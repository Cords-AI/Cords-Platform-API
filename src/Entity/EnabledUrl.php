<?php

namespace App\Entity;

use App\Repository\EnabledUrlRepository;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

#[ORM\Entity(repositoryClass: EnabledUrlRepository::class)]
class EnabledUrl implements JsonSerializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $url = null;

    #[ORM\ManyToOne(inversedBy: 'enabledUrls')]
    #[ORM\JoinColumn(name: "api_key_id", referencedColumnName: "id")]
    private ?ApiKey $apiKey = null;

    #[ORM\Column]
    private ?int $apiKeyId = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): static
    {
        $this->url = $url;

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
            'url' => $this->url,
            'apiKey' => $this->apiKeyId,
        ];
    }
}
