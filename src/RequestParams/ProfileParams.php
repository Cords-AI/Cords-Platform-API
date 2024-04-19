<?php

namespace App\RequestParams;

class ProfileParams extends RequestParams
{
    public ?string $organization;

    public ?string $purpose;

    protected function init($body)
    {
        $this->organization = $body->organization;
        $this->purpose = $body->purpose;
    }
}
