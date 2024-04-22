<?php

namespace App\Collection;

use App\Entity\ApiKey;
use App\Entity\Log;
use DateTime;
use Doctrine\ORM\Tools\Pagination\Paginator;

class LogCollection extends AbstractCollection
{
    private string $userUid;

    private bool $isAdmin = false;

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

    public function isAdmin(?bool $isAdmin): self
    {
        if ($isAdmin) {
            $this->isAdmin = $isAdmin;
        }
        return $this;
    }

    public function fetchRows(): array
    {
        $fieldsToFetch = ['id', 'apiKey', 'ip', 'searchString', 'latitude', 'longitude', 'province', 'type', 'createdDate'];
        if ($this->isAdmin) {
            $fieldsToFetch[] = 'email';
        }

        $fields = implode(', ', $fieldsToFetch);

        $this->qb->select("partial log.{ $fields }")
            ->from(Log::class, 'log');

        if (!$this->isAdmin) {
            $relatedApiKeys = $this->getRelatedApiKeys();
            if(empty($relatedApiKeys)) {
                $this->collection = [];
                return $this->collection;
            }
            $this->qb->where($this->qb->expr()->in('log.apiKey', $relatedApiKeys));
        }

        if (!empty($this->filters['province'])) {
            $provinces = explode(',', $this->filters['province']);
            $this->qb->andWhere('log.province IN(:provinces)')
                ->setParameter('provinces', $provinces);
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

        if (!empty($this->filters['key-type'])) {
            $keyTypes = explode(',', $this->filters['key-type']);
            $this->qb->leftJoin(ApiKey::class, 'apiKeyTable', 'WITH', 'log.apiKey = apiKeyTable.apiKey')
                ->andWhere('apiKeyTable.type IN(:keyTypes)')
                ->andWhere('apiKeyTable.uid = :uid')
                ->setParameter('keyTypes', $keyTypes)
                ->setParameter('uid', $this->userUid);
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

    protected function setHeaders(): void
    {
        $this->exportHeaders[] = 'API key';
        $this->exportHeaders[] = 'Type';
        $this->exportHeaders[] = 'Query';
        $this->exportHeaders[] = 'Province';
        $this->exportHeaders[] = 'Latitude';
        $this->exportHeaders[] = 'Longitude';
        $this->exportHeaders[] = 'Created Date';
    }

    protected function setExportableRows(): void
    {
        $this->exportableRows = [$this->exportHeaders];
        /** @var Log $log */
        foreach ($this->collection as $log) {
            $currentRow['API key'] = $log->getApiKey();
            $currentRow['Type'] = $log->getType();
            $currentRow['Query'] = $log->getSearchString();
            $currentRow['Province'] = $log->getProvince();
            $currentRow['Latitude'] = $log->getLatitude();
            $currentRow['Longitude'] = $log->getLongitude();
            $currentRow['Created Date'] = $log->getCreatedDate() ? $log->getCreatedDate()->format('d/M/Y - G:i:s') : '';

            $this->exportableRows[] = $currentRow;
        }
    }
}
