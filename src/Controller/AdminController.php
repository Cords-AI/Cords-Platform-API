<?php

namespace App\Controller;

use App\Collection\AccountCollection;
use App\Dto\Admin\UserData;
use App\Repository\AccountRepository;
use App\RequestParams\StatusParams;
use Doctrine\ORM\EntityManagerInterface;
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
        $collection
            ->filters($request->get('filters'))
            ->search($request->get('search'))
            ->limit($request->get('limit'))
            ->page($request->get('page'))
            ->sort($request->get('sort-by'), $request->get('descending'))
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
    public function status(
        StatusParams $params,
        AccountRepository $repository,
        EntityManagerInterface $em
    ): JsonResponse {

        $account = $repository->findOneBy(['uid' => $params->uid]);
        $account->setStatus($params->status);
        $em->persist($account);
        $em->flush();

        return new JsonResponse([
            "data" => 'success'
        ]);
    }
}
