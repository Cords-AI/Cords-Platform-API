<?php

namespace App\EventListener;

use App\Utils\Util;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class CsrfTokenListener
{

    public function __construct(
        private EntityManagerInterface $em
    ) {
    }

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

        $keyEntity = Util::getApiKeyEntity($request, $this->em);

        $accessedUsingValidPlatformKey = $keyEntity && Util::platformKeyIsValid($request, $keyEntity) && empty($request->cookies->get('session'));

        if (!$accessedUsingValidPlatformKey && (!$token || $token != $_SESSION['csrf_token'])) {
            throw new AccessDeniedHttpException('Invalid CSRF token');
        }
    }
}
