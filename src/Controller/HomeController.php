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
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class HomeController extends BaseController
{
    #[Route('/', name: 'app_home')]
    public function index(Request $request, EntityManagerInterface $entityManager, HttpClientInterface $httpClient): Response
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

        if ($form->isSubmitted() && $form->isValid()) {

            $isClassic = $form->get('isClassic')->getData();
            if (!$isClassic) {
                // redirect to a form multiplayer
                return $this->redirectToRoute('app_room_create');
            }

            // get data from form
            $name = $form->get('name')->getData();
            $word = $form->get('word')->getData();
            $duration = $form->get('duration')->getData();
            $isClassic = $form->get('isClassic')->getData();
            $wordLength = strlen($word);
            $initWord = '';
            for ($i = 0; $i < $wordLength; $i++) {
                $initWord .= '_';
            }
            
            // create a room
            $room = new Room();
            $room->setOwner($this->getUser());
            $room->setName($name);
            $room->setCapacity(100);
            $room->setGameCount(1);

            $entityManager->persist($room);
            // $entityManager->persist($game);
            $entityManager->flush();

            $roomId = $room->getId();

            $data = [
                'word' => $word,
                'duration' => $duration,
                'init' => $initWord,
                'classic' => $isClassic,
            ];

            if ($roomId != null) {
                $httpHost = rtrim($_SERVER['HTTP_HOST'], '/');
                $url = 'http://' . $httpHost;
                $url .= $this->generateUrl('app_game_create', ['roomId' => $roomId]);

                $response = $httpClient->request('POST', $url, [
                    'json' => $data,
                ]);

                $statusCode = $response->getStatusCode();
                if ($statusCode == 200) {
                    $this->addFlash('success', 'Ajout d\'une partie réussi.');
                } else {
                    $this->addFlash('success', 'Erreur: ' . $statusCode);
                }
            } else {
                $this->addFlash('success', 'Room id introuvable');
            }

            // $game->setRoom($room);
            // $game->setDuration($duration);
            // $game->setWord($word);

            // $game->setWordStatus($initWord);
            // $game->setIsClassic($isClassic);

            return $this->redirectToRoute('app_home');
        }

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
