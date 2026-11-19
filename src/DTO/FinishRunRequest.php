<?php

declare(strict_types=1);

namespace App\DTO;

use App\Enum\RunResult;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class FinishRunRequest
{
    public function __construct(
        #[Assert\Range(min: 0, max: 7200)]
        public int $survivalSeconds,
        #[Assert\Range(min: 1, max: 100)]
        public int $reachedLevel,
        #[Assert\Range(min: 0, max: 100000)]
        public int $kills,
        #[Assert\NotNull]
        public RunResult $result,
    ) {
    }
}
