<?php

declare(strict_types=1);

namespace App\Game\User;

use App\DTO\PlayerProfile;
use App\DTO\RunHistoryItem;
use App\DTO\RunHistoryPage;
use App\DTO\RunHistoryQuery;
use App\Entity\User;
use App\Game\Reward\UnlockService;
use App\Game\Talent\TalentTree;
use App\Repository\RunRepository;

final readonly class ProfileService
{
    public function __construct(
        private RunRepository $runs,
        private UnlockService $unlockService,
        private TalentTree $talentTree,
    ) {
    }

    public function profile(User $user): PlayerProfile
    {
        return new PlayerProfile(
            $user->getId() ?? 0,
            $user->getEmail(),
            $user->getScrap(),
            $user->getTotalRuns(),
            $user->getTotalVictories(),
            $user->getTotalSurvivalSeconds(),
            $user->getBestSurvivalSeconds(),
            $user->getTotalKills(),
            $this->unlockService->catalogFor($user),
            $this->talentTree->catalogFor($user),
        );
    }

    public function history(User $user, RunHistoryQuery $query): RunHistoryPage
    {
        $items = array_map(RunHistoryItem::fromRun(...), $this->runs->findPageForUser($user, $query->page, $query->limit));

        return new RunHistoryPage($query->page, $query->limit, $this->runs->countForUser($user), $items);
    }
}
