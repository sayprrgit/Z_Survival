<?php

declare(strict_types=1);

namespace App\DTO;

use App\Entity\Run;

final readonly class RunSummary
{
    public function __construct(
        public int $id,
        public string $status,
        public ?string $result,
        public int $survivalSeconds,
        public int $reachedLevel,
        public int $kills,
        public int $scrapEarned,
        public int $scrapBalance,
    ) {
    }

    public static function fromRun(Run $run): self
    {
        return new self(
            $run->getId() ?? 0,
            $run->getStatus()->value,
            $run->getResult()?->value,
            $run->getSurvivalSeconds(),
            $run->getReachedLevel(),
            $run->getKills(),
            $run->getScrapEarned(),
            $run->getUser()->getScrap(),
        );
    }
}
