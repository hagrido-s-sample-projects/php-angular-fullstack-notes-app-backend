<?php

namespace App\Entity;

use App\Repository\SessionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SessionRepository::class)]
class Session
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    #[ORM\ManyToOne(targetEntity: User::class)]
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

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private string $access;

    public function getAccessToken(): ?string
    {
        return $this->access;
    }

    public function setAccessToken(string $access): self
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

    #[ORM\Column(type: 'datetime', nullable: false)]
    private \DateTime $createdAt;

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }
}
