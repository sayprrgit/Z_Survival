<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Game\Talent\TalentService;
use App\Game\Talent\TalentTree;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class TalentController extends AbstractController
{
    #[Route('/api/talents', name: 'api_talent_catalog', methods: ['GET'])]
    public function catalog(TalentTree $talentTree): JsonResponse
    {
        return $this->json($talentTree->catalogFor($this->currentUser()));
    }

    #[Route('/api/talents/{code}/purchase', name: 'api_talent_purchase', methods: ['POST'])]
    public function purchase(string $code, Request $request, TalentService $talentService): JsonResponse
    {
        if (!$this->isCsrfTokenValid('purchase-talent', (string) $request->headers->get('X-CSRF-Token'))) {
            throw $this->createAccessDeniedException('Jeton d’achat invalide.');
        }

        try {
            return $this->json($talentService->purchase($this->currentUser(), $code));
        } catch (\DomainException $exception) {
            throw new BadRequestHttpException($exception->getMessage(), $exception);
        }
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
