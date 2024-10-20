<?php

namespace App\Entity;

use App\Repository\SessionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SessionRepository::class)]
class Session
{
    public function __construct()
    {
        $this->isRevoked = false;
        $this->createdAt = new \DateTime();
    }
    
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'sessions')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    private ?User $user = null;

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    #[ORM\Column(type: 'json', nullable: false)]
    private array $access    = [];

    public function getAccessToken(): array
    {
        return $this->access;
    }

    public function setAccessToken(array $access): self
    {
        $this->access = $access;
        return $this;
    }

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private string $refresh;

    public function getRefreshToken(): ?string
    {
        return $this->refresh;
    }

    public function setRefreshToken(string $refresh): self
    {
        $this->refresh = $refresh;
        return $this;
    }

    #[ORM\Column(type: 'boolean', nullable: false)]
    private bool $isRevoked = false;

    public function isRevoked(): bool
    {
        return $this->isRevoked;
    }

    public function revoke(): self
    {
        $this->isRevoked = true;
        return $this;
    }

    #[ORM\Column(type: 'datetime', nullable: false)]
    private \DateTime $createdAt;

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }
}
