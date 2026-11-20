<?php

declare(strict_types=1);

namespace App\Enum;

enum EnemyCode: string
{
    public const PATIENT_ZERO_AVAILABLE_FROM_SECONDS = 900;

    case Walker = 'walker';
    case Runner = 'runner';
    case Brute = 'brute';
    case Spitter = 'spitter';
    case Colossus = 'colossus';
    case PatientZero = 'patient_zero';
}
