<?php

namespace App\Collection;

interface CollectionInterface
{
    public function limit($limit): static;
    public function offset($limit): static;
    public function execute(): void;
    public function getRows(): array;
    public function getTotal(): int;
    public function getPage(): int;
}
