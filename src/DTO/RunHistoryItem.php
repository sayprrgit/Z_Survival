<?php

declare(strict_types=1);

namespace App\DTO;

use App\Entity\Run;

final readonly class RunHistoryItem
{
    public function __construct(
        public int $id,
        public string $characterCode,
        public string $characterName,
        public string $difficultyCode,
        public string $difficultyName,
        public string $status,
        public ?string $result,
        public string $startedAt,
        public ?string $finishedAt,
        public int $survivalSeconds,
        public int $reachedLevel,
        public int $kills,
        public int $scrapEarned,
    ) {
    }

    public static function fromRun(Run $run): self
    {
        return new self(
            $run->getId() ?? 0,
            $run->getCharacter()->getCode()->value,
            $run->getCharacter()->getName(),
            $run->getDifficulty()->getCode()->value,
            $run->getDifficulty()->getName(),
            $run->getStatus()->value,
            $run->getResult()?->value,
            $run->getStartedAt()->format(DATE_ATOM),
            $run->getFinishedAt()?->format(DATE_ATOM),
            $run->getSurvivalSeconds(),
            $run->getReachedLevel(),
            $run->getKills(),
            $run->getScrapEarned(),
        );
    }
}
