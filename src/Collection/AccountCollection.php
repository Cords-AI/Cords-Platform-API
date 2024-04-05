<?php

namespace App\Collection;

use App\Entity\Account;
use App\Service\FirebaseService;
use Doctrine\Persistence\ManagerRegistry;

class AccountCollection implements CollectionInterface
{
    private int $limit = 25;

    private int $offset = 0;

    private int|null $total = null;

    private array|null $rows = null;

    public function __construct(
        private ManagerRegistry $doctrine,
        private FirebaseService $firebase
    )
    {
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
