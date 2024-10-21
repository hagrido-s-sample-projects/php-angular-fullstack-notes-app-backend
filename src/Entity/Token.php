<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\TokenRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Enum\TokenType;

#[ORM\Entity(repositoryClass: TokenRepository::class)]
#[ApiResource]
class Token
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $token;

    #[ORM\Column(enumType: TokenType::class)]
    private TokenType $type;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'boolean', nullable: false)]
    private bool $isRevoked = false;

    #[ORM\ManyToOne(targetEntity: Session::class, inversedBy: 'tokens')]
    #[ORM\JoinColumn(name: 'session_id', referencedColumnName: 'id', nullable: false)]
    private Session $session;

    public function __construct(string $token, TokenType $type, Session $session)
    {
        $this->token = $token;
        $this->type = $type;
        $this->session = $session;
        $this->createdAt = new \DateTimeImmutable();
        $this->isRevoked = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getType(): TokenType
    {
        return $this->type;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function isRevoked(): bool
    {
        return $this->isRevoked;
    }

    public function revoke(): self
    {
        $this->isRevoked = true;
        return $this;
    }

    public function getSession(): Session
    {
        return $this->session;
    }
}
