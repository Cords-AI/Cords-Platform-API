<?php

namespace App\Controller;

use App\Entity\Account;
use App\Entity\ApiKey;
use App\Entity\EnabledUrl;
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

        $repository = $em->getRepository(Account::class);
        $account = $repository->findOneBy(['uid' => $uid]);

        $key = new ApiKey();
        $key->setApiKey(bin2hex(random_bytes(16)));
        $key->setUid($uid);
        $key->setAccount($account);
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

    #[Post('/partner/enabled-url/api-key/{id}/add')]
    public function addEnabledUrl(EntityManagerInterface $em, string $id, Request $request): JsonResponse
    {
        $uid = $this->getUser()->getUserIdentifier();

        $body = json_decode($request->getContent());
        $url = $body->url;

        /** @var \App\Repository\ApiKeyRepository $repository */
        $repository = $em->getRepository(ApiKey::class);
        $key = $repository->findOneBy(['id' => $id, 'uid' => $uid]);

        $enabledUrl = new EnabledUrl();
        $enabledUrl->setUrl($url);
        $enabledUrl->setApiKeyId($key->getId());
        $enabledUrl->setApiKey($key);
        $key->addEnabledUrl($enabledUrl);

        $em->persist($enabledUrl);
        $em->persist($key);
        $em->flush();

        return new JsonResponse(["data" => 'url added']);
    }

    #[Delete('/partner/enabled-url/api-key/{apiKeyId}/remove/{urlId}')]
    public function deleteEnabledUrl(EntityManagerInterface $em, string $apiKeyId, string $urlId): JsonResponse
    {
        $uid = $this->getUser()->getUserIdentifier();

        /** @var \App\Repository\ApiKeyRepository $repository */
        $apiKeysRepository = $em->getRepository(ApiKey::class);
        $key = $apiKeysRepository->findOneBy(['id' => $apiKeyId, 'uid' => $uid]);

        /** @var \App\Repository\EnabledUrlRepository $repository */
        $enabledUrlRepository = $em->getRepository(EnabledUrl::class);
        $enabledUrl = $enabledUrlRepository->findOneBy(['id' => $urlId, 'apiKeyId' => $apiKeyId]);

        $key->removeEnabledUrl($enabledUrl);
        $em->remove($enabledUrl);
        $em->persist($key);
        $em->flush();

        return new JsonResponse(["data" => 'url removed']);
    }

    #[Get('/partner/enabled-urls/api-key/{id}')]
    public function getEnabledUrls(EntityManagerInterface $em, string $id): JsonResponse
    {
        $uid = $this->getUser()->getUserIdentifier();

        /** @var \App\Repository\ApiKeyRepository $repository */
        $repository = $em->getRepository(ApiKey::class);
        $key = $repository->findOneBy(['id' => $id, 'uid' => $uid]);

        return new JsonResponse(["data" => $key->getEnabledUrls()->getValues()]);
    }
}
