<?php

namespace App\Entity;

use App\Repository\RoomParticipantRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RoomParticipantRepository::class)]
class RoomParticipant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Room::class, inversedBy: 'roomParticipants')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Room $room = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'roomParticipants')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;


    #[ORM\Column]
    private ?\DateTimeImmutable $created_at;

    #[ORM\Column]
    private ?bool $is_active = true;

    #[ORM\Column]
    private ?bool $is_owner = false;

    #[ORM\Column]
    private ?bool $is_banned = false;

    public function __construct()
    {
        $this->created_at = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRoom(): ?Room
    {
        return $this->room;
    }

    public function setRoom(Room $room): RoomParticipant
    {
        $this->room = $room;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): RoomParticipant
    {
        $this->user = $user;
        return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->created_at;
    }

    public function getIsActive(): ?bool
    {
        return $this->is_active;
    }

    public function setIsActive(bool $is_active): RoomParticipant
    {
        $this->is_active = $is_active;
        return $this;
    }

    public function getIsOwner(): ?bool
    {
        return $this->is_owner;
    }

    public function setIsOwner(bool $is_owner): RoomParticipant
    {
        $this->is_owner = $is_owner;
        return $this;
    }

    public function getIsBanned(): ?bool
    {
        return $this->is_banned;
    }

    public function setIsBanned(bool $is_banned): RoomParticipant
    {
        $this->is_banned = $is_banned;
        return $this;
    }

    public function __toString(): string
    {
        return $this->id;
    }
}
