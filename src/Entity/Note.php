<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\NoteRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;
use App\Enum\NoteState;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

#[ORM\Entity(repositoryClass: NoteRepository::class)]
#[ApiResource]
class Note
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'notes')]
    #[ORM\JoinColumn(nullable: false)]
    private User $owner;    

    #[ORM\Column(type: 'string', enumType: NoteState::class)]
    private NoteState $state = NoteState::NORMAL;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $updatedAt;

    public function __construct(User $owner, string $title = null, string $content = null)
    {
        $this->owner = $owner;
        $this->title = $title;
        $this->content = $content;
        $this->state = NoteState::NORMAL;
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;
        $this->setUpdatedAt();
        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;
        $this->setUpdatedAt();
        return $this;
    }

    public function getOwner(): User
    {
        return $this->owner;
    }

    public function setOwner(User $owner): self
    {
        $this->owner = $owner;
        $this->setUpdatedAt();
        return $this;
    }

    public function getState(): NoteState
    {
        return $this->state;
    }

    public function setState(NoteState $state): self
    {
        $this->state = $state;
        $this->setUpdatedAt();
        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeInterface
    {
        return $this->updatedAt;
    }

    private function setUpdatedAt(): void
    {
        $this->updatedAt = new \DateTime();
    }

    public function trashNote(): JsonResponse
    {
        if ($this->state === NoteState::TRASHED) {
            return new JsonResponse(['status' => 'ALREADY_TRASHED', 'error' => 'Note is already in trash'], Response::HTTP_BAD_REQUEST);
        }
        $this->state = NoteState::TRASHED;
        $this->setUpdatedAt();
        return new JsonResponse(['status' => 'SUCCESS', 'message' => 'Note trashed successfully'], Response::HTTP_OK);
    }

    public function archiveNote(): JsonResponse
    {
        if ($this->state === NoteState::ARCHIVED) {
            return new JsonResponse(['status' => 'ALREADY_ARCHIVED', 'error' => 'Note is already archived'], Response::HTTP_BAD_REQUEST);
        }
        $this->state = NoteState::ARCHIVED;
        $this->setUpdatedAt();
        return new JsonResponse(['status' => 'SUCCESS', 'message' => 'Note archived successfully'], Response::HTTP_OK);
    }

    public function restoreNote(): JsonResponse
    {
        if ($this->state === NoteState::NORMAL) {
            return new JsonResponse(['status' => 'ALREADY_NORMAL', 'error' => 'Note is already in normal state'], Response::HTTP_BAD_REQUEST);
        }
        $this->state = NoteState::NORMAL;
        $this->setUpdatedAt();
        return new JsonResponse(['status' => 'SUCCESS', 'message' => 'Note restored successfully'], Response::HTTP_OK);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'title' => $this->getTitle(),
            'content' => $this->getContent(),
            'createdAt' => $this->getCreatedAt()->format('Y-m-d H:i:s'),
            'updatedAt' => $this->getUpdatedAt()->format('Y-m-d H:i:s'),
        ];
    }
}
