<?php

namespace App\Entity;

use App\Repository\GameRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GameRepository::class)]
class Game
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'games')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Room $room = null;

    #[ORM\Column(length: 255, nullable: false)]
    private ?string $word = null;

    #[ORM\Column(length: 255, nullable: false)]
    private ?string $word_status = null;

    #[ORM\Column]
    private ?int $duration = null;

    #[ORM\Column(nullable: true)]
    private ?int $finished_in = null;

    #[ORM\Column]
    private ?bool $active = true;

    #[ORM\Column]
    private ?bool $won = false;

    #[ORM\ManyToOne(inversedBy: 'games')]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $winner = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at;

    #[ORM\OneToMany(mappedBy: 'game', targetEntity: Proposal::class)]
    private Collection $proposals;

    #[ORM\OneToMany(mappedBy: 'game', targetEntity: GameParticipant::class)]
    private Collection $gameParticipants;

    public function __construct()
    {
        $this->created_at = new \DateTimeImmutable();
        $this->proposals = new ArrayCollection();
        $this->gameParticipants = new ArrayCollection();
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

    public function getWordStatus(): ?string
    {
        return $this->word_status;
    }

    public function setWordStatus(?string $word_status): self
    {
        $this->word_status = $word_status;

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

    public function getFinishedIn(): int
    {
        return $this->finished_in;
    }

    public function setFinishedIn(int $finished_in): self
    {
        $this->finished_in = $finished_in;

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

    public function isWon(): ?bool
    {
        return $this->won;
    }

    public function setWon(?bool $won): self
    {
        $this->won = $won;

        return $this;
    }

    public function getWinner(): ?User
    {
        return $this->winner;
    }

    public function setWinner(?User $winner): self
    {
        $this->winner = $winner;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
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
            $proposal->setGame($this);
        }

        return $this;
    }

    public function removeProposal(Proposal $proposal): self
    {
        if ($this->proposals->removeElement($proposal)) {
            // set the owning side to null (unless already changed)
            if ($proposal->getGame() === $this) {
                $proposal->setGame(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, GameParticipant>
     */
    public function getGameParticipants(): Collection
    {
        return $this->proposals;
    }

    public function addGameParticipant(GameParticipant $gameParticipant): self
    {
        if (!$this->gameParticipants->contains($gameParticipant)) {
            $this->gameParticipants->add($gameParticipant);
            $gameParticipant->setGame($this);
        }

        return $this;
    }

    public function removeGameParticipant(GameParticipant $gameParticipant): self
    {
        if ($this->gameParticipants->removeElement($gameParticipant)) {
            // set the owning side to null (unless already changed)
            if ($gameParticipant->getGame() === $this) {
                $gameParticipant->setGame(null);
            }
        }

        return $this;
    }
}
