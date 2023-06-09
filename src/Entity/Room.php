<?php

namespace App\Entity;

use App\Repository\RoomRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RoomRepository::class)]
class Room
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?string $name = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'rooms')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    #[ORM\Column]
    private ?int $capacity = null;

    #[ORM\Column(nullable: true)]
    private ?int $game_count = null;

    #[ORM\Column]
    private ?bool $active = true;

    #[ORM\Column]
    private ?bool $private = false;

    #[ORM\Column(nullable: true)]
    private ?string $password = null;

    #[ORM\OneToMany(mappedBy: 'room', targetEntity: RoomParticipant::class)]
    private Collection $roomParticipants;

    #[ORM\OneToMany(mappedBy: 'room', targetEntity: Game::class)]
    private Collection $games;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at;

    public function __construct()
    {
        $this->created_at = new \DateTimeImmutable();
        $this->roomParticipants = new ArrayCollection();
        $this->games = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): Room
    {
        $this->name = $name;
        return $this;
    }

    public function getCapacity(): ?int
    {
        return $this->capacity;
    }

    public function setCapacity(int $capacity): Room
    {
        $this->capacity = $capacity;
        return $this;
    }


    public function getGameCount(): ?int
    {
        return $this->game_count;
    }

    public function setGameCount(int $game_count): Room
    {
        $this->game_count = $game_count;
        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): Room
    {
        $this->active = $active;
        return $this;
    }

    public function isPrivate(): ?bool
    {
        return $this->private;
    }

    public function setPrivate(bool $private): Room
    {
        $this->private = $private;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): Room
    {
        $this->password = $password;
        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(User $owner): Room
    {
        $this->owner = $owner;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->created_at;
    }

    /**
     * @return Collection<int, RoomParticipant>
     */
    public function getRoomParticipants(): Collection
    {
        return $this->roomParticipants;
    }

    public function addRoomParticipant(RoomParticipant $roomParticipant): self
    {
        if (!$this->roomParticipants->contains($roomParticipant)) {
            $this->roomParticipants->add($roomParticipant);
            $roomParticipant->setRoom($this);
        }
        return $this;
    }

    public function removeRoomParticipant(RoomParticipant $roomParticipant): self
    {
        if($this->roomParticipants->removeElement($roomParticipant)) {
            if ($roomParticipant->getRoom() === $this) {
                $roomParticipant->setRoom(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Game>
     */
    public function getGames(): Collection
    {
        return $this->games;
    }

    public function addGame(Game $game): self
    {
        if (!$this->games->contains($game)) {
            $this->games->add($game);
            $game->setRoom($this);
        }

        return $this;
    }

    public function removeGame(Game $game): self
    {
        if ($this->games->removeElement($game)) {
            // set the owning side to null (unless already changed)
            if ($game->getRoom() === $this) {
                $game->setRoom(null);
            }
        }

        return $this;
    }
}
