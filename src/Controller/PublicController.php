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

        $referer = $request->headers->get('referer');

        if (!$this->prodKeyHasIpOrReferrerRestrictions($key, $referer)) {
            return new JsonResponse([
                "error" => "You must add either an IP or Referer restriction to your API key."
            ], 401);
        }

        $fromValidIp = $this->determineValidIp($key);
        $fromValidUrl = $this->determineValidUrl($key, $referer);

        if (empty($key) || $account->getStatus() !== 'approved' || !$fromValidUrl || !$fromValidIp) {
            return new JsonResponse(["error" => "Request from invalid source"], 403);
        }

        if ($key->isExpired()) {
            return new JsonResponse(["error" => "API key expired"], 403);
        }

        return new JsonResponse(null, 200);
    }

    private function determineValidUrl(ApiKey $key, string $referer): bool
    {
        $validReferrers = array_map(fn ($enabledUrl) => $enabledUrl->getUrl(), $key->getEnabledUrls()->getValues());

        if (!count($validReferrers)) {
            return true;
        }

        if (empty($referer)) {
            return false;
        }

        if (!empty($_ENV['WHITELISTED_URL'])) {
            $validReferrers[] = $_ENV['WHITELISTED_URL'];
        }

        $url = preg_replace("#^https?://#i", "", $referer);
        $url = rtrim($url, '/');
        $pattern = "#^(https?://)?(www\.)?$url(/|$)#i";

        foreach ($validReferrers as $validReferrer) {
            if (preg_match($pattern, $validReferrer) === 1) {
                return true;
            }
        }

        return false;
    }

    private function determineValidIp(ApiKey $key): bool {
        $authorizedIps = array_map(fn ($enabledIp) => $enabledIp->getIp(), $key->getEnabledIps()->getValues());

        if (empty($authorizedIps)) {
            return true;
        }

        $forwardedFor = explode(",", $_SERVER['HTTP_X_FORWARDED_FOR']);
        $forwardedFor = current($forwardedFor);

        return in_array($forwardedFor, $authorizedIps);
    }

    private function prodKeyHasIpOrReferrerRestrictions(ApiKey $key, $referer): bool
    {
        if (!empty($_ENV['ENFORCE_PROD_RESTRICTIONS']) && $_ENV['ENFORCE_PROD_RESTRICTIONS'] === 'FALSE') {
            return true;
        }
        if ($key->getType() !== 'production') {
            return true;
        }
        if (!empty($_ENV['WHITELISTED_URL']) && $referer === $_ENV['WHITELISTED_URL']) {
            return true;
        }
        $validReferrers = $key->getEnabledUrls()->getValues();
        $enabledIps = $key->getEnabledIps()->getValues();
        if (count($validReferrers) || count($enabledIps)) {
            return true;
        }
        return false;
    }
}
