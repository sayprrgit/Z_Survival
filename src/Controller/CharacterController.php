<?php

declare(strict_types=1);

namespace App\Controller;

use App\Enum\DifficultyCode;
use App\Repository\CharacterRepository;
use App\Repository\DifficultyRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class CharacterController extends AbstractController
{
    #[Route('/characters', name: 'character_index', methods: ['GET'])]
    public function index(): RedirectResponse
    {
        return new RedirectResponse($this->generateUrl('home').'#survivors');
    }

    #[Route('/characters/{code}/select', name: 'character_select', methods: ['POST'])]
    public function select(string $code, Request $request, CharacterRepository $characters, DifficultyRepository $difficulties): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('select-character', (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton de sélection invalide.');
        }

        $character = $characters->findOneByCodeValue($code);
        if ($character === null) {
            throw $this->createNotFoundException('Personnage introuvable.');
        }

        $difficultyCode = DifficultyCode::tryFrom((string) $request->request->get('difficulty', DifficultyCode::Normal->value));
        if ($difficultyCode === null || $difficulties->findOneBy(['code' => $difficultyCode]) === null) {
            throw $this->createNotFoundException('Difficulté introuvable.');
        }

        $request->getSession()->set('selected_character', $character->getCode()->value);
        $request->getSession()->set('selected_difficulty', $difficultyCode->value);
        $this->addFlash('success', sprintf('%s est prêt à survivre.', $character->getName()));

        return $this->redirectToRoute('game_index');
    }
}
