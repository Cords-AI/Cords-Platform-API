<?php

namespace App\Collection;

use App\Entity\Term;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class TermCollection
{
    private ?array $rows = [];

    private string $sort = 'version';

    private int $limit = 25;

    private bool $descending = true;

    private array|null $filters = null;

    public function __construct(
        private readonly ManagerRegistry $doctrine
    )
    {
    }

    public function sort($sort, $descending): static
    {
        if ($sort) {
            $this->sort = $sort;
        }
        if ($descending !== null) {
            $this->descending = $descending === true;
        }
        return $this;
    }

    public function limit($limit): static
    {
        if ($limit) {
            $this->limit = $limit;
        }
        return $this;
    }

    public function filters($filters): static
    {
        if ($filters) {
            $this->filters = array_map(fn($row) => explode(",", $row), $filters);
        }
        return $this;
    }

    public function execute(): void
    {
        $em = $this->doctrine->getManager();
        /** @var QueryBuilder $qb */
        $qb = $em->createQueryBuilder();
        $order = $this->descending ? 'DESC' : 'ASC';

        $qb->select("term")
            ->from(Term::class, 'term');

        if (!empty($this->filters['name'])) {
            $name = $this->filters['name'];
            $qb->andWhere("term.name = :name");
            $qb->setParameter('name', $name);
        }

        $qb->orderBy($this->sort, $order);

        $results = $qb->getQuery()->execute();
        $this->rows = $results;
    }

    public function getRows(): array
    {
        return $this->rows;
    }
}
