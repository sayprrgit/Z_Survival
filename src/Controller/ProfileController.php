<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\RunHistoryQuery;
use App\Entity\User;
use App\Game\User\ProfileService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;

#[IsGranted('ROLE_USER')]
final class ProfileController extends AbstractController
{
    #[Route('/api/profile', name: 'api_profile_show', methods: ['GET'])]
    public function show(ProfileService $profiles): JsonResponse
    {
        return $this->json($profiles->profile($this->currentUser()));
    }

    #[Route('/api/runs', name: 'api_run_history', methods: ['GET'])]
    public function history(ProfileService $profiles, #[MapQueryString] ?RunHistoryQuery $query = null): JsonResponse
    {
        return $this->json($profiles->history($this->currentUser(), $query ?? new RunHistoryQuery()));
    }

    private function currentUser(): User
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        return $user;
    }
}
