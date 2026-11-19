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
}
