<?php

namespace App\Security;

use App\Service\FirebaseService;
use App\Utils\Util;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class APIKeyAuthenticator extends AbstractAuthenticator
{

    public function __construct(
        private EntityManagerInterface $em
    ) {
    }

    public function supports(Request $request): ?bool
    {
        $userIsSignedOut = empty($request->cookies->get('session'));
        $uri = $_SERVER['REQUEST_URI'];
        $isApiKeyValidateUrl = $uri === '/api-key/validate';
        $isSystemUrl = str_starts_with($uri, "/system");
        return !empty($request->headers->get('x-api-key')) && !$isSystemUrl && !$isApiKeyValidateUrl && $userIsSignedOut;
    }

    public function authenticate(Request $request): Passport
    {
        $key = Util::getApiKeyEntity($request, $this->em);

        $securityUser = null;

        if ($key) {
            $account = $key->getAccount();
            $uid = $account->getUid();

            $firebaseService = new FirebaseService(HttpClient::create());
            $users = $firebaseService->getUsers();

            $fireBaseUser = FirebaseService::getMatchingFirebaseUser($users, $uid);
            $securityUser = User::createFromFireBaseUser($fireBaseUser);

            if ($securityUser) {
                $securityUser->setStatus($account->getStatus());
            }

            if (!Util::platformKeyIsValid($request, $key)) {
                $securityUser = null;
            }
        }

        $passport = new SelfValidatingPassport(new UserBadge("", function () use ($securityUser) {
            return $securityUser;
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
