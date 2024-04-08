<?php

namespace App\Collection;

interface CollectionInterface
{
    public function search($search): static;
    public function limit($limit): static;
    public function page($page): static;
    public function sort($sort, $descending): static;
    public function execute(): void;
    public function getRows(): array;
    public function getTotal(): int;
    public function getPage(): int;
}
