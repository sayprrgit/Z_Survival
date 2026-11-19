<?php

declare(strict_types=1);

namespace App\Game\Run;

use App\Entity\Difficulty;
use App\Enum\DifficultyCode;
use App\Repository\DifficultyRepository;

final class DifficultyService
{
    public function __construct(private readonly DifficultyRepository $difficulties)
    {
    }

    public function get(DifficultyCode $code): Difficulty
    {
        $difficulty = $this->difficulties->findOneBy(['code' => $code]);
        if (!$difficulty instanceof Difficulty) {
            throw new \DomainException(sprintf('La difficulté "%s" est inconnue.', $code->value));
        }

        return $difficulty;
    }
}
