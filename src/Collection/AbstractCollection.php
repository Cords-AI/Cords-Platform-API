<?php

namespace App\Collection;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Shuchkin\SimpleXLSXGen;

abstract class AbstractCollection
{
    protected ManagerRegistry $doctrine;

    protected array $collection;

    protected int $limit = 1000000;

    protected int $total = 0;

    protected QueryBuilder $qb;

    protected int $page = 1;

    protected string $sort = 'createdDate';

    protected bool $descending = true;

    protected string $search = '';

    protected array $exportableRows = [];

    protected array $exportHeaders = [];

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;

        $em = $this->doctrine->getManager();
        /** @var QueryBuilder $qb */
        $this->qb = $em->createQueryBuilder();
    }

    public function limit($limit): self
    {
        if ($limit) {
            $this->limit = $limit;
        }
        return $this;
    }

    public function sort($sort, $descending): static
    {
        if ($sort) {
            $this->sort = $sort;
        }
        if ($descending !== null) {
            $this->descending = $descending === 'true';
        }
        return $this;
    }

    abstract public function fetchRows(): array;

    public function returnAsJSON(): array
    {
        return [
            'meta' => [
                'total' => $this->total,
                'page' => $this->page
            ],
            'data' => $this->collection
        ];
    }

    abstract protected function setHeaders(): void;

    abstract protected function setExportableRows(): void;

    public function render(): self
    {
        $this->setHeaders();
        $this->setExportableRows();
        return $this;
    }

    public function send(): void
    {
        $this->outputWritableData($this->exportableRows);
        exit;
    }

    public function outputWritableData($rows): void
    {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
        header("Access-Control-Allow-Origin:$origin");
        header('Access-Control-Allow-Credentials:true');
        SimpleXLSXGen::fromArray($rows)->download();
    }
}
