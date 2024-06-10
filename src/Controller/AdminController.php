<?php

namespace App\Controller;

use App\Collection\AccountCollection;
use App\Dto\Admin\AccountData;
use App\Dto\Admin\UserData;
use App\Entity\Account;
use App\Entity\ApiKey;
use App\Repository\AccountRepository;
use App\RequestParams\StatusParams;
use App\Service\FirebaseService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class AdminController extends AbstractController
{
    #[Get('/admin/users')]
    public function getUsers(Request $request, AccountCollection $collection): JsonResponse
    {
        $collection
            ->filters($request->get('filters'))
            ->search($request->get('search'))
            ->limit($request->get('limit'))
            ->page($request->get('page'))
            ->sort($request->get('sort-by'), $request->get('descending'))
            ->execute();

        $data = array_map(fn ($row) => new UserData($row), $collection->getRows());

        return new JsonResponse([
            "meta" => [
                "total" => $collection->getTotal(),
                "page" => $collection->getPage()
            ],
            "data" => $data
        ]);
    }

    #[Get('/admin/users/{id}')]
    public function getUserRequest(
        AccountCollection $collection,
        AccountRepository $repository,
        $id
    ): JsonResponse {
        $collection->execute();
        $rows = $collection->getRows();

        $firebaseUser = current(array_filter($rows, fn($row) => $row->uid === $id));
        $account = $repository->findOneBy(['uid' => $id]);
        $accountData = new AccountData($account, $firebaseUser);

        return new JsonResponse([
            "data" => $accountData
        ]);
    }

    #[Post('/admin/status')]
    public function status(
        StatusParams $params,
        AccountRepository $repository,
        EntityManagerInterface $em
    ): JsonResponse {

        /** @var Account $account */
        $account = $repository->findOneBy(['uid' => $params->uid]);
        if (!$account) {
            $account = new Account();
            $account->setUid($params->uid);
        }
        $account->setStatus($params->status);
        $em->persist($account);
        $em->flush();

        return new JsonResponse([
            "data" => 'success'
        ]);
    }

    #[Post('/admin/manage-admin')]
    public function manageAdminRole(FirebaseService $fireBase, Request $request): JsonResponse {
        $body = json_decode($request->getContent());
        $uid = $body->uid;
        $action = $body->action;

        $fireBase->manageAdminRole($uid, $action);

        return new JsonResponse([
            "data" => 'success'
        ]);
    }

    #[Get('/admin/api-keys')]
    public function getAllApiKeys(ManagerRegistry $doctrine, Request $request): JsonResponse
    {
        $apiKeyRepository = $doctrine->getRepository(ApiKey::class);
        $keys = $apiKeyRepository->findAll();

        $formatted = $request->get('select-formatted');

        if ($formatted === 'true') {
            $formattedKeys = array_map(fn($key) => ['label' => $key->getApiKey(), 'value' => $key->getId()], $keys);
            return new JsonResponse(["data" => $formattedKeys]);
        }

        return new JsonResponse(["data" => $keys]);
    }
}
