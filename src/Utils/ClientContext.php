<?php

namespace App\Utils;

use Symfony\Component\HttpFoundation\RequestStack;

class ClientContext
{
    public string $langCode;

    public string $host;

    public function __construct(RequestStack $requestStack)
    {
        $request = $requestStack->getCurrentRequest();
        $this->langCode = $request->headers->get('client-langcode') ?? "en";
        $this->host = "https://{$request->headers->get('client-hostname')}";
    }
}
