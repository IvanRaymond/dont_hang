<?php

namespace App\Controller;

use App\Controller\BaseController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AccountController extends BaseController
{
    #[Route('/account', name: 'app_account')]
    public function index(): Response
    {
        $isLogged = $this->isLoggedIn();
        return $this->render('account/index.html.twig', [
            'controller_name' => 'AccountController',
            'is_logged_in' => $isLogged,
        ]);
    }
}
