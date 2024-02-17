<?php

namespace App\Controller;

use App\Entity\ApiKey;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class AuthenticatedController extends AbstractController
{
    #[Get('/authenticated/user')]
    public function user(): JsonResponse
    {
        $user = $this->getUser();
        return new JsonResponse(["data" => $user]);
    }
}
