<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Character;
use App\Entity\User;
use App\Enum\DifficultyCode;
use App\Game\Character\CharacterFactory;
use App\Game\Reward\UnlockService;
use App\Game\Run\GameConfigurationBuilder;
use App\Repository\CharacterRepository;
use App\Repository\DifficultyRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class GameController extends AbstractController
{
    #[Route('/game', name: 'game_index', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(
        Request $request,
        CharacterFactory $characterFactory,
        CharacterRepository $characters,
        DifficultyRepository $difficulties,
        GameConfigurationBuilder $configurationBuilder,
        UnlockService $unlockService,
    ): Response {
        $selectedCharacter = $characterFactory->findByValue((string) $request->getSession()->get('selected_character'));
        if ($selectedCharacter === null) {
            $this->addFlash('warning', 'Choisis un personnage avant de lancer une partie.');

            return $this->redirectToRoute('character_index');
        }

        $user = $this->getUser();
        $catalogCharacter = $characters->findOneByCodeValue($selectedCharacter->code->value);
        if (!$user instanceof User || !$catalogCharacter instanceof Character) {
            throw $this->createAccessDeniedException();
        }

        try {
            $unlockService->assertUnlocked($user, $catalogCharacter);
        } catch (\DomainException $exception) {
            $request->getSession()->remove('selected_character');
            $this->addFlash('warning', $exception->getMessage());

            return $this->redirectToRoute('home');
        }

        $difficultyCode = DifficultyCode::tryFrom((string) $request->getSession()->get('selected_difficulty', DifficultyCode::Normal->value));
        if ($difficultyCode === null || $difficulties->findOneBy(['code' => $difficultyCode]) === null) {
            $request->getSession()->remove('selected_difficulty');
            $this->addFlash('warning', 'Choisis une difficulté valide avant de lancer une partie.');

            return $this->redirectToRoute('home');
        }

        return $this->render('game/index.html.twig', [
            'character' => $selectedCharacter,
            'gameConfiguration' => $configurationBuilder->build($selectedCharacter, $user, $difficultyCode->value),
        ]);
    }
}
