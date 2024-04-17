<?php

namespace App\Controller;

use App\Dto\Authenticated\FilterData;
use App\Entity\Filter;
use App\Repository\AccountRepository;
use App\Repository\FilterRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class AuthenticatedController extends AbstractController
{
    #[Get('/authenticated/user')]
    public function user(AccountRepository $repository): JsonResponse
    {
        $user = $this->getUser();
        $account = $repository->findOneBy(['uid' => $user->getUserIdentifier()]);
        if ($account) {
            $user->setStatus($account->getStatus());
        }
        return new JsonResponse(["data" => $user]);
    }

    #[Get('/authenticated/filters')]
    public function getFilters(FilterRepository $repository, Request $request): JsonResponse
    {
        $tableview = $request->query->get('tableview');
        $filters = $repository->findBy([
            'uid' => $this->getUser()->getUserIdentifier(),
            'tableview' => $tableview
        ]);
        $data = array_map(fn ($filter) => new FilterData($filter), $filters);
        return $this->json(["data" => $data]);
    }

    #[Post('/authenticated/filters')]
    public function addFilter(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $body = json_decode($request->getContent());

        $user = $this->getUser();

        $filter = new Filter();
        $filter->setUid($user->getUserIdentifier());
        $filter->setTableview($body->tableview);
        $filter->setName($body->name);
        $filter->setFilter(json_encode($body->filter));

        $em->persist($filter);
        $em->flush();

        $data = new FilterData($filter);

        return $this->json(["data" => $data]);
    }

    #[Delete('/authenticated/filters/{id}')]
    public function deleteFilter(FilterRepository $repository, EntityManagerInterface $em, Request $request, $id): JsonResponse
    {
        $filter = $repository->findOneBy([
            'id' => $id,
            'uid' => $this->getUser()->getUserIdentifier(),
        ]);
        $em->remove($filter);
        $em->flush();
        return $this->json([]);
    }
}
