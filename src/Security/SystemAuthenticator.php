<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class SystemAuthenticator extends AbstractAuthenticator
{
    public function supports(Request $request): ?bool
    {
        $uri = $_SERVER['REQUEST_URI'];
        return str_starts_with($uri, "/system");
    }

    public function authenticate(Request $request): Passport
    {
        $key = $request->headers->get('x-api-key');

        $user = null;
        if (!empty($key) && $key === $_ENV['SYSTEM_TOKEN']) {
            $user = new SystemUser();
        }

        $passport = new SelfValidatingPassport(new UserBadge("", function () use ($user) {
            return $user;
        }));

        return $passport;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new JsonResponse([], Response::HTTP_UNAUTHORIZED);
    }
}
