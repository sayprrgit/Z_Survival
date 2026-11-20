<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\RunResult;
use App\Enum\RunStatus;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Repository\RunRepository::class)]
#[ORM\Table(name: 'runs')]
final class Run extends AbstractEntity
{
    #[ORM\ManyToOne(inversedBy: 'runs')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\ManyToOne(inversedBy: 'runs')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private Character $character;

    #[ORM\ManyToOne(inversedBy: 'runs')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private Difficulty $difficulty;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $startedAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $finishedAt = null;

    #[ORM\Column(options: ['default' => 0])]
    private int $survivalSeconds = 0;

    #[ORM\Column(options: ['default' => 1])]
    private int $reachedLevel = 1;

    #[ORM\Column(options: ['default' => 0])]
    private int $kills = 0;

    #[ORM\Column(options: ['default' => 0])]
    private int $scrapEarned = 0;

    #[ORM\Column(enumType: RunResult::class, nullable: true)]
    private ?RunResult $result = null;

    #[ORM\Column(enumType: RunStatus::class)]
    private RunStatus $status = RunStatus::Active;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $rewardsGrantedAt = null;

    public function __construct(User $user, Character $character, Difficulty $difficulty)
    {
        $this->user = $user;
        $this->character = $character;
        $this->difficulty = $difficulty;
        $this->startedAt = new \DateTimeImmutable();
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getCharacter(): Character
    {
        return $this->character;
    }

    public function getDifficulty(): Difficulty
    {
        return $this->difficulty;
    }

    public function getStartedAt(): \DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function getFinishedAt(): ?\DateTimeImmutable
    {
        return $this->finishedAt;
    }

    public function getSurvivalSeconds(): int
    {
        return $this->survivalSeconds;
    }

    public function getReachedLevel(): int
    {
        return $this->reachedLevel;
    }

    public function getKills(): int
    {
        return $this->kills;
    }

    public function getScrapEarned(): int
    {
        return $this->scrapEarned;
    }

    public function getResult(): ?RunResult
    {
        return $this->result;
    }

    public function getStatus(): RunStatus
    {
        return $this->status;
    }

    public function getRewardsGrantedAt(): ?\DateTimeImmutable
    {
        return $this->rewardsGrantedAt;
    }

    public function finish(int $survivalSeconds, int $reachedLevel, int $kills, RunResult $result, int $scrapEarned, \DateTimeImmutable $finishedAt): void
    {
        if ($this->status !== RunStatus::Active) {
            throw new \LogicException('Cette partie est déjà terminée.');
        }

        $this->survivalSeconds = $survivalSeconds;
        $this->reachedLevel = $reachedLevel;
        $this->kills = $kills;
        $this->result = $result;
        $this->scrapEarned = $scrapEarned;
        $this->finishedAt = $finishedAt;
        $this->status = RunStatus::Finished;
    }

    public function markRewardsGranted(\DateTimeImmutable $grantedAt): void
    {
        if ($this->status !== RunStatus::Finished) {
            throw new \LogicException('Les récompenses ne peuvent être versées que pour une partie terminée.');
        }

        $this->rewardsGrantedAt = $grantedAt;
        $this->status = RunStatus::RewardsGranted;
    }
}
