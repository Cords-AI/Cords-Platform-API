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

class FirebaseCookieAuthenticator extends AbstractAuthenticator
{
    public function supports(Request $request): ?bool
    {
        return !empty($request->headers->get('cookie'));
    }

    public function authenticate(Request $request): Passport
    {
        $user = null;

        $cookie = $request->headers->get('cookie');
        $cookies = explode(";", $cookie);
        $cookies = array_map(fn($cookie) => trim($cookie), $cookies);
        $cookies = array_map(fn($cookie) => explode("=", $cookie), $cookies);
        $sessions = array_filter($cookies, fn($cookie) => $cookie[0] == 'session');
        $session = current($sessions);
        if ($session !== false) {
            $idToken = $session[1];
            $user = User::create($idToken);
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
