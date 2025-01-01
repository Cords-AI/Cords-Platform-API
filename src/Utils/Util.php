<?php

namespace App\Utils;

use App\Entity\ApiKey;
use App\Repository\ApiKeyRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class Util
{
    public static function isExpired(DateTime $date): bool
    {
        $now = new DateTime();
        if($date < $now) {
            return true;
        }

        return false;
    }

    public static function keyHasIpOrReferrerRestrictions(ApiKey $key, $referer): bool
    {
        $keyIsPlatformType = $key->getType() === 'platform';

        if (!$keyIsPlatformType) {
            return true;
        }
        if ($key->getType() === 'dev') {
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

    public static function determineValidUrl(ApiKey $key, string $referer): bool
    {
        $validReferrers = array_map(fn ($enabledUrl) => $enabledUrl->getUrl(), $key->getEnabledUrls()->getValues());

        if (!count($validReferrers)) {
            return true;
        }

        if (empty($referer)) {
            return false;
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

    public static function determineValidIp(ApiKey $key): bool {
        $authorizedIps = array_map(fn ($enabledIp) => $enabledIp->getIp(), $key->getEnabledIps()->getValues());

        if (empty($authorizedIps)) {
            return true;
        }

        $forwardedFor = explode(",", $_SERVER['HTTP_X_FORWARDED_FOR']);
        $forwardedFor = current($forwardedFor);

        return in_array($forwardedFor, $authorizedIps);
    }

    public static function platformKeyIsValid(Request $request, ApiKey $key): bool
    {
        $referer = $request->headers->get('referer') ?? "";

        $keyHasRestrictions = Util::keyHasIpOrReferrerRestrictions($key, $referer);
        $fromValidIp = Util::determineValidIp($key);
        $fromValidUrl = !empty($referer) ? Util::determineValidUrl($key, $referer) : false;
        $keyIsPlatformType = $key->getType() === 'platform';

        if ($keyIsPlatformType && $keyHasRestrictions && ($fromValidUrl || $fromValidIp)) {
            return true;
        }
        return false;
    }

    public static function getApiKeyEntity(Request $request, EntityManagerInterface $em): ApiKey|bool {
        /** @var ApiKeyRepository $repository */
        $repository = $em->getRepository(ApiKey::class);
        $apiKey = $request->headers->get('x-api-key');

        $queryBuilder = $repository->createQueryBuilder("key");
        $queryBuilder->where("key.apiKey = :apiKey")
            ->andWhere($queryBuilder->expr()->orX(
                $queryBuilder->expr()->neq('key.deleted', 1),
                $queryBuilder->expr()->isNull('key.deleted')
            ))
            ->setParameter('apiKey', $apiKey);

        return current($queryBuilder->getQuery()->getResult());
    }
}
