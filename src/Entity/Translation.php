<?php

namespace App\Entity;

use App\Repository\TranslationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TranslationRepository::class)]
class Translation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $translationKey = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $value = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $lang = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTranslationKey(): ?string
    {
        return $this->translationKey;
    }

    public function setTranslationKey(?string $translationKey): static
    {
        $this->translationKey = $translationKey;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function getLang(): ?string
    {
        return $this->lang;
    }

    public function setLang(?string $lang): static
    {
        $this->lang = $lang;

        return $this;
    }
}
