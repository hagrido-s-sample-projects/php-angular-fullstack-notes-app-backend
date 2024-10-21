<?php

namespace App\Entity;

use App\Repository\SessionRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\Token;
use App\Enum\TokenType;

#[ORM\Entity(repositoryClass: SessionRepository::class)]
class Session
{
    public function __construct()
    {
        $this->isRevoked = false;
        $this->createdAt = new \DateTime();
        $this->tokens = new ArrayCollection();
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

    #[ORM\OneToMany(mappedBy: 'session', targetEntity: Token::class, cascade: ['persist'])]
    private Collection $tokens;

    public function getTokens(): Collection
    {
        return $this->tokens;
    }

    public function addToken(Token $token): self
    {
        if (!$this->tokens->contains($token)) {
            $this->tokens[] = $token;
        }
        return $this;
    }

    public function generateAccessToken(): Token
    {
        $token = new Token(\Symfony\Component\Uid\Uuid::v4()->toRfc4122(), TokenType::ACCESS);
        $this->addToken($token);
        return $token;
    }

    public function generateRefreshToken(): Token
    {
        $token = new Token(\Symfony\Component\Uid\Uuid::v4()->toRfc4122(), TokenType::REFRESH);
        $this->addToken($token);
        return $token;
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
        foreach ($this->tokens as $token) {
            $token->revoke();
        }
        return $this;
    }

    #[ORM\Column(type: 'datetime', nullable: false)]
    private \DateTime $createdAt;

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }
}
