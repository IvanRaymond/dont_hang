<?php

namespace App\Entity;

use App\Repository\RoomRepository;
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
    private ?bool $is_active = true;

    #[ORM\Column]
    private ?bool $is_private = false;

    #[ORM\Column(nullable: true)]
    private ?string $password = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at;

    public function __construct()
    {
        $this->created_at = new \DateTimeImmutable();
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

    public function getIsActive(): ?bool
    {
        return $this->is_active;
    }

    public function setIsActive(bool $is_active): Room
    {
        $this->is_active = $is_active;
        return $this;
    }

    public function getIsPrivate(): ?bool
    {
        return $this->is_private;
    }

    public function setIsPrivate(bool $is_private): Room
    {
        $this->is_private = $is_private;
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
}
