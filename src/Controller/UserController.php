<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/user')]
class UserController extends AbstractController
{
    #[Route('/register', name: 'app_user_register', methods: ['GET', 'POST'])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        $user = new User();
        $form = $this->createForm(UserType::class, $user, [
            'is_edit' => false,
            'is_admin' => false,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $form->get('plainPassword')->getData()
            );
            $user->setPassword($hashedPassword);

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Inscription réussie ! Vous pouvez maintenant vous connecter.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('user/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/manage', name: 'app_user_manage', methods: ['GET'])]
    public function manage(UserRepository $userRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('user/manage.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        User $user,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm(UserType::class, $user, [
            'is_edit' => true,
            'is_admin' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->has('plainPassword') && $form->get('plainPassword')->getData()) {
                $hashedPassword = $passwordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                );
                $user->setPassword($hashedPassword);
            }

            $entityManager->flush();

            $this->addFlash('success', 'Profil mis à jour avec succès');
            return $this->redirectToRoute('app_user_manage');
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }
}
