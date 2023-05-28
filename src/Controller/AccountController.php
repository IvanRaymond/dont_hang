<?php

namespace App\Controller;

use App\Entity\User;
use App\Controller\BaseController;
use App\Form\UserPhotoType;
use App\Form\UserFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class AccountController extends BaseController
{
    #[Route('/account', name: 'app_account')]
    public function index(): Response
    {
        $isLogged = $this->isLoggedIn();
        if (!$isLogged) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('account/index.html.twig', [
            'controller_name' => 'AccountController',
            'is_logged_in' => $isLogged,
            'name' => $this->getUser()->getUsername(),
            'email' => $this->getUser()->getEmail(),
            'avatar' => $this->getUser()->getPicture(),
        ]);
    }

    #[Route('/account/edit', name: 'app_account_edit')]
    public function edit(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $isLogged = $this->isLoggedIn();

        if (!$isLogged) {
            return $this->redirectToRoute('app_login');
        }

        $user = $this->getUser();

        $photoForm = $this->createForm(UserPhotoType::class, $user);
        $photoForm->handleRequest($request);

        $editForm = $this->createForm(UserFormType::class, $user);
        $editForm->handleRequest($request);

        $errors = $editForm->getErrors(true);

        if ($photoForm->isSubmitted() && $photoForm->isValid()) {
            $picture = $photoForm->get('picture')->getData();

            if ($picture->getClientOriginalExtension() != "jpeg") {
                // display a snackbar error
                $this->addFlash('success', 'Mauvaise extension du fichier. (jpeg)');
                return $this->redirectToRoute('app_account_edit');
            }

            // move the file to the right folder
            $pictureUrl = uniqid().'.jpeg';
            $picture->move(
                "assets/users",
                $pictureUrl
            );

            $this->getUser()->setPicture($pictureUrl);

            $entityManager->persist($user);
            $entityManager->flush();

            // display a snackbar success
            $this->addFlash('success', 'Photo de profil modifiée.');
            return $this->redirectToRoute('app_account');
        }

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $password = $editForm->get('password')->getData();
            $email = $editForm->get('email')->getData();

            if ($email != null && $email != "") {
                $user->setEmail($email);
            }
            
            if ($password != null && $password != "") {
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $password
                    )
                );
            }

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Paramètres du compte modifiés.');
            return $this->redirectToRoute('app_account');
        }

        return $this->render('account/edit.html.twig', [
            'is_logged_in' => $isLogged,
            'errors' => $errors,
            'avatar' => $this->getUser()->getPicture(),
            'name' => $this->getUser()->getUsername(),
            'photoForm' => $photoForm->createView(),
            'editForm' => $editForm->createView(),
        ]);
    }
}
