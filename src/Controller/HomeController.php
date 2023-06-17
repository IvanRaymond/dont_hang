<?php

namespace App\Controller;

use App\Controller\BaseController;
use App\Entity\Game;
use App\Entity\Room;
use App\Form\CreateGameType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends BaseController
{
    #[Route('/', name: 'app_home')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $isLogged = $this->isLoggedIn();
        if ($isLogged) {
            $isAdmin = $this->getUser()->isMaster();
        } else {
            $isAdmin = false;
        }

        $game = new Game();
        $form = $this->createForm(CreateGameType::class, $game);
        $form->handleRequest($request);

        // if ($form->isSubmitted() && $form->isValid()) {
            // $game->setRoom($room);
            // $game->setDuration($duration);
            // $game->setWord($word);
            // $game->setWordStatus($init);
            // $picture = $photoForm->get('picture')->getData();

            // if ($picture->getClientOriginalExtension() != "jpeg") {
            //     // display a snackbar error
            //     $this->addFlash('success', 'Mauvaise extension du fichier. (jpeg)');
            //     return $this->redirectToRoute('app_account_edit');
            // }

            // // move the file to the right folder
            // $pictureUrl = uniqid().'.jpeg';
            // $picture->move(
            //     "assets/users",
            //     $pictureUrl
            // );

            // $this->getUser()->setPicture($pictureUrl);

            // $entityManager->persist($user);
            // $entityManager->flush();

            // display a snackbar success
            // $this->addFlash('success', 'Photo de profil modifiée.');
            // return $this->redirectToRoute('app_account');
        // }

        $avatar = $this->getUser() ? $this->getUser()->getPicture() : '';

        $rooms = $entityManager->getRepository(Room::class)->findAll();
        return $this->render('home/index.html.twig', [
            'is_logged_in' => $isLogged,
            'is_admin' => $isAdmin,
            'avatar' => $avatar,
            'controller_name' => 'HomeController',
            'rooms' => $rooms,
            'createGameForm' => $form->createView(),
        ]);
    }
}
