<?php

namespace App\Entity;

use App\Repository\ApiKeyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

#[ORM\HasLifecycleCallbacks]
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

    #[ORM\Column(length: 1000)]
    private ?string $name = null;

    #[ORM\Column(length: 100)]
    private ?string $type = null;

    #[ORM\ManyToOne(inversedBy: 'apiKeys')]
    #[ORM\JoinColumn(name: "uid", referencedColumnName: "uid")]
    private ?Account $account = null;

    #[ORM\OneToMany(mappedBy: 'apiKey', targetEntity: EnabledUrl::class)]
    private Collection $enabledUrls;

    #[ORM\OneToMany(mappedBy: 'apiKey', targetEntity: EnabledIp::class)]
    private Collection $enabledIps;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $created_date = null;

    public function __construct()
    {
        $this->enabledUrls = new ArrayCollection();
        $this->enabledIps = new ArrayCollection();
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

    public function getAccount(): ?Account
    {
        return $this->account;
    }

    public function setAccount(?Account $account): static
    {
        $this->account = $account;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

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

    /**
     * @return Collection<int, EnabledIp>
     */
    public function getEnabledIps(): Collection
    {
        return $this->enabledIps;
    }

    public function addEnabledIp(EnabledIp $enabledIp): static
    {
        if (!$this->enabledIps->contains($enabledIp)) {
            $this->enabledIps->add($enabledIp);
            $enabledIp->setApiKey($this);
        }

        return $this;
    }

    public function removeEnabledIp(EnabledIp $enabledIp): static
    {
        if ($this->enabledIps->removeElement($enabledIp)) {
            // set the owning side to null (unless already changed)
            if ($enabledIp->getApiKey() === $this) {
                $enabledIp->setApiKey(null);
            }
        }

        return $this;
    }

    public function getCreatedDate(): ?\DateTimeInterface
    {
        return $this->created_date;
    }

    #[ORM\PrePersist]
    public function setCreatedDate(): static
    {
        if ($this->created_date === null) {
            $this->created_date = new \DateTime();
        }

        return $this;
    }

    public function getExpirationDate()
    {
        $result = clone $this->getCreatedDate();
        $result->modify('+60 days');
        return $result;
    }

    public function isExpired(): bool
    {
        if($this->getType() === "production") {
            return false;
        }

        $expirationDate = $this->getExpirationDate();
        return \App\Utils\Util::isExpired($expirationDate);
    }

    public function jsonSerialize(): mixed
    {
        return [
            "id" => $this->id,
            "uid" => $this->uid,
            "apiKey" => $this->apiKey,
            "name" => $this->name,
            "type" => $this->type,
            "enabledUrls" => $this->enabledUrls->getValues() ?? [],
            "enabledIps" => $this->enabledIps->getValues() ?? [],
            "expirationDate" => $this->getExpirationDate(),
            "isExpired" => $this->isExpired()
        ];
    }
}
