<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Enum\NoteState;
use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

use App\Entity\Session;
use App\Entity\Note;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ApiResource]
class User implements PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: false, unique: true, type: 'string')]
    private string $email;

    #[ORM\Column(length: 255, nullable: false, unique: true, type: 'string')]
    private string $username;

    #[ORM\Column(length: 255, nullable: false, type: 'string')]
    private string $password;

    #[ORM\Column(type: 'datetime', nullable: false)]
    private \DateTime $createdAt;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Session::class)]
    private Collection $sessions;

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: Note::class)]
    private Collection $notes;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->sessions = new ArrayCollection();
        $this->notes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return Collection<int, Session>
     */
    public function getSessions(): Collection
    {
        return $this->sessions;
    }

    public function addSession(Session $session): self
    {
        if (!$this->sessions->contains($session)) {
            $this->sessions->add($session);
            $session->setUser($this);
        }

        return $this;
    }

    public function removeSession(Session $session): self
    {
        if ($this->sessions->removeElement($session)) {
            // set the owning side to null (unless already changed)
            if ($session->getUser() === $this) {
                $session->setUser(null);
            }
        }

        return $this;
    }

    public function getNotes(): Collection
    {
        return $this->notes->filter(function($note) {
            return $note->getState() === NoteState::NORMAL;
        });
    }

    public function getArchivedNotes(): Collection
    {
        return $this->notes->filter(function($note) {
            return $note->getState() === NoteState::ARCHIVED;
        });
    }

    public function getTrashedNotes(): Collection
    {
        return $this->notes->filter(function($note) {
            return $note->getState() === NoteState::TRASHED;
        });
    }

    public function addNote(Note $note): self
    {
        if (!$this->notes->contains($note)) {
            $this->notes->add($note);
            $note->setOwner($this);
        }

        return $this;
    }

    public function removeNote(Note $note): self
    {
        if ($this->notes->removeElement($note)) {
            if ($note->getOwner() === $this) {
                $note->setState(NoteState::TRASHED);
            }
        }

        return $this;
    }
}
