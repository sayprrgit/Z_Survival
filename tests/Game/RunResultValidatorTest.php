<?php

declare(strict_types=1);

namespace App\Tests\Game;

use App\DTO\FinishRunRequest;
use App\Entity\Character;
use App\Entity\Difficulty;
use App\Entity\Run;
use App\Entity\User;
use App\Entity\Weapon;
use App\Enum\CharacterCode;
use App\Enum\DifficultyCode;
use App\Enum\EnemyCode;
use App\Enum\RunResult;
use App\Enum\RunStatus;
use App\Enum\WeaponCode;
use App\Game\Run\RunResultValidator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class RunResultValidatorTest extends TestCase
{
    #[Test]
    public function it_accepts_reasonable_result_stats_within_elapsed_time(): void
    {
        $run = $this->createRun();
        $validator = new RunResultValidator();
        $finishAt = $run->getStartedAt()->modify('+15 seconds');

        $validator->validate($run, new FinishRunRequest(12, 3, 8, RunResult::Defeat), $finishAt);

        self::assertSame(RunStatus::Active, $run->getStatus());
    }

    #[Test]
    public function it_rejects_a_client_reported_duration_ahead_of_server_time(): void
    {
        $run = $this->createRun();
        $finishAt = $run->getStartedAt()->modify('+10 seconds');

        $this->expectException(\DomainException::class);
        (new RunResultValidator())->validate($run, new FinishRunRequest(30, 2, 5, RunResult::Defeat), $finishAt);
    }

    #[Test]
    public function it_rejects_impossible_kill_and_level_totals(): void
    {
        $run = $this->createRun();
        $finishAt = $run->getStartedAt()->modify('+15 seconds');

        $this->expectException(\DomainException::class);
        (new RunResultValidator())->validate($run, new FinishRunRequest(12, 20, 1, RunResult::Defeat), $finishAt);
    }

    #[Test]
    public function it_rejects_a_victory_without_patient_zero_confirmation(): void
    {
        $run = $this->createRun();
        $finishAt = $run->getStartedAt()->modify('+900 seconds');

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('confirmer la défaite de Patient Zéro');
        (new RunResultValidator())->validate($run, new FinishRunRequest(900, 20, 50, RunResult::Victory), $finishAt);
    }

    #[Test]
    public function it_rejects_a_colossus_victory_as_the_final_result(): void
    {
        $run = $this->createRun();
        $finishAt = $run->getStartedAt()->modify('+900 seconds');

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('confirmer la défaite de Patient Zéro');
        (new RunResultValidator())->validate(
            $run,
            new FinishRunRequest(900, 20, 50, RunResult::Victory, EnemyCode::Colossus),
            $finishAt,
        );
    }

    #[Test]
    public function it_rejects_a_patient_zero_victory_before_the_boss_is_available(): void
    {
        $run = $this->createRun();
        $finishAt = $run->getStartedAt()->modify('+900 seconds');

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('qu’après 900 secondes');
        (new RunResultValidator())->validate(
            $run,
            new FinishRunRequest(899, 20, 50, RunResult::Victory, EnemyCode::PatientZero),
            $finishAt,
        );
    }

    #[Test]
    public function it_rejects_a_patient_zero_victory_before_server_elapsed_time(): void
    {
        $run = $this->createRun();
        $finishAt = $run->getStartedAt()->modify('+899 seconds');

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('qu’après 900 secondes');
        (new RunResultValidator())->validate(
            $run,
            new FinishRunRequest(900, 20, 50, RunResult::Victory, EnemyCode::PatientZero),
            $finishAt,
        );
    }

    #[Test]
    public function it_accepts_a_patient_zero_victory_after_the_boss_is_available(): void
    {
        $run = $this->createRun();
        $finishAt = $run->getStartedAt()->modify('+900 seconds');

        (new RunResultValidator())->validate(
            $run,
            new FinishRunRequest(900, 20, 50, RunResult::Victory, EnemyCode::PatientZero),
            $finishAt,
        );

        self::assertSame(RunStatus::Active, $run->getStatus());
    }

    private function createRun(): Run
    {
        $weapon = new Weapon(WeaponCode::Pistol, 'Pistolet', 'Test', 'targeted', 10, 1, 'Test', 100);
        $character = new Character(CharacterCode::Mason, 'Mason', 'Test', 'Test', 100, $weapon);
        $difficulty = new Difficulty(DifficultyCode::Normal, 'Normal', 'Test', 1, 1);
        $user = new User('test@example.test', 'not-a-password-hash');

        return new Run($user, $character, $difficulty);
    }
}
