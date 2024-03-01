<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class CsrfTokenController extends AbstractController
{
    #[Route('/csrf-token', name: 'csrf_token')]
    public function csrfToken(): Response
    {
        session_start();

        if(!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $this->json([
            'csrf-token' => $_SESSION['csrf_token']
        ]); 
    }
}