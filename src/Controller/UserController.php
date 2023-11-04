<?php

namespace App\Controller;

use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;

class UserController extends AbstractController
{
    #[Get('/user')]
    public function getCurrentUser(): JsonResponse
    {
        $user = $this->getUser();
        return new JsonResponse($user);
    }

    #[Post('/user/sign-in')]
    public function signIn(): JsonResponse
    {
        return new JsonResponse();
    }

    #[Post('/user/sign-out')]
    public function signOut(Security $security): JsonResponse
    {
        $security->logout(false);
        return new JsonResponse();
    }
}
