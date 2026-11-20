<?php

declare(strict_types=1);

namespace App\DTO;

use App\Enum\CharacterCode;
use App\Enum\DifficultyCode;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class StartRunRequest
{
    public function __construct(
        #[Assert\NotNull]
        public CharacterCode $character,
        public DifficultyCode $difficulty = DifficultyCode::Normal,
    ) {
    }
}
