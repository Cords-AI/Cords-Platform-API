<?php

namespace App\Controller;

use App\Entity\Term;
use Doctrine\Persistence\ManagerRegistry;
use FOS\RestBundle\Controller\Annotations\Get;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class TermController extends AbstractController
{
    #[Get('/term/{name}')]
    public function getLatestTermByName(ManagerRegistry $doctrine, string $name): JsonResponse
    {
        $termRepository = $doctrine->getRepository(Term::class);
        $latestTerm = $termRepository->findOneBy(['name' => $name], ['version' => 'DESC']);

        if (!$latestTerm) {
            return new JsonResponse(['error' => 'term not found'], 404);
        }

        return new JsonResponse(["data" => $latestTerm]);
    }
}
