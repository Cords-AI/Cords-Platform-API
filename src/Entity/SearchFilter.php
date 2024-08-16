<?php

namespace App\Entity;

use App\Repository\SearchFilterRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SearchFilterRepository::class)]
class SearchFilter implements \JsonSerializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'searchFilters')]
    private ?Log $log = null;

    #[ORM\Column]
    private ?int $logId = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getLog(): ?Log
    {
        return $this->log;
    }

    public function setLog(?Log $log): static
    {
        $this->log = $log;

        return $this;
    }

    public function getLogId(): ?int
    {
        return $this->id;
    }

    public function setLogId(int $logId): static
    {
        $this->logId = $logId;

        return $this;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'name' => $this->name,
        ];
    }
}
