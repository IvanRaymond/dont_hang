<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[UniqueEntity(fields: ['username', 'email'], message: 'There is already an account with this username')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $username = null;

    #[ORM\Column]
    private ?string $email = null;

    #[ORM\Column(nullable: true)]
    private ?string $picture = null;

    #[ORM\Column]
    private ?bool $master = false;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column]
    private ?bool $active = true;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Proposal::class)]
    private Collection $proposals;

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: Room::class)]
    private Collection $rooms;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: RoomParticipant::class)]
    public Collection $roomParticipants;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: GameWinner::class)]
    public Collection $gameWinners;

    public function __construct()
    {
        $this->created_at = new \DateTimeImmutable();
        $this->proposals = new ArrayCollection();
        $this->rooms = new ArrayCollection();
        $this->roomParticipants = new ArrayCollection();
        $this->gameWinners = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPicture(): ?string
    {
        return $this->picture;
    }

    public function setPicture(string $picture): self
    {
        $this->picture = $picture;

        return $this;
    }

    public function isMaster(): ?bool
    {
        return $this->master;
    }

    public function setMaster(bool $master): self
    {
        $this->master = $master;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->created_at;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function __toString(): string
    {
        return $this->username;
    }

    /**
     * @return Collection<int, Proposal>
     */
    public function getProposals(): Collection
    {
        return $this->proposals;
    }

    public function addProposal(Proposal $proposal): self
    {
        if (!$this->proposals->contains($proposal)) {
            $this->proposals->add($proposal);
            $proposal->setUser($this);
        }

        return $this;
    }

    public function removeProposal(Proposal $proposal): self
    {
        if ($this->proposals->removeElement($proposal)) {
            // set the owning side to null (unless already changed)
            if ($proposal->getUser() === $this) {
                $proposal->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Room>
     */
    public function getRooms(): Collection
    {
        return $this->rooms;
    }

    public function addRoom(Room $room): self
    {
        if (!$this->rooms->contains($room)) {
            $this->rooms->add($room);
            $room->setOwner($this);
        }

        return $this;
    }

    public function removeRoom(Room $room): self
    {
        if ($this->rooms->removeElement($room)) {
            // set the owning side to null (unless already changed)
            if ($room->getOwner() === $this) {
                $room->setOwner(null);
            }
        }

        return $this;
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
            $roomParticipant->setUser($this);
        }

        return $this;
    }

    public function removeRoomParticipant(RoomParticipant $roomParticipant): self
    {
        if ($this->roomParticipants->removeElement($roomParticipant)) {
            // set the owning side to null (unless already changed)
            if ($roomParticipant->getUser() === $this) {
                $roomParticipant->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, GameWinner>
     */
    public function getGameWinners(): Collection
    {
        return $this->gameWinners;
    }

    public function addGameWinner(GameWinner $gameWinner): self
    {
        if (!$this->gameWinners->contains($gameWinner)) {
            $this->gameWinners->add($gameWinner);
            $gameWinner->setUser($this);
        }

        return $this;
    }

    public function removeGameWinner(GameWinner $gameWinner): self
    {
        if ($this->gameWinners->removeElement($gameWinner)) {
            // set the owning side to null (unless already changed)
            if ($gameWinner->getUser() === $this) {
                $gameWinner->setUser(null);
            }
        }

        return $this;
    }
}
