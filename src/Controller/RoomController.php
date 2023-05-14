<?php

namespace App\Controller;

use App\Entity\Room;
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

        // Check if user is master
        if (!$this->getUser()->getMaster()) {
            return $this->redirectToRoute('app_home');
        }

        $room = new Room();
        $form = $this->createForm(RoomFormType::class, $room);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($room);
            $entityManager->flush();
            return $this->redirect('/room/' . $room->getId());
        }

        return $this->render('room/create.html.twig', [
            'roomForm' => $form->createView(),
        ]);
    }

    #[Route('/room/{id}', name: 'app_room')]
    public function room(Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
         if (!$this->getUser()) {
             return $this->redirectToRoute('app_login');
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
