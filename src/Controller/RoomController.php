<?php

namespace App\Controller;

use App\Entity\Room;
use App\Entity\RoomParticipant;
use App\Entity\User;
use App\Form\RoomFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RoomController extends AbstractController
{
    #[Route('/room/create', name: 'app_room_create')]
    public function createRoom(Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $user = $entityManager->getRepository(User::class)->findOneByUsername($this->getUser()->getUserIdentifier());
        if ($user) {
            if (!$user->getMaster()) {
                return $this->redirectToRoute('app_home');
            }

            $room = new Room();
            $form = $this->createForm(RoomFormType::class, $room);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $room->setOwner($user);
                $entityManager->persist($room);
                $entityManager->flush();
                return $this->redirect('/room/' . $room->getId() . '/join');
            }

            return $this->render('room/create.html.twig', [
                'roomForm' => $form->createView(),
            ]);
        } else {
            return $this->redirectToRoute('app_home');
        }
    }

    #[Route('/room/{id}', name: 'app_room')]
    public function getRoom(Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $user = $entityManager->getRepository(User::class)->findOneByUsername($this->getUser()->getUserIdentifier());

        if ($user) {
            // Check if user is a room_participant and try to add him if not
            $room_participant = $entityManager->getRepository(RoomParticipant::class)->findOneByRoomAndUser($id, $user->getId());
            if (!$room_participant) {
                // if the room is not password protected add user
                $room = $entityManager->getRepository(Room::class)->find($id);
                if (!$room->getIsPrivate()) {
                    return $this->redirect('/room/' . $id . '/join');
                } else {
                    return $this->redirectToRoute('app_home');
                }
            }

            $room = $entityManager->getRepository(Room::class)->find($id);

            if (!$room) {
                return $this->redirectToRoute('app_home');
            }

            return $this->render('room/room.html.twig', [
                'room' => $room,
            ]);
        } else {
            return $this->redirectToRoute('app_home');
        }
    }
}
