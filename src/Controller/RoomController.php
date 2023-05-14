<?php

namespace App\Controller;

use App\Entity\Room;
use App\Entity\RoomParticipant;
use App\Entity\User;
use App\Form\RoomFormType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RoomController extends AbstractController
{
    /**
     * @throws NonUniqueResultException
     */
    #[Route('/room/create', name: 'app_room_create')]
    public function createRoom(Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $user = $entityManager->getRepository(User::class)->findOneByUsername($this->getUser()->getUserIdentifier());
        if (!$user->getMaster()) {
            return $this->redirectToRoute('app_home');
        }

        $room = new Room();
        $form = $this->createForm(RoomFormType::class, $room);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $room->setOwner($user);
            $room->setCreatedAt(new \DateTime());
            $entityManager->persist($room);
            $entityManager->flush();
            return $this->redirect('/room/' . $room->getId() . '/join');
        }

        return $this->render('room/create.html.twig', [
            'roomForm' => $form->createView(),
        ]);
    }

    #[Route('/room/{id}', name: 'app_room')]
    public function getRoom(Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
         if (!$this->getUser()) {
             return $this->redirectToRoute('app_login');
         }

         // Check if user is a room_participant and try to add him if not
        $room_participant = $entityManager->getRepository(RoomParticipant::class)->findOneBy([
            'user' => $this->getUser(),
            'room' => $id
        ]);
        if (!$room_participant) {
            // if the room is not password protected add user
            $room = $entityManager->getRepository(Room::class)->find($id);
            if (!$room->getIsPrivate()){
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
        }
    }
