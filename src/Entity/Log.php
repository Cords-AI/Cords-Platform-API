<?php

namespace App\Entity;

use App\Repository\LogRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LogRepository::class)]
class Log implements \JsonSerializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $apiKey = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $ip = null;

    #[ORM\Column(length: 1000, nullable: true)]
    private ?string $searchString = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $type = null;

    #[ORM\Column(nullable: true)]
    private ?float $latitude = null;

    #[ORM\Column(nullable: true)]
    private ?float $longitude = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $province = null;

    #[ORM\Column(length: 15, nullable: true)]
    private ?string $postalCode = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $country = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $createdDate = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $email = null;

    #[ORM\OneToMany(mappedBy: 'log', targetEntity: SearchFilter::class)]
    private Collection $searchFilters;

    private ?array $queriedFilters;

    public function __construct()
    {
        $this->searchFilters = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }

    public function setApiKey(?string $apiKey): static
    {
        $this->apiKey = $apiKey;

        return $this;
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

    public function getSearchString(): ?string
    {
        return $this->searchString;
    }

    public function setSearchString(?string $searchString): static
    {
        $this->searchString = $searchString;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(?float $latitude): static
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(?float $longitude): static
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getProvince(): ?string
    {
        return $this->province;
    }

    public function setProvince(?string $province): static
    {
        $this->province = $province;

        return $this;
    }

    public function getCreatedDate(): ?\DateTimeInterface
    {
        return $this->createdDate;
    }

    public function setCreatedDate(?\DateTimeInterface $createdDate): static
    {
        $this->createdDate = $createdDate;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;

    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): static
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): static
    {
        $this->country = $country;

        return $this;
    }

    public function setQueriedFilters(?array $queriedFilters): static
    {
        $this->queriedFilters = $queriedFilters;
        return $this;
    }

    public function getQueriedFilters(): array
    {
        return $this->queriedFilters ?? [];
    }

    public function jsonSerialize(): mixed
    {
        return [
            'apiKey' => $this->apiKey,
            'ip' => $this->ip,
            'searchString' => $this->searchString,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'province' => $this->province,
            'type' => $this->type,
            'createdDate' => $this->createdDate,
            'email' => $this->email ?? '',
            'filters' => $this->getQueriedFilters(),
            'country' => $this->country,
            'postalCode' => $this->postalCode,
        ];
    }

    /**
     * @return Collection<int, SearchFilter>
     */
    public function getSearchFilters(): Collection
    {
        return $this->searchFilters;
    }

    public function addSearchFilter(SearchFilter $searchFilter): static
    {
        if (!$this->searchFilters->contains($searchFilter)) {
            $this->searchFilters->add($searchFilter);
            $searchFilter->setLog($this);
        }

        return $this;
    }

    public function removeSearchFilter(SearchFilter $searchFilter): static
    {
        if ($this->searchFilters->removeElement($searchFilter)) {
            // set the owning side to null (unless already changed)
            if ($searchFilter->getLog() === $this) {
                $searchFilter->setLog(null);
            }
        }

        return $this;
    }
}
