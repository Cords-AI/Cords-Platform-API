<?php

namespace App\Controller;

use App\Entity\ApiKey;
use App\Utils\Util;
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
        $key = Util::getApiKeyEntity($request, $em);

        $account = $key->getAccount();

        $referer = $request->headers->get('referer');

        if (!Util::keyHasIpOrReferrerRestrictions($key, $referer)) {
            return new JsonResponse([
                "error" => "You must add either an IP or Referer restriction to your API key."
            ], 401);
        }

        $fromValidIp = Util::determineValidIp($key);
        $fromValidUrl = Util::determineValidUrl($key, $referer);

        $unApprovedAccount = $account->getStatus() !== 'approved';
        $keyIsPlatformType = $key->getType() === 'platform';

        if (empty($key) || $unApprovedAccount || !$fromValidUrl || !$fromValidIp || $keyIsPlatformType) {
            return new JsonResponse(["error" => "Request from invalid source"], 403);
        }

        if ($key->isExpired()) {
            return new JsonResponse(["error" => "API key expired"], 403);
        }

        return new JsonResponse(null, 200);
    }
}
