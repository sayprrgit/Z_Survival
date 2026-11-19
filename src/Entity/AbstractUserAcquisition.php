<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/** Association base for a permanent item acquired by a user. */
#[ORM\MappedSuperclass]
abstract class AbstractUserAcquisition extends AbstractEntity
{
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    protected User $user;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    protected \DateTimeImmutable $acquiredAt;

    protected function __construct(User $user)
    {
        $this->user = $user;
        $this->acquiredAt = new \DateTimeImmutable();
    }
}
