<?php

namespace App\Entity;

use App\Repository\AccountRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AccountRepository::class)]
class Account
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy:"NONE")]
    #[ORM\Column(type: "string", length: 255)]
    private string $uid;

    #[ORM\Column(length: 50)]
    private ?string $status = null;

    #[ORM\OneToMany(mappedBy: 'account', targetEntity: ApiKey::class)]
    private Collection $apiKeys;

    #[ORM\OneToOne(cascade: ['persist', 'remove'], fetch: 'EAGER')]
    private ?Profile $profile = null;

    #[ORM\OneToMany(mappedBy: 'account', targetEntity: Agreement::class)]
    private Collection $agreements;

    private bool $hasAcceptedTermsOfUse = false;

    public function __construct()
    {
        $this->apiKeys = new ArrayCollection();
        $this->agreements = new ArrayCollection();
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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): static
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection<int, ApiKey>
     */
    public function getApiKeys(): Collection
    {
        return $this->apiKeys;
    }

    public function addApiKey(ApiKey $apiKey): static
    {
        if (!$this->apiKeys->contains($apiKey)) {
            $this->apiKeys->add($apiKey);
            $apiKey->setAccount($this);
        }

        return $this;
    }

    public function removeApiKey(ApiKey $apiKey): static
    {
        if ($this->apiKeys->removeElement($apiKey)) {
            // set the owning side to null (unless already changed)
            if ($apiKey->getAccount() === $this) {
                $apiKey->setAccount(null);
            }
        }

        return $this;
    }

    public function getProfile(): ?Profile
    {
        return $this->profile;
    }

    public function setProfile(?Profile $profile): static
    {
        $this->profile = $profile;

        return $this;
    }

    /**
     * @return Collection<int, Agreement>
     */
    public function getAgreements(): Collection
    {
        return $this->agreements;
    }

    public function addAgreement(Agreement $agreement): static
    {
        if (!$this->agreements->contains($agreement)) {
            $this->agreements->add($agreement);
            $agreement->setAccount($this);
        }

        return $this;
    }

    public function removeAgreement(Agreement $agreement): static
    {
        if ($this->agreements->removeElement($agreement)) {
            // set the owning side to null (unless already changed)
            if ($agreement->getAccount() === $this) {
                $agreement->setAccount(null);
            }
        }

        return $this;
    }

    public function getHasAcceptedTermsOfUse(): bool
    {
        return $this->hasAcceptedTermsOfUse;
    }

    public function getUnacceptedAgreementIds(): array
    {
        $connection = DriverManager::getConnection([
            'url' => $_ENV['DATABASE_URL'],
        ]);

        $sql = "SELECT term.id
                FROM term
                LEFT JOIN agreement
                ON term.id = agreement.term_id
                AND agreement.account_uid = '{$this->uid}'
                WHERE agreement.term_id IS NULL
                AND term.version = (SELECT MAX(version) FROM term as termInner WHERE termInner.name = term.name)";

        return $connection->fetchFirstColumn($sql);
    }

    public function calculateHasAcceptedTermsOfUse(): void
    {
        $connection = DriverManager::getConnection([
            'url' => $_ENV['DATABASE_URL'],
        ]);

        $sql = "SELECT term.id
                FROM term
                LEFT JOIN agreement
                ON term.id = agreement.term_id
                AND agreement.account_uid = '{$this->uid}'
                WHERE agreement.term_id IS NULL 
                AND term.version = (SELECT MAX(version) FROM term as termInner WHERE termInner.name = term.name)
                AND term.name = 'terms-of-use'
                AND (SELECT COUNT(agreement.id) FROM agreement
                        LEFT JOIN term as innerTerm ON agreement.term_id = innerTerm.id
                        WHERE account_uid = '{$this->uid}'
                        AND innerTerm.name = 'terms-of-use' AND valid_until > CURRENT_TIMESTAMP) = 0";

        $results = $connection->fetchFirstColumn($sql);

        $this->hasAcceptedTermsOfUse = count($results) === 0;
    }

    public function alreadyAcceptedThisTerm(Term $term): bool
    {
        $previousAgreements = $this->getAgreements()->getValues();
        foreach ($previousAgreements as $agreement) {
            if ($agreement->getTerm()->getId() === $term->getId()) {
                return true;
            }
        }
        return false;
    }
}
