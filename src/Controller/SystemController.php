<?php

namespace App\Controller;

use App\Entity\Log;
use Doctrine\Persistence\ManagerRegistry;
use FOS\RestBundle\Controller\Annotations\Post;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class SystemController extends AbstractController
{
    #[Post('/system/log')]
    public function addLog(ManagerRegistry $doctrine, Request $request): JsonResponse
    {
        $body = json_decode($request->getContent());

        $log = new Log();
        $log->setApiKey($body->apiKey);
        $log->setIp($body->ip);
        $log->setSearchString(urldecode($body->searchString));
        $log->setLatitude(floatval($body->latitude));
        $log->setLongitude(floatval($body->longitude));
        $log->setProvince($body->province);
        $log->setType($body->type);
        $log->setCreatedDate(new \DateTime());

        $em = $doctrine->getManager();
        $em->persist($log);
        $em->flush();

        return new JsonResponse(["data" => 'success']);
    }
}