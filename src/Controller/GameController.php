<?php

declare(strict_types=1);

namespace App\Controller;

use App\Game\Character\CharacterFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GameController extends AbstractController
{
    #[Route('/game', name: 'game_index', methods: ['GET'])]
    public function index(Request $request, CharacterFactory $characterFactory): Response
    {
        $selectedCharacter = $characterFactory->findByValue((string) $request->getSession()->get('selected_character'));
        if ($selectedCharacter === null) {
            $this->addFlash('warning', 'Choisis un personnage avant de lancer une partie.');

            return $this->redirectToRoute('character_index');
        }

        return $this->render('game/index.html.twig', [
            'character' => $selectedCharacter,
        ]);
    }
}
