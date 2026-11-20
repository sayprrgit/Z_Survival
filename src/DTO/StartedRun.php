<?php

declare(strict_types=1);

namespace App\DTO;

use App\Entity\Run;

final readonly class StartedRun
{
    public function __construct(public int $id, public string $status, public string $startedAt, public string $difficulty)
    {
    }

    public static function fromRun(Run $run): self
    {
        return new self(
            $run->getId() ?? 0,
            $run->getStatus()->value,
            $run->getStartedAt()->format(DATE_ATOM),
            $run->getDifficulty()->getCode()->value,
        );
    }
}
