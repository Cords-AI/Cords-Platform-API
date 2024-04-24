<?php

namespace App\Controller;

use App\Entity\ApiKey;
use App\Entity\Log;
use App\Service\FirebaseService;
use Doctrine\Persistence\ManagerRegistry;
use FOS\RestBundle\Controller\Annotations\Post;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class SystemController extends AbstractController
{
    #[Post('/system/log')]
    public function addLog(ManagerRegistry $doctrine, Request $request, FirebaseService $firebaseService): JsonResponse
    {
        $body = json_decode($request->getContent());

        $users = $firebaseService->getUsers();
        $apiKeyRepository = $doctrine->getRepository(ApiKey::class);
        $apiKeyEntity = $apiKeyRepository->findOneBy(['apiKey' => $body->apiKey]);

        $associatedAccount = FirebaseService::getMatchingFirebaseUser($users, $apiKeyEntity->getUid());

        $log = new Log();
        $log->setApiKey($body->apiKey);
        $log->setIp($body->ip);
        $log->setSearchString(urldecode($body->searchString));
        $log->setLatitude(floatval($body->latitude));
        $log->setLongitude(floatval($body->longitude));
        $log->setProvince($body->province);
        $log->setCountry($body->country);
        $log->setPostalCode($body->postalCode);
        $log->setType($body->type);
        $log->setFilters($body->filters);
        $log->setCreatedDate(new \DateTime());
        $log->setEmail($associatedAccount->email);

        $em = $doctrine->getManager();
        $em->persist($log);
        $em->flush();

        return new JsonResponse(["data" => 'success']);
    }
}
