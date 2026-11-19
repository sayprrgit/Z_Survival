<?php

declare(strict_types=1);

namespace App\Controller;

use App\Game\Character\CharacterFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CharacterController extends AbstractController
{
    #[Route('/characters', name: 'character_index', methods: ['GET'])]
    public function index(): RedirectResponse
    {
        return new RedirectResponse($this->generateUrl('home').'#survivors');
    }

    #[Route('/characters/{code}/select', name: 'character_select', methods: ['POST'])]
    public function select(string $code, Request $request, CharacterFactory $characterFactory): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('select-character', (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton de sélection invalide.');
        }

        $character = $characterFactory->findByValue($code);
        if ($character === null) {
            throw $this->createNotFoundException('Personnage introuvable.');
        }

        $request->getSession()->set('selected_character', $character->code->value);
        $this->addFlash('success', sprintf('%s est prêt à survivre.', $character->name));

        return $this->redirectToRoute('game_index');
    }
}
