<?php

namespace App\Entity;

use App\Repository\GameParticipantRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GameParticipantRepository::class)]
class GameParticipant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

//    #[ORM\ManyToOne(targetEntity: Game::class, inversedBy: 'gameParticipants')]
//    #[ORM\JoinColumn(nullable: false)]
//    private ?Game $game = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'gameParticipants')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column]
    private ?bool $is_active = true;

    #[ORM\Column]
    private ?bool $is_owner = false;

    #[ORM\Column]
    private ?DateTime $created_at = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): GameParticipant
    {
        $this->user = $user;
        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->is_active;
    }

    public function setIsActive(bool $is_active): GameParticipant
    {
        $this->is_active = $is_active;
        return $this;
    }

    public function getIsOwner(): ?bool
    {
        return $this->is_owner;
    }

    public function setIsOwner(bool $is_owner): GameParticipant
    {
        $this->is_owner = $is_owner;
        return $this;
    }

    public function getCreatedAt(): ?DateTime
    {
        return $this->created_at;
    }

    public function setCreatedAt(DateTime $created_at): GameParticipant
    {
        $this->created_at = $created_at;
        return $this;
    }

    public function __toString(): string
    {
        return $this->id;
    }
}
