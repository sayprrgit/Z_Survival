<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Character;
use App\Entity\User;
use App\Enum\CharacterCode;
use App\Game\Character\CharacterFactory;
use App\Game\Reward\UnlockService;
use App\Repository\CharacterRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class CharacterController extends AbstractController
{
    #[Route('/characters', name: 'character_index', methods: ['GET'])]
    public function index(): RedirectResponse
    {
        return new RedirectResponse($this->generateUrl('home').'#survivors');
    }

    #[Route('/characters/{code}/select', name: 'character_select', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function select(
        string $code,
        Request $request,
        CharacterFactory $characterFactory,
        CharacterRepository $characters,
        UnlockService $unlockService,
    ): RedirectResponse {
        if (!$this->isCsrfTokenValid('select-character', (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton de sélection invalide.');
        }

        $characterCode = CharacterCode::tryFrom($code);
        $character = $characterCode === null ? null : $characterFactory->find($characterCode);
        $catalogCharacter = $characterCode === null ? null : $characters->findOneBy(['code' => $characterCode]);
        if ($character === null || !$catalogCharacter instanceof Character) {
            throw $this->createNotFoundException('Personnage introuvable.');
        }

        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }
        try {
            $unlockService->assertUnlocked($user, $catalogCharacter);
        } catch (\DomainException $exception) {
            throw $this->createAccessDeniedException($exception->getMessage(), $exception);
        }

        $request->getSession()->set('selected_character', $character->code->value);
        $this->addFlash('success', sprintf('%s est prêt à survivre.', $character->name));

        return $this->redirectToRoute('game_index');
    }

    #[Route('/api/characters', name: 'api_character_catalog', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function catalog(UnlockService $unlockService): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        return $this->json($unlockService->catalogFor($user));
    }

    #[Route('/api/characters/{code}/unlock', name: 'api_character_unlock', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function unlock(string $code, Request $request, UnlockService $unlockService): JsonResponse
    {
        if (!$this->isCsrfTokenValid('unlock-character', (string) $request->headers->get('X-CSRF-Token'))) {
            throw $this->createAccessDeniedException('Jeton de déblocage invalide.');
        }

        $characterCode = CharacterCode::tryFrom($code);
        if ($characterCode === null) {
            throw $this->createNotFoundException('Personnage introuvable.');
        }

        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        try {
            return $this->json($unlockService->unlock($user, $characterCode));
        } catch (\DomainException $exception) {
            throw new BadRequestHttpException($exception->getMessage(), $exception);
        }
    }
}
