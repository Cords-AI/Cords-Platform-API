<?php

namespace App\Controller;

use App\Entity\ApiKey;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class PartnerController extends AbstractController
{
    #[Post('/partner/api-key/add')]
    public function addApiKey(EntityManagerInterface $em): JsonResponse
    {
        $uid = $this->getUser()->getUserIdentifier();

        $key = new ApiKey();
        $key->setApiKey(bin2hex(random_bytes(16)));
        $key->setUid($uid);
        $em->persist($key);
        $em->flush();

        return new JsonResponse(["data" => $key]);
    }

    #[Delete('/partner/api-key/delete/{id}')]
    public function deleteApiKey(EntityManagerInterface $em, string $id): JsonResponse
    {
        $uid = $this->getUser()->getUserIdentifier();

        /** @var \App\Repository\ApiKeyRepository $repository */
        $repository = $em->getRepository(ApiKey::class);
        $key = $repository->findOneBy(['id' => $id, 'uid' => $uid]);

        $key->setDeleted(true);
        $em->persist($key);
        $em->flush();

        return new JsonResponse(["data" => 'deleted']);
    }

    #[Get('/partner/api-key/list')]
    public function getApiKeysList(EntityManagerInterface $em, Request $request): JsonResponse
    {
        $uid = $this->getUser()->getUserIdentifier();

        /** @var \App\Repository\ApiKeyRepository $repository */
        $repository = $em->getRepository(ApiKey::class);

        $queryBuilder = $repository->createQueryBuilder("key");
        $queryBuilder->where("key.uid = :uid")
            ->andWhere($queryBuilder->expr()->orX(
                $queryBuilder->expr()->neq('key.deleted', 1),
                $queryBuilder->expr()->isNull('key.deleted')
            ))
            ->setParameter('uid', $uid);

        return new JsonResponse(["data" => $queryBuilder->getQuery()->getResult()]);
    }

    #[Get('/partner/api-key/{id}')]
    public function getApiKey(EntityManagerInterface $em, string $id): JsonResponse
    {
        $uid = $this->getUser()->getUserIdentifier();

        /** @var \App\Repository\ApiKeyRepository $repository */
        $repository = $em->getRepository(ApiKey::class);
        $key = $repository->findOneBy(['id' => $id, 'uid' => $uid]);

        return new JsonResponse(["data" => $key]);
    }
}
