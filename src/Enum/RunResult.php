<?php

declare(strict_types=1);

namespace App\Enum;

enum RunResult: string
{
    case Victory = 'victory';
    case Defeat = 'defeat';
}
