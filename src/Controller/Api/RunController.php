<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\DTO\FinishRunRequest;
use App\DTO\StartRunRequest;
use App\DTO\StartedRun;
use App\Entity\Character;
use App\Entity\User;
use App\Enum\CharacterCode;
use App\Game\Run\DifficultyService;
use App\Game\Run\RunService;
use App\Game\Reward\UnlockService;
use App\Repository\CharacterRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class RunController extends AbstractController
{
    #[Route('/api/runs/start', name: 'api_run_start', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function start(
        Request $request,
        #[MapRequestPayload] StartRunRequest $payload,
        CharacterRepository $characters,
        DifficultyService $difficulties,
        UnlockService $unlockService,
        RunService $runs,
    ): Response {
        if (!$this->isCsrfTokenValid('start-run', (string) $request->headers->get('X-CSRF-Token'))) {
            throw $this->createAccessDeniedException('Jeton de démarrage invalide.');
        }

        $selectedCode = CharacterCode::tryFrom((string) $request->getSession()->get('selected_character'));
        if ($selectedCode === null || $selectedCode !== $payload->character) {
            throw $this->createAccessDeniedException('Le personnage doit être sélectionné avant le départ.');
        }

        $character = $characters->findOneBy(['code' => $selectedCode]);
        if (!$character instanceof Character) {
            throw $this->createNotFoundException('Personnage introuvable.');
        }

        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        try {
            $unlockService->assertUnlocked($user, $character);
        } catch (\DomainException $exception) {
            throw $this->createAccessDeniedException($exception->getMessage(), $exception);
        }

        return $this->json(StartedRun::fromRun($runs->start($user, $character, $difficulties->get($payload->difficulty))), Response::HTTP_CREATED);
    }

    #[Route('/api/runs/{id}/finish', name: 'api_run_finish', requirements: ['id' => '\\d+'], methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function finish(int $id, Request $request, #[MapRequestPayload] FinishRunRequest $payload, RunService $runs): Response
    {
        if (!$this->isCsrfTokenValid('finish-run', (string) $request->headers->get('X-CSRF-Token'))) {
            throw $this->createAccessDeniedException('Jeton de clôture invalide.');
        }

        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        try {
            return $this->json($runs->finish($user, $id, $payload));
        } catch (\DomainException $exception) {
            throw new BadRequestHttpException($exception->getMessage(), $exception);
        }
    }
}
