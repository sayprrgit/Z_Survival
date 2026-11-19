<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: \App\Repository\UserRepository::class)]
#[ORM\Table(name: 'users')]
#[UniqueEntity(fields: ['email'], message: 'Cette adresse e-mail possède déjà un compte.')]
final class User extends AbstractEntity implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Column(length: 180, unique: true)]
    private string $email;

    /** @var list<string> */
    #[ORM\Column(type: Types::JSON)]
    private array $roles = [];

    #[ORM\Column]
    private string $password;

    #[ORM\Column(options: ['default' => 0])]
    private int $scrap = 0;

    #[ORM\Column(options: ['default' => 0])]
    private int $totalRuns = 0;

    #[ORM\Column(options: ['default' => 0])]
    private int $totalVictories = 0;

    #[ORM\Column(options: ['default' => 0])]
    private int $totalSurvivalSeconds = 0;

    #[ORM\Column(options: ['default' => 0])]
    private int $bestSurvivalSeconds = 0;

    #[ORM\Column(options: ['default' => 0])]
    private int $totalKills = 0;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $lastLoginAt = null;

    /** @var Collection<int, Run> */
    #[ORM\OneToMany(targetEntity: Run::class, mappedBy: 'user')]
    private Collection $runs;

    public function __construct(string $email, string $password)
    {
        $this->email = $email;
        $this->password = $password;
        $this->createdAt = new \DateTimeImmutable();
        $this->runs = new ArrayCollection();
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    /** @return list<string> */
    public function getRoles(): array
    {
        return array_values(array_unique([...$this->roles, 'ROLE_USER']));
    }

    /** @param list<string> $roles */
    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function eraseCredentials(): void
    {
    }

    public function markLoginAt(\DateTimeImmutable $loggedInAt): void
    {
        $this->lastLoginAt = $loggedInAt;
    }

    public function getScrap(): int
    {
        return $this->scrap;
    }

    public function getTotalRuns(): int
    {
        return $this->totalRuns;
    }

    public function getTotalVictories(): int
    {
        return $this->totalVictories;
    }

    public function getTotalSurvivalSeconds(): int
    {
        return $this->totalSurvivalSeconds;
    }

    public function getBestSurvivalSeconds(): int
    {
        return $this->bestSurvivalSeconds;
    }

    public function getTotalKills(): int
    {
        return $this->totalKills;
    }

    public function recordRun(int $scrapEarned, int $survivalSeconds, int $kills, bool $victory): void
    {
        $this->scrap += $scrapEarned;
        ++$this->totalRuns;
        $this->totalSurvivalSeconds += $survivalSeconds;
        $this->bestSurvivalSeconds = max($this->bestSurvivalSeconds, $survivalSeconds);
        $this->totalKills += $kills;
        if ($victory) {
            ++$this->totalVictories;
        }
    }

    public function spendScrap(int $amount): void
    {
        if ($amount < 0) {
            throw new \InvalidArgumentException('Le montant à débiter ne peut pas être négatif.');
        }
        if ($this->scrap < $amount) {
            throw new \DomainException('Tu n’as pas assez de ferraille.');
        }

        $this->scrap -= $amount;
    }
}
