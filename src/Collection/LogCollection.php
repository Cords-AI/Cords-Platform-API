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

    protected int $limit = 25;

    private array $filters = [];

    private function getOffset(): int {
        return ($this->page - 1) * $this->limit;
    }

    public function page(?int $page): self
    {
        if ($page) {
            $this->page = $page;
        }
        return $this;
    }

    public function filters(?array $filters): self
    {
        if ($filters) {
            $this->filters = $filters;
        }
        return $this;
    }

    public function search($search): static
    {
        if ($search) {
            $this->search = $search;
        }
        return $this;
    }

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

    public function fetchRows(): array
    {
        $this->qb->select('log')
            ->from(Log::class, 'log');

        if ($this->limitToRelatedApiKeys) {
            $relatedApiKeys = $this->getRelatedApiKeys();
            $this->qb->where($this->qb->expr()->in('log.apiKey', $relatedApiKeys));
        }

        if (!empty($this->filters['province'])) {
            $province = $this->filters['province'];
            $this->qb->andWhere("log.province = '$province'");
        }

        if (!empty($this->filters['api-key'])) {
            $apiKeyMatch = $this->filters['api-key'];
            $apiKeyMatch = "%$apiKeyMatch%";
            $this->qb->andWhere('log.apiKey LIKE :apiKeyMatch')
                ->setParameter('apiKeyMatch', $apiKeyMatch);
        }

        if (!empty($this->filters['search-term'])) {
            $searchString = $this->filters['search-term'];
            $searchString = "%$searchString%";
            $this->qb->andWhere('log.searchString LIKE :searchString')
                ->setParameter('searchString', $searchString);
        }

        if (!empty($this->search)) {
            $search = $this->search;
            $search = "%$search%";
            $this->qb->andWhere('log.searchString LIKE :search')
                ->setParameter('search', $search);
        }

        if (!empty($this->filters['dates'])) {
            $dateRange = urldecode($this->filters['dates']);
            $timestamps = explode("|", $dateRange);

            $startDate = (new DateTime())->setTimestamp($timestamps[0] / 1000);
            $startDate->setTime(0, 0, 0);

            $endDate = (new DateTime())->setTimestamp($timestamps[1] / 1000);
            $endDate->setTime(23, 59, 59);

            $this->qb->andWhere('log.createdDate >= :startDate')
                ->setParameter('startDate', $startDate);

            $this->qb->andWhere('log.createdDate <= :endDate')
                ->setParameter('endDate', $endDate);
        }

        $direction = $this->descending ? 'DESC' : 'ASC';

        $this->qb->orderBy("TRIM(log.$this->sort)", $direction);

        // paginate
        $query = $this->qb->getQuery();
        $query->setMaxResults($this->limit);
        $query->setFirstResult($this->getOffset());
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
        return array_map(fn ($apiKey) => $apiKey->getApiKey(), $apiKeys);
    }
}
