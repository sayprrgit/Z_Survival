<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\CharacterRepository;
use App\Game\Run\GameConfigurationBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GameController extends AbstractController
{
    private const PROGRESSION_KEY = 'talent_progression_v2';
    #[Route('/game', name: 'game_index', methods: ['GET'])]
    public function index(Request $request, CharacterRepository $characters, GameConfigurationBuilder $configurationBuilder): Response
    {
        $selectedCharacter = $characters->findOneByCodeValue((string) $request->getSession()->get('selected_character'));
        if ($selectedCharacter === null) {
            $this->addFlash('warning', 'Choisis un personnage avant de lancer une partie.');

            return $this->redirectToRoute('character_index');
        }

        return $this->render('game/index.html.twig', [
            'character' => $selectedCharacter,
            'gameConfiguration' => $configurationBuilder->build($selectedCharacter, (string) $request->getSession()->get('selected_difficulty', 'normal')),
            'progression' => $this->progression($request),
        ]);
    }

    #[Route('/talents', name: 'talent_index', methods: ['GET'])]
    public function talents(Request $request): Response
    {
        return $this->render('talent/index.html.twig', ['progression' => $this->progression($request)]);
    }

    #[Route('/game/progression', name: 'game_progression', methods: ['POST'])]
    public function saveProgression(Request $request): JsonResponse
    {
        if (!$this->isCsrfTokenValid('game-progression', (string) $request->request->get('_token'))) return $this->json(['error' => 'csrf'], 403);
        $resources = max(0, min(100000, $request->request->getInt('resources')));
        $talents = array_values(array_filter(explode(',', (string) $request->request->get('talents')), static fn (string $code): bool => preg_match('/^[a-z0-9_]+$/', $code) === 1));
        $request->getSession()->set(self::PROGRESSION_KEY, ['resources' => $resources, 'talents' => $talents]);
        return $this->json(['resources' => $resources, 'talents' => $talents]);
    }

    private function progression(Request $request): array { return $request->getSession()->get(self::PROGRESSION_KEY, ['resources' => 0, 'talents' => []]); }
}
