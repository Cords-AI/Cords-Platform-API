<?php

namespace App\Controller;

use App\Collection\AccountCollection;
use App\Dto\Admin\UserData;
use App\Entity\Account;
use Doctrine\Persistence\ManagerRegistry;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class AdminController extends AbstractController
{
    #[Get('/admin/users')]
    public function user(Request $request, AccountCollection $collection): JsonResponse
    {
        $limit = $request->get('limit');
        $offset = $request->get('offset');

        $collection
            ->limit($limit)
            ->offset($offset)
            ->execute();

        $data = array_map(fn($row) => new UserData($row), $collection->getRows());

        return new JsonResponse([
            "meta" => [
                "total" => $collection->getTotal(),
                "page" => $collection->getPage()
            ],
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
