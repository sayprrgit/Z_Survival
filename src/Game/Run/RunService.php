<?php

declare(strict_types=1);

namespace App\Game\Run;

use App\DTO\FinishRunRequest;
use App\DTO\RunSummary;
use App\Entity\Character;
use App\Entity\Difficulty;
use App\Entity\Run;
use App\Entity\User;
use App\Enum\RunResult;
use App\Enum\RunStatus;
use App\Game\Reward\RewardCalculator;
use App\Game\Talent\TalentEffectResolver;
use App\Repository\RunRepository;
use Doctrine\DBAL\LockMode;
use Doctrine\DBAL\Platforms\SQLitePlatform;
use Doctrine\ORM\EntityManagerInterface;

final readonly class RunService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RunRepository $runs,
        private RunResultValidator $validator,
        private RewardCalculator $rewardCalculator,
        private TalentEffectResolver $talentEffects,
    ) {
    }

    public function start(User $user, Character $character, Difficulty $difficulty): Run
    {
        $run = new Run($user, $character, $difficulty);
        $this->entityManager->persist($run);
        $this->entityManager->flush();

        return $run;
    }

    public function finish(User $user, int $runId, FinishRunRequest $request): RunSummary
    {
        if ($user->getId() === null) {
            throw new \DomainException('Le joueur doit être enregistré pour terminer une partie.');
        }

        return $this->entityManager->wrapInTransaction(function (EntityManagerInterface $entityManager) use ($user, $runId, $request): RunSummary {
            $lockMode = $entityManager->getConnection()->getDatabasePlatform() instanceof SQLitePlatform
                ? LockMode::NONE
                : LockMode::PESSIMISTIC_WRITE;
            $lockedUser = $entityManager->find(User::class, $user->getId(), $lockMode);
            if (!$lockedUser instanceof User) {
                throw new \DomainException('Joueur introuvable.');
            }

            $run = $this->runs->findOwnedForUpdate($runId, $lockedUser);
            if (!$run instanceof Run) {
                throw new \DomainException('Partie introuvable pour ce joueur.');
            }

            if ($run->getStatus() === RunStatus::RewardsGranted) {
                return RunSummary::fromRun($run);
            }

            if ($run->getStatus() === RunStatus::Active) {
                $now = new \DateTimeImmutable();
                $this->validator->validate($run, $request, $now);
                $scrapBonus = $this->talentEffects->effectsFor($lockedUser)['scrap_bonus'] ?? 0.0;
                $reward = $this->rewardCalculator->calculate($request->survivalSeconds, $request->kills, $run->getDifficulty(), $scrapBonus);
                $run->finish($request->survivalSeconds, $request->reachedLevel, $request->kills, $request->result, $reward->scrap, $now);
            }

            $lockedUser->recordRun(
                $run->getScrapEarned(),
                $run->getSurvivalSeconds(),
                $run->getKills(),
                $run->getResult() === RunResult::Victory,
            );
            $run->markRewardsGranted(new \DateTimeImmutable());
            $entityManager->flush();

            return RunSummary::fromRun($run);
        });
    }
}
