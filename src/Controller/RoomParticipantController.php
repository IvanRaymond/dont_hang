<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Room;
use App\Entity\RoomParticipant;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RoomParticipantController extends AbstractController
{
    /**
     * @throws NonUniqueResultException
     */
    #[Route('/room/{id}/join', name: 'app_join_room')]
    public function joinRoom(Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $user = $entityManager->getRepository(User::class)->findOneByUsername($this->getUser()->getUserIdentifier());

        // Check if room is at capacity
        $room_participant_count = $entityManager->getRepository(RoomParticipant::class)->getCountByRoomId($id);
        $room = $entityManager->getRepository(Room::class)->findOneById($id);

        // if room doesn't exist or is at capacity or is not active, redirect to home
        if(!$room || $room_participant_count >= $room->getCapacity() || !$room->getIsActive()) {
            return $this->redirectToRoute('app_home');
        }

        // Check if user is not a room_participant
        $room_participant = $entityManager->getRepository(RoomParticipant::class)->findOneByRoomAndUser($id, $user->getId());

        if (!$room_participant) {
            // add user to room_participant
            $room_participant = new RoomParticipant();
            $room_participant->setUser($user);
            $room_participant->setRoom($room);
            $entityManager->persist($room_participant);
            $entityManager->flush();
        } else {
            // Check if user is banned
            if ($room_participant->getIsBanned()) {
                return $this->redirectToRoute('app_home');
            }
        }

        // When finished, redirect to room
        return $this->redirect('/room/' . $id);
    }

    #[Route('/room/{id}/leave', name: 'app_leave_room')]
    public function leaveRoom(Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $user = $entityManager->getRepository(User::class)->findOneByUsername($this->getUser()->getUserIdentifier());

        // Check if user is a room_participant
        $room_participant = $entityManager->getRepository(RoomParticipant::class)->findOneByRoomAndUser($id, $user->getId());

        if ($room_participant) {
            $room_participant->setIsActive(false);
            $entityManager->persist($room_participant);
            $entityManager->flush();
        }

        // When finished, redirect to home
        return $this->redirectToRoute('app_home');
    }

    #[Route('/room/{id}/kick/{user_id}', name: 'app_kick_room')]
    public function kickParticipant(Request $request, EntityManagerInterface $entityManager, int $id, int $user_id): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $user = $entityManager->getRepository(User::class)->findOneByUsername($this->getUser()->getUserIdentifier());

        if ($user) {
            // Check if user is a room_participant
            $room_participant = $entityManager->getRepository(RoomParticipant::class)->findOneByRoomAndUser($id, $user->getId());
            if (!$room_participant) {
                return $this->redirectToRoute('app_home');
            }

            // Check if user is the room master
            $room = $entityManager->getRepository(Room::class)->find($id);
            if (!$room || $room->getOwner() !== $user) {
                return $this->redirectToRoute('app_home');
            }

            // Check if user to kick is a room_participant
            $user_to_kick = $entityManager->getRepository(User::class)->find($user_id);
            $room_participant_to_kick = $entityManager->getRepository(RoomParticipant::class)->findOneByRoomAndUser($id, $user_to_kick->getId());
            if ($room_participant_to_kick) {
                $room_participant_to_kick->setIsBanned(true);
                $room_participant_to_kick->setIsActive(false);
                $entityManager->persist($room_participant_to_kick);
                $entityManager->flush();
            }
            $this->addFlash('success', 'User ' . $user_to_kick->getUsername() . ' kicked');
        }

        return new JsonResponse(['success' => true]);
    }
}
