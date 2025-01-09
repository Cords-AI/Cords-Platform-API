<?php

namespace App\Controller;

use App\Collection\TermCollection;
use FOS\RestBundle\Controller\Annotations\Get;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class TermController extends AbstractController
{
    #[Get('/term/{name}')]
    public function getLatestTermByName(TermCollection $collection, string $name): JsonResponse
    {

        $filters = ['name' => $name];

        $collection
            ->sort("CAST(term.version AS DECIMAL(8, 3))", true)
            ->limit(1)
            ->filters($filters)
            ->execute();

        $latestTerm = current($collection->getRows());

        if (!$latestTerm) {
            return new JsonResponse(['error' => 'term not found'], 404);
        }

        return new JsonResponse(["data" => $latestTerm]);
    }
}
