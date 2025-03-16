<?php

namespace App\Controller;

use App\Collection\LogCollection;
use App\Entity\Account;
use App\Entity\ApiKey;
use App\Entity\EnabledIp;
use App\Entity\EnabledUrl;
use App\Repository\EnabledIpRepository;
use App\Utils\ClientContext;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Patch;
use FOS\RestBundle\Controller\Annotations\Post;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

use OpenApi\Attributes as OA;

#[OA\Info(title: "CORDS Platform API", version: "", description: "The CORDS Platform API can be used to get the Search Log and manage API keys.\n\nVisit https://partners.cords.ai to generate a Platform API key.")]
#[OA\Parameter(
    name: "x-api-key",
    in: "header",
    required: true,
    schema: new OA\Schema(type: "string"),
    parameter: "ApiKeyHeader"
)]
#[OA\Parameter(
    name: "referer",
    in: "header",
    required: false,
    description: "Platform API keys must have either an IP or Referer restriction",
    schema: new OA\Schema(type: "string"),
    parameter: "RefererHeader"
)]
#[OA\Parameter(
    name: "id",
    in: "path",
    required: true,
    description: "The id of the API key",
    schema: new OA\Schema(type: "string"),
    parameter: "ApiKeyId"
)]
#[OA\Parameter(
    name: "apiKeyId",
    in: "path",
    required: true,
    description: "The id of the API key",
    schema: new OA\Schema(type: "integer"),
    parameter: "ApiKeyId2"
)]
class ApprovedPartnerController extends AbstractController
{
    #[Post('/partner/approved/api-key/add')]
    #[OA\Post(
        path: "/partner/approved/api-key/add",
        summary: "/partner/approved/api-key/add",
        description: "Add an API key.",
        parameters: [
            new OA\Parameter(ref: "#/components/parameters/ApiKeyHeader"),
            new OA\Parameter(ref: "#/components/parameters/RefererHeader"),
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                type: "object",
                required: ["name", "type"],
                properties: [
                    new OA\Property(property: "name", type: "string"),
                    new OA\Property(property: "type", type: "string", description: "Either 'dev' or 'prod'"),
                ]
            )
        ),
        responses: [new OA\Response(response: 200, description: "", content: new OA\JsonContent(type: "object"))]
    )]
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
    #[OA\Patch(
        path: "/partner/approved/api-key/update/{id}",
        summary: "/partner/approved/api-key/update/{id}",
        description: "Rename an API key.",
        parameters: [
            new OA\Parameter(ref: "#/components/parameters/ApiKeyHeader"),
            new OA\Parameter(ref: "#/components/parameters/RefererHeader"),
            new OA\Parameter(ref: "#/components/parameters/ApiKeyId"),
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                type: "object",
                required: ["name"],
                properties: [
                    new OA\Property(property: "name", type: "string"),
                ]
            )
        ),
        responses: [new OA\Response(response: 200, description: "", content: new OA\JsonContent(type: "object"))]
    )]
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
    #[OA\Delete(
        path: "/partner/approved/api-key/delete/{id}",
        summary: "/partner/approved/api-key/delete/{id}",
        description: "Delete an API key.",
        parameters: [
            new OA\Parameter(ref: "#/components/parameters/ApiKeyHeader"),
            new OA\Parameter(ref: "#/components/parameters/RefererHeader"),
            new OA\Parameter(ref: "#/components/parameters/ApiKeyId"),
        ],
        responses: [new OA\Response(response: 200, description: "", content: new OA\JsonContent(type: "object"))]
    )]
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
    #[OA\Get(
        path: "/partner/approved/api-key/list",
        summary: "/partner/approved/api-key/list",
        description: "List your API keys.",
        parameters: [
            new OA\Parameter(ref: "#/components/parameters/ApiKeyHeader"),
            new OA\Parameter(ref: "#/components/parameters/RefererHeader"),
        ],
        responses: [new OA\Response(response: 200, description: "", content: new OA\JsonContent(type: "object"))]
    )]
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

        $keys = $queryBuilder->getQuery()->getResult();

        $formatted = $request->get('select-formatted');

        if ($formatted === 'true') {
            $formattedKeys = array_map(fn($key) => ['label' => $key->getApiKey(), 'value' => $key->getId()], $keys);
            return new JsonResponse(["data" => $formattedKeys]);
        }

        return new JsonResponse(["data" => $keys]);
    }

    #[Get('/partner/approved/api-key/{id}')]
    #[OA\Get(
        path: "/partner/approved/api-key/{id}",
        summary: "/partner/approved/api-key/{id}",
        description: "Get API key details",
        parameters: [
            new OA\Parameter(ref: "#/components/parameters/ApiKeyHeader"),
            new OA\Parameter(ref: "#/components/parameters/RefererHeader"),
            new OA\Parameter(ref: "#/components/parameters/ApiKeyId"),
        ],
        responses: [new OA\Response(response: 200, description: "", content: new OA\JsonContent(type: "object"))]
    )]
    public function getApiKey(EntityManagerInterface $em, string $id): JsonResponse
    {
        $uid = $this->getUser()->getUserIdentifier();

        /** @var \App\Repository\ApiKeyRepository $repository */
        $repository = $em->getRepository(ApiKey::class);
        $key = $repository->findOneBy(['id' => $id, 'uid' => $uid]);

        return new JsonResponse(["data" => $key]);
    }

    #[Post('/partner/approved/enabled-url/api-key/{id}/add')]
    #[OA\Post(
        path: "/partner/approved/enabled-url/api-key/{id}/add",
        summary: "/partner/approved/enabled-url/api-key/{id}/add",
        description: "Authorize a Referer",
        parameters: [
            new OA\Parameter(ref: "#/components/parameters/ApiKeyHeader"),
            new OA\Parameter(ref: "#/components/parameters/RefererHeader"),
            new OA\Parameter(ref: "#/components/parameters/ApiKeyId"),
            new OA\RequestBody(
                required: true,
                description: "The url to authorize",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(
                            property: "url",
                            type: "string"
                        )
                    ]
                )
            )
        ],
        responses: [new OA\Response(response: 200, description: "", content: new OA\JsonContent(type: "object"))]
    )]
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
    #[OA\Delete(
        path: "/partner/approved/enabled-url/api-key/{apiKeyId}/remove/{urlId}",
        summary: "/partner/approved/enabled-url/api-key/{apiKeyId}/remove/{urlId}",
        description: "Remove a Referer",
        parameters: [
            new OA\Parameter(ref: "#/components/parameters/ApiKeyHeader"),
            new OA\Parameter(ref: "#/components/parameters/RefererHeader"),
            new OA\Parameter(ref: "#/components/parameters/ApiKeyId2"),
            new OA\Parameter(
                name: "urlId",
                in: "path",
                required: true,
                description: "The Referer ID to remove",
                schema: new OA\Schema(type: "string"),
            )
        ],
        responses: [new OA\Response(response: 200, description: "", content: new OA\JsonContent(type: "object"))]
    )]
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
    #[OA\Get(
        path: "/partner/approved/enabled-urls/api-key/{id}",
        summary: "/partner/approved/enabled-urls/api-key/{id}",
        description: "List authorized Referers",
        parameters: [
            new OA\Parameter(ref: "#/components/parameters/ApiKeyHeader"),
            new OA\Parameter(ref: "#/components/parameters/RefererHeader"),
            new OA\Parameter(ref: "#/components/parameters/ApiKeyId"),
        ],
        responses: [new OA\Response(response: 200, description: "", content: new OA\JsonContent(type: "object"))]
    )]
    public function getEnabledUrls(EntityManagerInterface $em, string $id): JsonResponse
    {
        $uid = $this->getUser()->getUserIdentifier();

        /** @var \App\Repository\ApiKeyRepository $repository */
        $repository = $em->getRepository(ApiKey::class);
        $key = $repository->findOneBy(['id' => $id, 'uid' => $uid]);

        return new JsonResponse(["data" => $key->getEnabledUrls()->getValues()]);
    }

    #[Post('/partner/approved/enabled-ip/api-key/{id}/add')]
    #[OA\Post(
        path: "/partner/approved/enabled-ip/api-key/{id}/add",
        summary: "/partner/approved/enabled-ip/api-key/{id}/add",
        description: "Authorize an IP",
        parameters: [
            new OA\Parameter(ref: "#/components/parameters/ApiKeyHeader"),
            new OA\Parameter(ref: "#/components/parameters/RefererHeader"),
            new OA\Parameter(ref: "#/components/parameters/ApiKeyId"),
            new OA\RequestBody(
                required: true,
                description: "The ip to authorize",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(
                            property: "ip",
                            type: "string"
                        )
                    ]
                )
            )
        ],
        responses: [new OA\Response(response: 200, description: "", content: new OA\JsonContent(type: "object"))]
    )]
    public function addEnabledIp(EntityManagerInterface $em, string $id, Request $request): JsonResponse
    {
        $uid = $this->getUser()->getUserIdentifier();

        $body = json_decode($request->getContent());
        $ip = $body->ip;

        /** @var \App\Repository\ApiKeyRepository $repository */
        $repository = $em->getRepository(ApiKey::class);
        $key = $repository->findOneBy(['id' => $id, 'uid' => $uid]);

        $enabledIp = new EnabledIp();
        $enabledIp->setIp($ip);
        $enabledIp->setApiKeyId($key->getId());
        $enabledIp->setApiKey($key);
        $key->addEnabledIp($enabledIp);

        $em->persist($enabledIp);
        $em->persist($key);
        $em->flush();

        return new JsonResponse(["data" => 'IP added']);
    }

    #[Delete('/partner/approved/enabled-ip/api-key/{apiKeyId}/remove/{ipId}')]
    #[OA\Delete(
        path: "/partner/approved/enabled-ip/api-key/{apiKeyId}/remove/{ipId}",
        summary: "/partner/approved/enabled-ip/api-key/{apiKeyId}/remove/{ipId}",
        description: "Remove an IP",
        parameters: [
            new OA\Parameter(ref: "#/components/parameters/ApiKeyHeader"),
            new OA\Parameter(ref: "#/components/parameters/RefererHeader"),
            new OA\Parameter(ref: "#/components/parameters/ApiKeyId2"),
            new OA\Parameter(
                name: "ipId",
                in: "path",
                required: true,
                description: "The ID of IP to remove",
                schema: new OA\Schema(type: "string"),
            )
        ],
        responses: [new OA\Response(response: 200, description: "", content: new OA\JsonContent(type: "object"))]
    )]
    public function deleteEnabledIp(EntityManagerInterface $em, string $apiKeyId, string $ipId): JsonResponse
    {
        $uid = $this->getUser()->getUserIdentifier();

        /** @var \App\Repository\ApiKeyRepository $repository */
        $apiKeysRepository = $em->getRepository(ApiKey::class);
        $key = $apiKeysRepository->findOneBy(['id' => $apiKeyId, 'uid' => $uid]);

        /** @var EnabledIpRepository $repository */
        $enabledIpRepository = $em->getRepository(EnabledIp::class);
        $enabledIp = $enabledIpRepository->findOneBy(['id' => $ipId, 'apiKeyId' => $apiKeyId]);

        $key->removeEnabledIp($enabledIp);
        $em->remove($enabledIp);
        $em->persist($key);
        $em->flush();

        return new JsonResponse(["data" => 'IP removed']);
    }

    #[Get('/partner/approved/report')]
    #[OA\Get(
        path: "/partner/approved/report",
        summary: "/partner/approved/report",
        description: "Get CORDS Search log",
        parameters: [
            new OA\Parameter(ref: "#/components/parameters/ApiKeyHeader"),
            new OA\Parameter(ref: "#/components/parameters/RefererHeader"),
            new OA\Parameter(in: "query", name: "filters[email]", description: "Account holder email must include value", schema: new OA\Schema(type: "string")),
            new OA\Parameter(in: "query", name: "filters[search-term]", description: "Search term must include value", schema: new OA\Schema(type: "string")),
            new OA\Parameter(in: "query", name: "filters[key-type]", description: "Either 'dev' or 'prod'", schema: new OA\Schema(type: "string")),
            new OA\Parameter(in: "query", name: "filters[api-key]", description: "The ID of the api-key. Not the key itself!", schema: new OA\Schema(type: "string")),
            new OA\Parameter(in: "query", name: "filters[province]", description: "The provincial abbreviation (e.g. ON) that the search was set to.", schema: new OA\Schema(type: "string")),
            new OA\Parameter(in: "query", name: "filters[postal-code]", description: "The postal code that the search was set to must include value", schema: new OA\Schema(type: "string")),
            new OA\Parameter(in: "query", name: "filters[country]", description: "The country that the search was set to must include value", schema: new OA\Schema(type: "string")),
            new OA\Parameter(in: "query", name: "page", description: "25 results are included per page", schema: new OA\Schema(type: "string", default: "1")),
        ],
        responses: [new OA\Response(response: 200, description: "", content: new OA\JsonContent(type: "object"))]
    )]
    public function getReport(Request $request, LogCollection $logCollection, ClientContext $clientContext): JsonResponse
    {
        $filters = $request->get('filters');

        $page = $request->get('page');
        $search = $request->get('search');

        $uid = $this->getUser()->getUserIdentifier();
        $isAdmin = in_array('ROLE_ADMIN', $this->getUser()->getRoles());

        $logCollection->userUid($uid)
            ->isAdmin($isAdmin)
            ->filters($filters)
            ->page($page)
            ->search($search)
            ->clientLang($clientContext->langCode)
            ->sort($request->get('sort-by'), $request->get('descending'))
            ->fetchRows();

        return new JsonResponse($logCollection->returnAsJSON());
    }

    #[Get("/partner/approved/report/export")]
    #[OA\Get(
        path: "/partner/approved/report/export",
        summary: "/partner/approved/report/export",
        description: "Get CORDS Search log as a .xlsx",
        parameters: [
            new OA\Parameter(ref: "#/components/parameters/ApiKeyHeader"),
            new OA\Parameter(ref: "#/components/parameters/RefererHeader"),
            new OA\Parameter(in: "query", name: "filters[email]", description: "Account holder email must include value", schema: new OA\Schema(type: "string")),
            new OA\Parameter(in: "query", name: "filters[search-term]", description: "Search term must include value", schema: new OA\Schema(type: "string")),
            new OA\Parameter(in: "query", name: "filters[key-type]", description: "Either 'dev' or 'prod'", schema: new OA\Schema(type: "string")),
            new OA\Parameter(in: "query", name: "filters[api-key]", description: "The ID of the api-key. Not the key itself!", schema: new OA\Schema(type: "string")),
            new OA\Parameter(in: "query", name: "filters[province]", description: "The provincial abbreviation (e.g. ON) that the search was set to.", schema: new OA\Schema(type: "string")),
            new OA\Parameter(in: "query", name: "filters[postal-code]", description: "The postal code that the search was set to must include value", schema: new OA\Schema(type: "string")),
            new OA\Parameter(in: "query", name: "filters[country]", description: "The country that the search was set to must include value", schema: new OA\Schema(type: "string")),
            new OA\Parameter(in: "query", name: "page", description: "25 results are included per page", schema: new OA\Schema(type: "string", default: "1")),
        ],
        responses: [new OA\Response(response: 200, description: "", content: new OA\JsonContent(type: "object"))]
    )]
    public function exportLogs(Request $request, LogCollection $logCollection)
    {
        $filters = $request->get('filters');
        $search = $request->get('search');

        $uid = $this->getUser()->getUserIdentifier();
        $isAdmin = in_array('ROLE_ADMIN', $this->getUser()->getRoles());

        $logCollection->userUid($uid)
            ->isAdmin($isAdmin)
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
