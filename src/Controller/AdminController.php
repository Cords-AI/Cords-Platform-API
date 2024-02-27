<?php

namespace App\Controller;

use App\Dto\Admin\UserData;
use App\Service\FirebaseService;
use FOS\RestBundle\Controller\Annotations\Get;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class AdminController extends AbstractController
{
    #[Get('/admin/users')]
    public function user(FirebaseService $firebase): JsonResponse
    {
        $rows = $firebase->getUsers();
        $data = array_map(fn($row) => new UserData($row), $rows);
        $data = array_filter($data, fn(UserData $userData) => $userData->emailVerified);
        return new JsonResponse([
            "data" => $data
        ]);
    }
}
