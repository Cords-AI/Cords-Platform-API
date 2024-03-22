<?php

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class CsrfTokenListener
{
    public function onKernelController(ControllerEvent $event)
    {
        $request = $event->getRequest();
        $method = $request->getMethod();
        $uri = $request->getRequestUri();

        if (str_starts_with($uri, "/system")) {
            return;
        }

        if (in_array($method, ['GET', 'OPTIONS'])) {
            return;
        }

        $token = $request->headers->get('X-Csrf-Token');
        session_start();

        if (!$token || $token != $_SESSION['csrf_token']) {
            throw new AccessDeniedHttpException('Invalid CSRF token');
        }
    }
}
