<?php

namespace App\Controller;

use App\Entity\ApiKey;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class KeyController extends AbstractController
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

    #[Post('/api-key')]
    public function getApiKey(EntityManagerInterface $em, Request $request): JsonResponse
    {
        $uid = $this->getUser()->getUserIdentifier();

        $body = json_decode($request->getContent());

        /** @var \App\Repository\ApiKeyRepository $repository */
        $repository = $em->getRepository(ApiKey::class);
        $key = $repository->findOneBy(['uid' => $uid]);

        if(!empty($body->refresh) && $body->refresh && !empty($key)) {
            $em->remove($key);
            $em->flush();
            unset($key);
        }

        if(empty($key)) {
            $key = new ApiKey();
            $key->setApiKey(bin2hex(random_bytes(16)));
            $key->setUid($uid);
            $em->persist($key);
            $em->flush();
        }

        return new JsonResponse(["data" => $key]);
    }
}
