<?php

declare(strict_types=1);

namespace App\DTO;

final readonly class TalentPurchaseReceipt
{
    public function __construct(public string $talentCode, public bool $alreadyOwned, public int $costPaid, public int $scrapBalance)
    {
    }
}
