<?php

namespace App\Collection;

use App\Entity\Account;
use App\Service\FirebaseService;
use Doctrine\Persistence\ManagerRegistry;

class AccountCollection implements CollectionInterface
{
    private string $search = '';

    private array|null $filters = null;

    private int $limit = 25;

    private int $offset = 0;

    private int|null $total = null;

    private array|null $rows = null;

    private string $sort = 'created';

    private bool $descending = true;

    public function __construct(
        private ManagerRegistry $doctrine,
        private FirebaseService $firebase
    ) {
    }

    public function filters($filters): static
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

    public function limit($limit): static
    {
        if ($limit) {
            $this->limit = $limit;
        }
        return $this;
    }

    public function offset($offset): static
    {
        if ($offset) {
            $this->offset = $offset;
        }
        return $this;
    }

    public function sort($sort): static
    {
        if ($sort) {
            if (strpos($sort, '-') === 0) {
                $this->descending = true;
                $sort = substr($sort, 1);
            } else {
                $this->descending = false;
            }
            $this->sort = $sort;
        }
        return $this;
    }

    public function execute(): void
    {
        $em = $this->doctrine->getManager();
        /** @var \App\Repository\AccountRepository $repository */
        $repository = $em->getRepository(Account::class);
        $accounts = $repository->findAll();

        $rows = $this->firebase->getUsers();

        $correspondingAccounts = [];

        array_map(function ($account) use (&$correspondingAccounts) {
            $correspondingAccounts[$account->getUid()] = $account->getStatus();
        }, $accounts);

        foreach ($rows as $row) {
            $row->status = $correspondingAccounts[$row->uid];
        }

        $rows = array_filter($rows, fn($row) => $row->emailVerified);
        array_walk($rows, function ($row) {
            $row->created = strtotime($row->metadata->creationTime);
            if (!$row->status) {
                $row->status = 'pending';
            }
        });

        if ($this->search) {
            $rows = array_filter($rows, fn($row) => strpos($row->email, $this->search) !== false);
        }

        if (isset($this->filters['status'])) {
            $rows = array_filter($rows, function ($row) {
                return in_array($row->status, $this->filters['status']);
            });
        }

        $order = array_map(fn($row) => $row->{$this->sort}, $rows);
        array_multisort($order, $this->descending ? SORT_DESC : SORT_ASC, $rows);

        $this->total = count($rows);

        $this->rows = array_splice($rows, $this->offset, $this->limit);
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function getPage(): int
    {
        return (($this->offset / $this->limit) ?? 0) + 1;
    }

    public function getRows(): array
    {
        return $this->rows;
    }
}
