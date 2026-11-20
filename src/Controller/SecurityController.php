<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\RegistrationData;
use App\Form\RegistrationFormType;
use App\Game\User\UserRegistrationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

final class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login', methods: ['GET', 'POST'])]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser() !== null) {
            return $this->redirectToRoute('home');
        }

        return $this->render('security/login.html.twig', [
            'lastUsername' => $authenticationUtils->getLastUsername(),
            'error' => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }

    #[Route('/register', name: 'app_register', methods: ['GET', 'POST'])]
    public function register(Request $request, UserRegistrationService $registrationService): Response
    {
        if ($this->getUser() !== null) {
            return $this->redirectToRoute('home');
        }

        $form = $this->createForm(RegistrationFormType::class, new RegistrationData());
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $registrationService->register($form->getData());
                $this->addFlash('success', 'Compte créé. Tu peux maintenant te connecter.');

                return $this->redirectToRoute('app_login');
            } catch (\DomainException $exception) {
                $form->addError(new FormError($exception->getMessage()));
            }
        }

        return $this->render('security/register.html.twig', ['registrationForm' => $form]);
    }

    #[Route('/logout', name: 'app_logout', methods: ['POST'])]
    public function logout(): never
    {
        throw new \LogicException('La déconnexion est interceptée par Symfony Security.');
    }
}
