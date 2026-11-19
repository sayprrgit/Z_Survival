<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\CharacterRepository;
use App\Repository\DifficultyRepository;
use App\Repository\EnemyTypeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'home', methods: ['GET'])]
    public function index(Request $request, CharacterRepository $characters, EnemyTypeRepository $enemyTypes, DifficultyRepository $difficulties): Response
    {
        return $this->render('home/index.html.twig', [
            'characters' => $characters->findAllForSelection(),
            'mobs' => $enemyTypes->findAllForShowcase(),
            'difficulties' => $difficulties->findAllForGame(),
            'selectedCharacterCode' => $request->getSession()->get('selected_character'),
            'selectedDifficultyCode' => $request->getSession()->get('selected_difficulty', 'normal'),
        ]);
    }
}
