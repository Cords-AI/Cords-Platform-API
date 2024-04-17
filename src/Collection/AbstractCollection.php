<?php

namespace App\Collection;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

abstract class AbstractCollection
{
    protected ManagerRegistry $doctrine;

    protected array $collection;

    protected int $limit = 1000000;

    protected int $offset = 0;

    protected string $sortField = 'createdDate';

    protected string $sortDirection = 'ASC';

    protected int $total = 0;

    protected ?string $q = '';

    protected QueryBuilder $qb;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;

        $em = $this->doctrine->getManager();
        /** @var QueryBuilder $qb */
        $this->qb = $em->createQueryBuilder();
    }

    public function q($q): self
    {
        if ($q) {
            $this->q = $q;
        }
        return $this;
    }

    public function limit($limit): self
    {
        if ($limit) {
            $this->limit = $limit;
        }
        return $this;
    }

    public function offset($offset): self
    {
        if ($offset) {
            $this->offset = $offset;
        }
        return $this;
    }

    public function sortField($sortField): self
    {
        if ($sortField) {
            $this->sortField = $sortField;
        }
        return $this;
    }

    public function sortDirection($sortDirection): self
    {
        if ($sortDirection) {
            $this->sortDirection = $sortDirection;
        }
        return $this;
    }

    abstract public function fetchRows(): array;

    public function returnAsJSON(): array
    {
        return [
            'meta' => [
                'total' => $this->total
            ],
            'data' => $this->collection
        ];
    }
}
