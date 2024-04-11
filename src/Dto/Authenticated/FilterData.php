<?php

namespace App\Dto\Authenticated;

use App\Entity\Filter;

class FilterData
{
    public int $id;

    public string $name;

    public array $filter;

    public function __construct(Filter $data)
    {
        $this->id = $data->getId();
        $this->name = $data->getName();
        $this->filter = json_decode($data->getFilter(), true);
    }
}