<?php

declare(strict_types=1);

namespace App\Game\Reward;

use App\Entity\Difficulty;

final class RewardCalculator
{
    public function calculate(int $survivalSeconds, int $kills, Difficulty $difficulty, float $scrapBonus = 0): Reward
    {
        $baseScrap = intdiv($survivalSeconds, 60) + intdiv($kills, 10);
        if ($survivalSeconds > 0 || $kills > 0) {
            $baseScrap = max(1, $baseScrap);
        }

        return new Reward((int) floor($baseScrap * $difficulty->getMultiplier() * (1 + max(0, $scrapBonus))));
    }
}
