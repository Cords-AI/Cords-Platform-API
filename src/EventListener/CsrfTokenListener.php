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

        if (!in_array($method, ['GET', 'OPTIONS'])) {
            $token = $request->headers->get('X-Csrf-Token');
            session_start();

            if (!$token || $token != $_SESSION['csrf_token']) {
                throw new AccessDeniedHttpException('Invalid CSRF token');
            }
        }
    }
}
