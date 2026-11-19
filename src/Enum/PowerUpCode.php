<?php

declare(strict_types=1);

namespace App\Enum;

enum PowerUpCode: string
{
    case ReinforcedAmmo = 'reinforced_ammo';
    case Reflexes = 'reflexes';
    case ReinforcedVest = 'reinforced_vest';
    case Adrenaline = 'adrenaline';
    case Precision = 'precision';
    case Regeneration = 'regeneration';
    case Magnet = 'magnet';
    case SurvivalInstinct = 'survival_instinct';
}
