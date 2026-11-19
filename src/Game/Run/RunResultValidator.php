<?php

declare(strict_types=1);

namespace App\Game\Run;

use App\DTO\FinishRunRequest;
use App\Entity\Run;
use App\Enum\RunStatus;

final class RunResultValidator
{
    public function validate(Run $run, FinishRunRequest $request, ?\DateTimeImmutable $now = null): void
    {
        if ($run->getStatus() !== RunStatus::Active) {
            throw new \DomainException('Cette partie n’est plus active.');
        }

        $now ??= new \DateTimeImmutable();
        $elapsedSeconds = max(0, $now->getTimestamp() - $run->getStartedAt()->getTimestamp());
        if ($request->survivalSeconds > min(7200, $elapsedSeconds + 5)) {
            throw new \DomainException('La durée annoncée dépasse la durée réelle de la partie.');
        }

        if ($request->kills > max(10, $request->survivalSeconds * 4)) {
            throw new \DomainException('Le nombre d’éliminations est incohérent avec la durée.');
        }

        if ($request->reachedLevel > $request->kills + 1) {
            throw new \DomainException('Le niveau annoncé est incohérent avec le nombre d’éliminations.');
        }
    }
}
