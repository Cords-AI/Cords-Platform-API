<?php

namespace App\Controller;

use App\Dto\Admin\UserData;
use App\Entity\Account;
use App\Service\FirebaseService;
use Doctrine\Persistence\ManagerRegistry;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class AdminController extends AbstractController
{
    #[Get('/admin/users')]
    public function user(FirebaseService $firebase, ManagerRegistry $doctrine): JsonResponse
    {
        $em = $doctrine->getManager();
        /** @var \App\Repository\AccountRepository $repository */
        $repository = $em->getRepository(Account::class);
        $accounts = $repository->findAll();

        $rows = $firebase->getUsers();

        $correspondingAccounts = [];

        array_map(function ($account) use (&$correspondingAccounts) {
            $correspondingAccounts[$account->getUid()] = $account->getStatus();
        }, $accounts);

        foreach ($rows as $row) {
            $row->status = $correspondingAccounts[$row->uid];
        }

        $data = array_map(fn($row) => new UserData($row), $rows);
        $data = array_filter($data, fn(UserData $userData) => $userData->emailVerified);
        return new JsonResponse([
            "data" => $data
        ]);
    }

    #[Post('/admin/status')]
    public function status(Request $request, ManagerRegistry $doctrine): JsonResponse
    {
        $body = json_decode($request->getContent());
        $status = $body->status;
        $uid = $body->uid;

        $em = $doctrine->getManager();
        /** @var \App\Repository\AccountRepository $repository */
        $repository = $em->getRepository(Account::class);

        $account = $repository->findOneBy(['uid' => $uid]);
        $account->setStatus($status);
        $em->persist($account);
        $em->flush();

        return new JsonResponse([
            "data" => 'success'
        ]);
    }
}
