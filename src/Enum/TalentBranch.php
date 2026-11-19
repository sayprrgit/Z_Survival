<?php

declare(strict_types=1);

namespace App\Enum;

enum TalentBranch: string
{
    case Survival = 'survival';
    case Combat = 'combat';
    case Utility = 'utility';
}
