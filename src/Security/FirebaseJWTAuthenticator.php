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

class FirebaseJWTAuthenticator extends AbstractAuthenticator
{
    public function supports(Request $request): ?bool
    {
        $uri = $_SERVER['REQUEST_URI'];
        if($uri === "/api-key/validate") {
            return false;
        }
        return !empty($request->headers->get('x-api-key'));
    }

    public function authenticate(Request $request): Passport
    {
        $token = $request->headers->get('x-api-key');

        $user = User::create($token, "https://www.googleapis.com/service_accounts/v1/metadata/x509/securetoken@system.gserviceaccount.com");

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
