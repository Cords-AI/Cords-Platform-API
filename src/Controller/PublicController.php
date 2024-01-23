<?php

namespace App\Controller;

use App\Entity\ApiKey;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations\Get;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class PublicController extends AbstractController
{
    #[Get('/api-key/validate')]
    public function validate(EntityManagerInterface $em, Request $request): JsonResponse
    {
        /** @var \App\Repository\ApiKeyRepository $repository */
        $repository = $em->getRepository(ApiKey::class);
        $apiKey = $request->headers->get('x-api-key');
        $key = $repository->findOneBy(['apiKey' => $apiKey]);

        if(empty($key)) {
            return new JsonResponse(null, 403);
        }

        return new JsonResponse(null, 200);
    }
}
