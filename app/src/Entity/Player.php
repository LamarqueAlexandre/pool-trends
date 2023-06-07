<?php

namespace App\Entity;

use App\Repository\PlayerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlayerRepository::class)]
class Player
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $pseudo = null;

    #[ORM\ManyToMany(targetEntity: Hand::class, mappedBy: 'players')]
    private Collection $hands;

    public function __construct()
    {
        $this->hands = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): self
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    /**
     * @return Collection<int, Hand>
     */
    public function getHands(): Collection
    {
        return $this->hands;
    }

    public function addHand(Hand $hand): self
    {
        if (!$this->hands->contains($hand)) {
            $this->hands->add($hand);
            $hand->addPlayer($this);
        }

        return $this;
    }

    public function removeHand(Hand $hand): self
    {
        if ($this->hands->removeElement($hand)) {
            $hand->removePlayer($this);
        }

        return $this;
    }
}
