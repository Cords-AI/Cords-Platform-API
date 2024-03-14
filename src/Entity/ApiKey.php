<?php

namespace App\Entity;

use App\Repository\ApiKeyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

#[ORM\Entity(repositoryClass: ApiKeyRepository::class)]
class ApiKey implements JsonSerializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $apiKey = null;

    #[ORM\Column(length: 255)]
    private ?string $uid = null;

    #[ORM\Column(nullable: true)]
    private ?bool $deleted = null;

    #[ORM\ManyToOne(inversedBy: 'apiKeys')]
    #[ORM\JoinColumn(name: "uid", referencedColumnName: "uid")]
    private ?Account $account = null;

    #[ORM\OneToMany(mappedBy: 'apiKey', targetEntity: EnabledUrl::class)]
    private Collection $enabledUrls;

    public function __construct()
    {
        $this->enabledUrls = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }

    public function setApiKey(string $apiKey): static
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    public function getUid(): ?string
    {
        return $this->uid;
    }

    public function setUid(string $uid): static
    {
        $this->uid = $uid;

        return $this;
    }

    public function getDeleted(): ?bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): static
    {
        $this->deleted = $deleted;

        return $this;
    }

    public function jsonSerialize(): mixed {
        return [
            "id" => $this->id,
            "uid" => $this->uid,
            "apiKey" => $this->apiKey
        ];
    }

    public function getAccount(): ?Account
    {
        return $this->account;
    }

    public function setAccount(?Account $account): static
    {
        $this->account = $account;

        return $this;
    }

    /**
     * @return Collection<int, EnabledUrl>
     */
    public function getEnabledUrls(): Collection
    {
        return $this->enabledUrls;
    }

    public function addEnabledUrl(EnabledUrl $enabledUrl): static
    {
        if (!$this->enabledUrls->contains($enabledUrl)) {
            $this->enabledUrls->add($enabledUrl);
            $enabledUrl->setApiKey($this);
        }

        return $this;
    }

    public function removeEnabledUrl(EnabledUrl $enabledUrl): static
    {
        if ($this->enabledUrls->removeElement($enabledUrl)) {
            // set the owning side to null (unless already changed)
            if ($enabledUrl->getApiKey() === $this) {
                $enabledUrl->setApiKey(null);
            }
        }

        return $this;
    }
}
