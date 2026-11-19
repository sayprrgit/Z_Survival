<?php

declare(strict_types=1);

namespace App\Enum;

enum DifficultyCode: string
{
    case Normal = 'normal';
    case Hard = 'hard';
    case Nightmare = 'nightmare';
}
