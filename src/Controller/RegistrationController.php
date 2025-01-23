<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // Encode the plain password
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            // Set additional user properties if needed
            $user->setRegistrationTime(new \DateTime()); // Set registration time
            $user->setStatus('active'); // Set default status

            // Persist the user entity
            $entityManager->persist($user);
            $entityManager->flush();

            // Optionally, you can send a confirmation email here

            // Redirect to a route after successful registration
            return $this->redirectToRoute('user_management'); // Redirect to login page or another page
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(), // Pass the form view to the template
        ]);
    }
}
