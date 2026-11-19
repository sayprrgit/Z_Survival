<?php

declare(strict_types=1);

namespace App\Game\User;

use App\DTO\RegistrationData;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final readonly class UserRegistrationService
{
    public function __construct(
        private UserRepository $users,
        private UserPasswordHasherInterface $passwordHasher,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function register(RegistrationData $data): User
    {
        $email = strtolower(trim($data->email));
        if ($this->users->findOneBy(['email' => $email]) instanceof User) {
            throw new \DomainException('Cette adresse e-mail possède déjà un compte.');
        }

        $user = new User($email, '');
        $user->setPassword($this->passwordHasher->hashPassword($user, $data->plainPassword));
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }
}
