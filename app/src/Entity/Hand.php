<?php

namespace App\Entity;

use App\Repository\HandRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HandRepository::class)]
class Hand
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $holdemNoLimit = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $playedAt = null;

    #[ORM\ManyToMany(targetEntity: Player::class, inversedBy: 'hands')]
    private Collection $players;

    
    #[ORM\Column]
    private array $playersPosition = [];

    #[ORM\Column(length: 255)]
    private ?string $preflopAction = null;

    #[ORM\Column(length: 255)]
    private ?string $flopAction = null;

    #[ORM\Column(length: 255)]
    private ?string $turnAction = null;

    #[ORM\Column(length: 255)]
    private ?string $riverAction = null;

    #[ORM\Column(length: 255)]
    private ?string $showdown = null;

    public function __construct()
    {
        $this->players = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHoldemNoLimit(): ?string
    {
        return $this->holdemNoLimit;
    }

    public function setHoldemNoLimit(string $holdemNoLimit): self
    {
        $this->holdemNoLimit = $holdemNoLimit;

        return $this;
    }

    public function getPlayedAt(): ?\DateTimeInterface
    {
        return $this->playedAt;
    }

    public function setPlayedAt(\DateTimeInterface $playedAt): self
    {
        $this->playedAt = $playedAt;

        return $this;
    }

    /**
     * @return Collection<int, Player>
     */
    public function getPlayers(): Collection
    {
        return $this->players;
    }

    public function addPlayer(Player $player): self
    {
        if (!$this->players->contains($player)) {
            $this->players->add($player);
        }

        return $this;
    }

    public function removePlayer(Player $player): self
    {
        $this->players->removeElement($player);

        return $this;
    }

    public function getPreflopAction(): ?string
    {
        return $this->preflopAction;
    }

    public function setPreflopAction(string $preflopAction): self
    {
        $this->preflopAction = $preflopAction;

        return $this;
    }

    public function getFlopAction(): ?string
    {
        return $this->flopAction;
    }

    public function setFlopAction(string $flopAction): self
    {
        $this->flopAction = $flopAction;

        return $this;
    }

    public function getTurnAction(): ?string
    {
        return $this->turnAction;
    }

    public function setTurnAction(?string $turnAction): self
    {
        $this->turnAction = $turnAction;

        return $this;
    }

    public function getRiverAction(): ?string
    {
        return $this->riverAction;
    }

    public function setRiverAction(?string $riverAction): self
    {
        $this->riverAction = $riverAction;

        return $this;
    }

    public function getShowdown(): ?string
    {
        return $this->showdown;
    }

    public function setShowdown(string $showdown): self
    {
        $this->showdown = $showdown;

        return $this;
    }

    public function getPlayersPosition(): array
    {
        return $this->playersPosition;
    }

    public function setPlayersPosition(array $playersPosition): self
    {
        $this->playersPosition = $playersPosition;

        return $this;
    }
}
