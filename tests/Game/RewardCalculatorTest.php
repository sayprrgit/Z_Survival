<?php

declare(strict_types=1);

namespace App\Tests\Game;

use App\Entity\Difficulty;
use App\Enum\DifficultyCode;
use App\Game\Reward\RewardCalculator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class RewardCalculatorTest extends TestCase
{
    #[Test]
    public function it_applies_the_difficulty_multiplier_to_survival_and_kills(): void
    {
        $difficulty = new Difficulty(DifficultyCode::Hard, 'Hard', 'Test', 1.3, 2);

        $reward = (new RewardCalculator())->calculate(180, 20, $difficulty);

        self::assertSame(6, $reward->scrap);
    }

    #[Test]
    public function it_does_not_grant_scrap_for_an_empty_zero_second_result(): void
    {
        $difficulty = new Difficulty(DifficultyCode::Normal, 'Normal', 'Test', 1, 1);

        self::assertSame(0, (new RewardCalculator())->calculate(0, 0, $difficulty)->scrap);
    }
}
