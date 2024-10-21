<?php

namespace App\Entity;

use App\Repository\SessionRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\Token;
use App\Enum\TokenType;
use Symfony\Component\Uid\Uuid;

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

    private function addToken(Token $token): self
    {
        if (!$this->tokens->contains($token)) {
            $this->tokens[] = $token;
        }
        return $this;
    }

    public function generateAccessToken(string $refreshTokenValue): Token
    {
        $this->revokeTokensByType(TokenType::ACCESS);
        
        $tokenValue = $refreshTokenValue . '.' . Uuid::v4()->toRfc4122();
        $token = new Token($tokenValue, TokenType::ACCESS, $this);
        $this->addToken($token);
        return $token;
    }

    public function regenerateTokens(): array
    {
        $this->revokeTokensByType(TokenType::REFRESH);
        $this->revokeTokensByType(TokenType::ACCESS);
        
        $refreshTokenBase = Uuid::v4()->toRfc4122();
        $hashedRefreshToken = hash('sha256', $refreshTokenBase);
        $refreshToken = new Token($hashedRefreshToken, TokenType::REFRESH, $this);
        $this->addToken($refreshToken);
        
        $accessToken = $this->generateAccessToken($refreshTokenBase);
        
        return ['refresh_token' => $refreshToken, 'access_token' => $accessToken];
    }

    private function revokeTokensByType(TokenType $type): void
    {
        foreach ($this->tokens as $token) {
            if ($token->getType() === $type) {
                $token->revoke();
            }
        }
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
