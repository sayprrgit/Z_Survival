<?php

declare(strict_types=1);

namespace App\Enum;

enum RunStatus: string
{
    case Active = 'active';
    case Finished = 'finished';
    case RewardsGranted = 'rewards_granted';
}
