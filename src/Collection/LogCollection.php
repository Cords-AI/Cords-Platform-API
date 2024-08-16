<?php

namespace App\Collection;

use App\Entity\ApiKey;
use App\Entity\Log;
use App\Entity\SearchFilter;
use App\Entity\Translation;
use DateTime;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Yaml\Yaml;

class LogCollection extends AbstractCollection
{
    private string $userUid;

    private bool $isAdmin = false;

    protected int $limit = 25;

    private array $filters = [];

    private string $clientLang = 'en';

    private int $maxTranslationsLength = 0;

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

    public function clientLang(?string $clientLang): self
    {
        if ($clientLang) {
            $this->clientLang = $clientLang;
        }
        return $this;
    }

    public function fetchRows(): array
    {
        $this->addTempTranslationTable();

        $fieldsToFetch = ['id', 'apiKey', 'ip', 'searchString', 'latitude', 'longitude', 'province', 'type',
            'createdDate', 'postalCode', 'country'];
        if ($this->isAdmin) {
            $fieldsToFetch[] = 'email';
            $this->addTextFieldFilter('email', $this->qb);
        }

        $searchFiltersSubQuery = $this->qb->getEntityManager()->createQueryBuilder()
            ->select("CONCAT(
                              '[',
                              GROUP_CONCAT(
                                  CONCAT('\"', translation.value, '\"')
                                  ORDER BY translation.value ASC
                              ),
                              ']'
                            )")
            ->from(SearchFilter::class, 'searchFilterTable')
            ->leftJoin(Translation::class, 'translation', 'WITH',
                "searchFilterTable.name = translation.translationKey AND translation.lang = '{$this->clientLang}'")
            ->where('searchFilterTable.log = log.id')
            ->getDQL();

        $fields = implode(', ', $fieldsToFetch);

        $this->qb->select("partial log.{ $fields }, ($searchFiltersSubQuery) as searchFilterNames")
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

        if (!empty($this->filters['search-term'])) {
            $searchString = $this->filters['search-term'];
            $searchString = "%$searchString%";
            $this->qb->andWhere('log.searchString LIKE :searchString')
                ->setParameter('searchString', $searchString);
        }

        if (!empty($this->search)) {
            $search = $this->search;
            $search = "%$search%";

            $orX = $this->qb->expr()->orX(
                $this->qb->expr()->like('log.searchString', ':search')
            );

            if ($this->isAdmin) {
                $orX->add($this->qb->expr()->like('log.email', ':search'));
            }

            $this->qb->andWhere($orX)
                ->setParameter('search', $search);
        }

        $this->addTextFieldFilter('postal-code', $this->qb);
        $this->addTextFieldFilter('country', $this->qb);

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


        if (!empty($this->filters['key-type']) || !empty($this->filters['api-key'])) {
            $this->qb->leftJoin(ApiKey::class, 'apiKeyTable', 'WITH', 'log.apiKey = apiKeyTable.apiKey');

            if (!empty($this->filters['key-type'])) {
                $keyTypes = explode(',', $this->filters['key-type']);
                $this->qb->andWhere('apiKeyTable.type IN(:keyTypes)')
                    ->setParameter('keyTypes', $keyTypes);
            }

            if (!empty($this->filters['api-key'])) {
                $apiKeys = explode(',', $this->filters['api-key']);
                $this->qb->andWhere('apiKeyTable.id IN(:apiKeys)')
                    ->setParameter('apiKeys', $apiKeys);
            }

            if (!$this->isAdmin) {
                $this->qb->andWhere('apiKeyTable.uid = :uid')
                    ->setParameter('uid', $this->userUid);
            }

        }

        $direction = $this->descending ? 'DESC' : 'ASC';

        if ($this->sort === 'longitude' || $this->sort === 'latitude') {
            $this->qb->orderBy("CAST(TRIM(log.$this->sort) AS DOUBLE)", $direction);
        } else if ($this->sort === 'filters') {
            $index = 0;
            while ($index <= $this->maxTranslationsLength) {
                $this->qb->addOrderBy("JSON_UNQUOTE(JSON_EXTRACT(CAST(searchFilterNames AS JSON), '$[$index]'))", $direction);
                $index++;
            }
            $this->qb->addOrderBy("log.createdDate", $direction);
        } else {
            $this->qb->orderBy("TRIM(log.$this->sort)", $direction);
        }

        // paginate
        $query = $this->qb->getQuery();
        $query->setMaxResults($this->limit);
        $query->setFirstResult($this->getOffset());
        $paginator = new Paginator($query);
        $this->total = count($paginator);
        $rows = [];
        foreach ($paginator as $row) {
            $rows[] = current($row)->setQueriedFilters(json_decode($row['searchFilterNames']));
        }

        // return
        $this->collection = $rows;
        return $this->collection;
    }

    private function addTempTranslationTable() {
        $tableCreateSql = "CREATE TEMPORARY TABLE `translation` (
                    `id` int NOT NULL AUTO_INCREMENT,
                    `translation_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                    `value` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                    `lang` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";

        $this->connection->executeStatement($tableCreateSql);

        $messages = Yaml::parseFile(__DIR__ . "/../../translations/messages.{$this->clientLang}.yaml");
        $translationKeys = array_keys($messages);
        $this->maxTranslationsLength = count($translationKeys);

        $insertSql = "INSERT INTO translation (translation_key, value, lang) VALUES";

        foreach (array_keys($messages) as $index => $key) {
            $messageValue = $messages[$key] ?? '';
            $insertSql .= "('$key', '$messageValue', '{$this->clientLang}'),";

            if ($index === (count($translationKeys) - 1)) {
                $insertSql .= "(null, '', 'en'), (null, '', 'fr');";
            }
        }

        $this->connection->executeStatement($insertSql);
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
        if ($this->isAdmin) {
            $this->exportHeaders[] = 'Account';
        }
        $this->exportHeaders[] = 'Type';
        $this->exportHeaders[] = 'Query';
        $this->exportHeaders[] = 'Postal Code';
        $this->exportHeaders[] = 'Province';
        $this->exportHeaders[] = 'Country';
        $this->exportHeaders[] = 'Latitude';
        $this->exportHeaders[] = 'Longitude';
        $this->exportHeaders[] = 'Cords Filters';
        $this->exportHeaders[] = 'Created Date';
    }

    protected function setExportableRows(): void
    {
        $this->exportableRows = [$this->exportHeaders];
        /** @var Log $log */
        foreach ($this->collection as $log) {
            $currentRow['API key'] = $log->getApiKey();
            if ($this->isAdmin) {
                $currentRow['Account'] = $log->getEmail();
            }
            $currentRow['Type'] = $log->getType();
            $currentRow['Query'] = $log->getSearchString();
            $currentRow['Postal Code'] = $log->getPostalCode();
            $currentRow['Province'] = $log->getProvince();
            $currentRow['Country'] = $log->getCountry();
            $currentRow['Latitude'] = $log->getLatitude();
            $currentRow['Longitude'] = $log->getLongitude();
            $currentRow['Cords Filters'] = implode(", ", $log->getQueriedFilters());
            $currentRow['Created Date'] = $log->getCreatedDate() ? $log->getCreatedDate()->format('d/M/Y - G:i:s') : '';

            $this->exportableRows[] = $currentRow;
        }
    }

    private function addTextFieldFilter(string $fieldName, QueryBuilder &$qb): void
    {
        if (!empty($this->filters[$fieldName])) {
            $textFilter = $this->filters[$fieldName];
            $textFilter = "%$textFilter%";
            $fieldName = $this->kebabToCamelCase($fieldName);
            $this->qb->andWhere("log.{$fieldName} LIKE :{$fieldName}")
                ->setParameter("$fieldName", $textFilter);
        }
    }

    function kebabToCamelCase($originalString): string
    {
        $formattedStr = str_replace('-', ' ', $originalString);
        $formattedStr = ucwords($formattedStr);
        $formattedStr = str_replace(' ', '', $formattedStr);
        return lcfirst($formattedStr);
    }
}
