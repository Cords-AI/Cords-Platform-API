<?php

namespace App\Controller;

use App\Collection\LogCollection;
use App\Entity\Account;
use App\Entity\ApiKey;
use App\Entity\EnabledUrl;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Patch;
use FOS\RestBundle\Controller\Annotations\Post;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ApprovedPartnerController extends AbstractController
{
    #[Post('/partner/approved/api-key/add')]
    public function addApiKey(EntityManagerInterface $em, Request $request): JsonResponse
    {
        $body = json_decode($request->getContent());

        $uid = $this->getUser()->getUserIdentifier();

        $repository = $em->getRepository(Account::class);
        $account = $repository->findOneBy(['uid' => $uid]);

        $key = new ApiKey();
        $key->setApiKey(bin2hex(random_bytes(16)));
        $key->setUid($uid);
        $key->setAccount($account);
        $key->setName(trim($body->name));
        $key->setType($body->type);
        $em->persist($key);
        $em->flush();

        return new JsonResponse(["data" => $key]);
    }

    #[Patch('/partner/approved/api-key/update/{id}')]
    public function updateKeyName(EntityManagerInterface $em, Request $request, string $id): JsonResponse
    {
        $body = json_decode($request->getContent());
        $uid = $this->getUser()->getUserIdentifier();

        $repository = $em->getRepository(ApiKey::class);
        $apiKey = $repository->findOneBy(['id' => $id, 'uid' => $uid]);

        $apiKey->setName(trim($body->name));
        $em->persist($apiKey);
        $em->flush();

        return new JsonResponse(["data" => $apiKey]);
    }

    #[Delete('/partner/approved/api-key/delete/{id}')]
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

    #[Get('/partner/approved/api-key/list')]
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

    #[Get('/partner/approved/api-key/{id}')]
    public function getApiKey(EntityManagerInterface $em, string $id): JsonResponse
    {
        $uid = $this->getUser()->getUserIdentifier();

        /** @var \App\Repository\ApiKeyRepository $repository */
        $repository = $em->getRepository(ApiKey::class);
        $key = $repository->findOneBy(['id' => $id, 'uid' => $uid]);

        return new JsonResponse(["data" => $key]);
    }

    #[Post('/partner/approved/enabled-url/api-key/{id}/add')]
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

    #[Delete('/partner/approved/enabled-url/api-key/{apiKeyId}/remove/{urlId}')]
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

    #[Get('/partner/approved/enabled-urls/api-key/{id}')]
    public function getEnabledUrls(EntityManagerInterface $em, string $id): JsonResponse
    {
        $uid = $this->getUser()->getUserIdentifier();

        /** @var \App\Repository\ApiKeyRepository $repository */
        $repository = $em->getRepository(ApiKey::class);
        $key = $repository->findOneBy(['id' => $id, 'uid' => $uid]);

        return new JsonResponse(["data" => $key->getEnabledUrls()->getValues()]);
    }

    #[Get('/partner/approved/report')]
    public function getReport(Request $request, LogCollection $logCollection): JsonResponse
    {
        $filters = $request->get('filters');

        $page = $request->get('page');
        $search = $request->get('search');

        $uid = $this->getUser()->getUserIdentifier();

        $logCollection->userUid($uid)
            ->limitToRelatedApiKeys(true)
            ->filters($filters)
            ->page($page)
            ->search($search)
            ->sort($request->get('sort-by'), $request->get('descending'))
            ->fetchRows();

        return new JsonResponse($logCollection->returnAsJSON());
    }

    #[Get("/partner/approved/report/export")]
    public function exportLogs(Request $request, LogCollection $logCollection)
    {
        $filters = $request->get('filters');
        $search = $request->get('search');

        $uid = $this->getUser()->getUserIdentifier();

        $logCollection->userUid($uid)
            ->limitToRelatedApiKeys(true)
            ->limit(10000000)
            ->filters($filters)
            ->page(1)
            ->search($search)
            ->sort($request->get('sort-by'), $request->get('descending'))
            ->fetchRows();

        $logCollection->render();
        $logCollection->send();

        return new JsonResponse([]);
    }
}
