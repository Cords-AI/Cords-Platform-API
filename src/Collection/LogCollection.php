<?php

namespace App\Collection;

use App\Entity\ApiKey;
use App\Entity\Log;
use DateTime;
use Doctrine\ORM\Tools\Pagination\Paginator;

class LogCollection extends AbstractCollection
{
    private string $userUid;

    private bool $limitToRelatedApiKeys = false;

    private ?string $province = '';

    private ?string $apiKey = '';

    private ?DateTime $startDate = null;

    private ?DateTime $endDate = null;

    public function userUid($userUid): self
    {
        if ($userUid) {
            $this->userUid = $userUid;
        }
        return $this;
    }

    public function limitToRelatedApiKeys($limitToRelatedApiKeys): self
    {
        if ($limitToRelatedApiKeys) {
            $this->limitToRelatedApiKeys = $limitToRelatedApiKeys;
        }
        return $this;
    }

    public function province($province): self
    {
        if ($province) {
            $this->province = $province;
        }
        return $this;
    }

    public function apiKey($apiKey): self
    {
        if ($apiKey) {
            $this->apiKey = $apiKey;
        }
        return $this;
    }

    public function startDate(?DateTime $startDate): self
    {
        if ($startDate) {
            $this->startDate = $startDate;
            $this->startDate->setTime(0, 0, 0);
        }
        return $this;
    }

    public function endDate(?DateTime $endDate): self
    {
        if ($endDate) {
            $this->endDate = $endDate;
            $this->endDate->setTime(23, 59, 59);
        }
        return $this;
    }

    public function fetchRows(): array
    {
        $this->qb->select('log')
            ->from(Log::class, 'log');

        if ($this->limitToRelatedApiKeys) {
            $relatedApiKeys = $this->getRelatedApiKeys();
            $this->qb->where($this->qb->expr()->in('log.apiKey', $relatedApiKeys));
        }

        if ($this->province) {
            $this->qb->andWhere("log.province = '$this->province'");
        }

        if ($this->apiKey) {
            $this->qb->andWhere("log.apiKey = '$this->apiKey'");
        }

        if ($this->q) {
            $searchTerm = "%$this->q%";
            $this->qb->andWhere('log.searchString LIKE :searchTerm')
                ->setParameter('searchTerm', $searchTerm);
        }

        if ($this->startDate) {
            $this->qb->andWhere('log.createdDate >= :startDate')
                ->setParameter('startDate', $this->startDate);
        }

        if ($this->endDate) {
            $this->qb->andWhere('log.createdDate <= :endDate')
                ->setParameter('endDate', $this->endDate);
        }

        $this->qb->orderBy("log.$this->sortField", $this->sortDirection);

        // paginate
        $query = $this->qb->getQuery();
        $query->setMaxResults($this->limit);
        $query->setFirstResult($this->offset);
        $paginator = new Paginator($query);
        $this->total = count($paginator);
        $rows = [];
        foreach ($paginator as $row) {
            $rows[] = $row;
        }

        // return
        $this->collection = $rows;
        return $this->collection;
    }

    private function getRelatedApiKeys(): array
    {
        $em = $this->doctrine->getManager();
        $apiKeyRepository = $em->getRepository(ApiKey::class);
        $apiKeys = $apiKeyRepository->findBy(['uid' => $this->userUid]);
        return array_map(fn($apiKey) => $apiKey->getApiKey(), $apiKeys);
    }
}
