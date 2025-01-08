<?php

namespace App\Controller;

use App\Dto\Authenticated\FilterData;
use App\Dto\Authenticated\UserData;
use App\Email\NewAccountRequestViewModel;
use App\Email\EmailService;
use App\Entity\Account;
use App\Entity\Agreement;
use App\Entity\Filter;
use App\Entity\Profile;
use App\Entity\Term;
use App\Repository\AccountRepository;
use App\Repository\FilterRepository;
use App\RequestParams\ProfileParams;
use App\Utils\ClientContext;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
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
        $userData = new UserData($user->dto(), $account);
        return new JsonResponse(["data" => $userData]);
    }

    #[Post('/authenticated/profile')]
    public function createProfile(
        AccountRepository $repository,
        EntityManagerInterface $em,
        ProfileParams $params,
        EmailService $emailService,
        ClientContext $clientContext
    ): JsonResponse {
        $user = $this->getUser();
        $account = $repository->findOneBy(['uid' => $user->getUserIdentifier()]);

        if (!$account) {
            $account = new Account();
            $account->setUid($user->getUserIdentifier());
            $account->setStatus('pending');
            $em->persist($account);
            $em->flush();
        }

        $profile = $account->getProfile();
        if (!$profile) {
            $profile = new Profile();
            $account->setProfile($profile);
        }
        $profile->setOrganization($params->organization);
        $profile->setPurpose($params->purpose);

        $em->persist($account);
        $em->flush();

        if (!empty($_ENV['FROM_EMAIL'])) {
            $to = $_ENV['ADMIN_EMAIL'];
            $langCode = $clientContext->langCode;
            $subject = "A new account is awaiting approval";
            $template = "new-account-request";
            $vm = new NewAccountRequestViewModel();
            $vm->clientLanguage = $langCode === 'fr' ? 'French' : 'English';
            $vm->accountLink = "{$_ENV['CORDS_ADMIN_FRONTEND_URL']}/en/accounts/{$user->getUserIdentifier()}";

            $emailService->send($to, $subject, $template, $vm);
        }

        return $this->json([]);
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

    #[Get('/authenticated/agreements/review')]
    public function getUnacceptedAgreements(ManagerRegistry $doctrine): JsonResponse
    {
        $user = $this->getUser();
        $userId = $user->getUserIdentifier();

        $accountRepository = $doctrine->getRepository(Account::class);
        $account = $accountRepository->findOneBy(['uid' => $userId]);

        $unacceptedTermIds = $account->getUnacceptedAgreementIds();

        $termRepository = $doctrine->getRepository(Term::class);
        $missingTerms = $termRepository->findBy(['id' => $unacceptedTermIds]);

        return new JsonResponse(['data' => $missingTerms]);
    }

    #[Post('/authenticated/agreements/accept/{name}')]
    public function acceptTerms(ManagerRegistry $doctrine, string $name): JsonResponse
    {
        $user = $this->getUser();

        $accountRepository = $doctrine->getRepository(Account::class);
        $account = $accountRepository->findOneBy(['uid' => $user->getUserIdentifier()]);

        $termRepository = $doctrine->getRepository(Term::class);
        $term = $termRepository->findOneBy(['name' => $name], ['version' => 'DESC']);

        if (!$account || !$term) {
            return new JsonResponse(['error' => 'account or term not found'], 422);
        }

        if (!$account->alreadyAcceptedThisTerm($term)) {
            $agreement = new Agreement();
            $agreement->setAccount($account);
            $agreement->setTerm($term);
            $agreement->setAcceptedDate((new \DateTime()));
            $agreement->setValidUntil((new \DateTime('+60 days')));

            $em = $doctrine->getManager();
            $em->persist($agreement);
            $em->flush();
        }

        return new JsonResponse(['data' => 'term accepted']);
    }
}
