<?php

namespace App\Controller;

use App\Entity\ApiKey;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations\Get;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class PublicController extends AbstractController
{
    #[Get('/api-key/validate')]
    public function validate(EntityManagerInterface $em, Request $request): JsonResponse
    {
        /** @var \App\Repository\ApiKeyRepository $repository */
        $repository = $em->getRepository(ApiKey::class);
        $apiKey = $request->headers->get('x-api-key');

        $queryBuilder = $repository->createQueryBuilder("key");
        $queryBuilder->where("key.apiKey = :apiKey")
            ->andWhere($queryBuilder->expr()->orX(
                $queryBuilder->expr()->neq('key.deleted', 1),
                $queryBuilder->expr()->isNull('key.deleted')
            ))
            ->setParameter('apiKey', $apiKey);

        $key = current($queryBuilder->getQuery()->getResult());

        $account = $key->getAccount();

        $fromValidUrl = $this->determineValidUrl($key, $request->headers->get('referer'));

        if (empty($key) || $account->getStatus() !== 'approved' || !$fromValidUrl) {
            return new JsonResponse(null, 403);
        }

        return new JsonResponse(null, 200);
    }

    private function determineValidUrl(ApiKey $key, string $referer): bool
    {
        $validReferrers = array_map(fn($enabledUrl) => $enabledUrl->getUrl(), $key->getEnabledUrls()->getValues());

        if (!count($validReferrers)) {
            return true;
        }

        $url = preg_replace("#^https?://#i", "", $referer);
        $url = rtrim($url, '/');
        $pattern = "#(https?://)?$url/?#i";

        foreach ($validReferrers as $validReferrer) {
            if (preg_match($pattern, $validReferrer) === 1) {
                return true;
            }
        }

        return false;
    }
}
