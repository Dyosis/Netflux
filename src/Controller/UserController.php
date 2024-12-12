<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\Type\User\LoginType;
use App\Form\Type\User\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends AbstractController
{
    #[Route('/register', name: 'app_user_register')]
    public function register(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $hasher
    ): Response
    {
        $user = new User();
        $user->setRoles(['ROLE_USER']);
        $form = $this->createForm(UserType::class, $user)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $hasher->hashPassword($user, $user->getPlainPassword());
            $user->setPassword($password);
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_user_login');
        }

        return $this->render('Page/User/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/login', 'app_user_login')]
    public function login(): Response
    {
        $form = $this->createForm(LoginType::class);
        return $this->render('Page/User/login.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}