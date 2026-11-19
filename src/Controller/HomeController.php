<?php

declare(strict_types=1);

namespace App\Controller;

use App\Game\Character\CharacterFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'home', methods: ['GET'])]
    public function index(Request $request, CharacterFactory $characterFactory): Response
    {
        return $this->render('home/index.html.twig', [
            'characters' => $characterFactory->all(),
            'selectedCharacterCode' => $request->getSession()->get('selected_character'),
        ]);
    }
}
