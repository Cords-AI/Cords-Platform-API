<?php

namespace App\Email;

class NewAccountRequestViewModel
{
    public string $accountLink;

    public readonly string $year;

    public string $clientLanguage;

    public function __construct()
    {
        $this->year = date('Y');
    }
}
