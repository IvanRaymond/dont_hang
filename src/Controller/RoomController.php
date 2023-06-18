<?php

namespace App\Controller;

use App\Controller\BaseController;
use App\Entity\Room;
use App\Entity\Game;
use App\Entity\GameParticipant;
use App\Entity\RoomParticipant;
use App\Entity\User;
use App\Form\RoomFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RoomController extends BaseController
{
    #[Route('/room/create', name: 'app_room_create')]
    public function createRoom(Request $request, EntityManagerInterface $entityManager): Response
    {
        $isLogged = $this->isLoggedIn();
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $user = $entityManager->getRepository(User::class)->findOneByUsername($this->getUser()->getUserIdentifier());
        if ($user) {
            if (!$user->isMaster()) {
                return $this->redirectToRoute('app_home');
            }

            $room = new Room();
            $form = $this->createForm(RoomFormType::class, $room);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $room->setOwner($user);
                $entityManager->persist($room);
                $entityManager->flush();
                return $this->redirectToRoute('app_room', ['id' => $room->getId()]);
            }

            return $this->render('room/create.html.twig', [
                'is_logged_in' => $isLogged,
                'avatar' => $this->getUser()->getPicture(),
                'roomForm' => $form->createView(),
            ]);
        } else {
            return $this->redirectToRoute('app_home');
        }
    }

    #[Route('/room/{id}', name: 'app_room')]
    public function getRoom(Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
        $isLogged = $this->isLoggedIn();
        $avatar = $this->getUser() ? $this->getUser()->getPicture() : '';
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $user = $entityManager->getRepository(User::class)->findOneByUsername($this->getUser()->getUserIdentifier());
        $room = $entityManager->getRepository(Room::class)->find($id);

        if(!$room) {
            return $this->redirectToRoute('app_home');
        }

        if ($user) {
            if($room->getOwner() === $user) {
                return $this->render('room/room.html.twig', [
                    'is_logged_in' => $isLogged,
                    'room' => $room,
                ]);
            }
            // Check if user is a room_participant and try to add him if not
            $room_participant = $entityManager->getRepository(RoomParticipant::class)->findOneByRoomAndUser($id, $user->getId());
            $wordStatus = 'shibouya';
            if (!$room_participant) {
                // if the room is not password protected add user
                if (!$room->isPrivate()) {
                    return $this->redirectToRoute('app_join_room', ['id' => $id]);
                } else {
                    return $this->redirectToRoute('app_home');
                }
            }

            // get word status
            $game = $entityManager->getRepository(Game::class)->findLatestByRoom($room->getId());

            if ($game) {
                $game_participant = $entityManager->getRepository(GameParticipant::class)->findOneByGameAndUser($game->getId(), $user->getId());
                if ($game_participant) {
                    $wordStatus = $game_participant->getWordStatus();
                } else {
                    $wordStatus = '';
                }
            } else {
                $wordStatus = '';
            }
            
            // get all user in the room from RoomParticipantRepository findParticipantsByRoomId
            $participants = $entityManager->getRepository(RoomParticipant::class)->findParticipantsByRoomId($id);

            return $this->render('room/room.html.twig', [
                'is_logged_in' => $isLogged,
                'avatar' => $avatar,
                'room' => $room,
                'wordStatus' => $wordStatus,
                'participants' => $participants,
            ]);
        } else {
            return $this->redirectToRoute('app_home');
        }
    }
}
