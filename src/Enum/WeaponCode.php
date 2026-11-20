<?php

declare(strict_types=1);

namespace App\Enum;

enum WeaponCode: string
{
    case Pistol = 'pistol';
    case AssaultRifle = 'assault_rifle';
    case Shotgun = 'shotgun';
    case Mace = 'mace';
    case Knife = 'knife';
    case Molotov = 'molotov';
    case Sniper = 'sniper';
    case RocketLauncher = 'rocket_launcher';
    case Minigun = 'minigun';
}
