<?php

namespace App\Controller;

use App\Controller\BaseController;
use App\Entity\Game;
use App\Entity\Room;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends BaseController
{
    #[Route('/', name: 'app_home')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $isLogged = $this->isLoggedIn();
        if ($isLogged) {
            $isAdmin = $this->getUser()->isMaster();
        } else {
            $isAdmin = false;
        }
        
        $avatar = $this->getUser() ? $this->getUser()->getPicture() : '';

        $rooms = $entityManager->getRepository(Room::class)->findAll();
        return $this->render('home/index.html.twig', [
            'is_logged_in' => $isLogged,
            'is_admin' => $isAdmin,
            'avatar' => $avatar,
            'controller_name' => 'HomeController',
            'rooms' => $rooms,
        ]);
    }
}
