<?php

namespace App\Entity;

use App\Repository\TermRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

#[ORM\Entity(repositoryClass: TermRepository::class)]
class Term implements JsonSerializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 500)]
    private ?string $name = null;

    #[ORM\Column(length: 25)]
    private ?string $version = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $titleEn = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $titleFr = null;

    #[ORM\Column(length: 1000, nullable: true)]
    private ?string $urlEn = null;

    #[ORM\Column(length: 1000, nullable: true)]
    private ?string $urlFr = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdDate = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function setVersion(string $version): static
    {
        $this->version = $version;

        return $this;
    }

    public function getTitleEn(): ?string
    {
        return $this->titleEn;
    }

    public function setTitleEn(string $titleEn): static
    {
        $this->titleEn = $titleEn;

        return $this;
    }

    public function getTitleFr(): ?string
    {
        return $this->titleFr;
    }

    public function setTitleFr(string $titleFr): static
    {
        $this->titleFr = $titleFr;

        return $this;
    }

    public function getUrlEn(): ?string
    {
        return $this->urlEn;
    }

    public function setUrlEn(string $urlEn): static
    {
        $this->urlEn = $urlEn;

        return $this;
    }

    public function getUrlFr(): ?string
    {
        return $this->urlFr;
    }

    public function setUrlFr(string $urlFr): static
    {
        $this->urlFr = $urlFr;

        return $this;
    }

    public function getCreatedDate(): ?\DateTimeInterface
    {
        return $this->createdDate;
    }

    public function setCreatedDate(\DateTimeInterface $createdDate): static
    {
        $this->createdDate = $createdDate;

        return $this;
    }

    public function jsonSerialize(): mixed
    {
        return [
            "name" => $this->name,
            "version" => $this->version,
            "titleEn" => $this->titleEn,
            "titleFr" => $this->titleFr,
            "urlEn" => $this->urlEn,
            "urlFr" => $this->urlFr,
            "createdDate" => $this->createdDate
        ];
    }
}
