<?php

namespace App\Entity;

use App\Repository\GameRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GameRepository::class)]
class Game
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Room::class, inversedBy: 'games')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Room $room = null;

    #[ORM\Column(length: 255, nullable: false)]
    private ?string $word = null;

    #[ORM\Column]
    private ?int $duration = null;

    #[ORM\Column(nullable: true)]
    private ?int $finished_in = null;

    #[ORM\Column]
    private ?bool $is_active = true;

    #[ORM\Column]
    private ?bool $is_won = false;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'games')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $winner_id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    public function __construct()
    {
        $this->created_at = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRoom(): ?Room
    {
        return $this->room;
    }

    public function setRoom(?Room $room): self
    {
        $this->room = $room;

        return $this;
    }

    public function getWord(): ?string
    {
        return $this->word;
    }

    public function setWord(?string $word): self
    {
        $this->word = $word;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    public function getFinishedAt(): int
    {
        return $this->finished_in;
    }

    public function setFinishedAt(int $finished_in): self
    {
        $this->finished_in = $finished_in;

        return $this;
    }

    public function isIsActive(): ?bool
    {
        return $this->is_active;
    }

    public function setIsActive(bool $is_active): self
    {
        $this->is_active = $is_active;

        return $this;
    }

    public function isIsWon(): ?bool
    {
        return $this->is_won;
    }

    public function setIsWon(?bool $is_won): self
    {
        $this->is_won = $is_won;

        return $this;
    }

    public function getWinnerId(): ?User
    {
        return $this->winner_id;
    }

    public function setWinnerId(?User $winner_id): self
    {
        $this->winner_id = $winner_id;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }
}
