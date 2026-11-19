<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final class RegistrationData
{
    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length(max: 180)]
    public string $email = '';

    #[Assert\NotBlank]
    #[Assert\Length(min: 8, max: 4096, minMessage: 'Le mot de passe doit contenir au moins {{ limit }} caractères.')]
    public string $plainPassword = '';
}
