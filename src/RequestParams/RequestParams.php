<?php

namespace App\RequestParams;

use Symfony\Component\HttpFoundation\RequestStack;

abstract class RequestParams
{
    public function __construct(RequestStack $requestStack)
    {
        $request = $requestStack->getCurrentRequest();
        $this->init(json_decode($request->getContent()));
    }

    abstract protected function init($body);
}
