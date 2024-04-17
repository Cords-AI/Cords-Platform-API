<?php

namespace App\RequestParams;

class StatusParams extends RequestParams
{
    public ?string $status;

    public string $uid;

    protected function init($body)
    {
        $this->status = $body->status;
        $this->uid = $body->uid;
    }
}
