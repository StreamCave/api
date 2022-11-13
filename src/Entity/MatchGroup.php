<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\MatchGroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MatchGroupRepository::class)]
#[ApiResource]
class MatchGroup
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::GUID, unique: true)]
    private ?string $uuid = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $teamNameA = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $logoTeamA = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $teamNameB = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $logoTeamB = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $startDate = null;

    #[ORM\OneToMany(mappedBy: 'matchGroup', targetEntity: Widget::class)]
    private Collection $widgets;

    public function __construct()
    {
        $this->widgets = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getTeamNameA(): ?string
    {
        return $this->teamNameA;
    }

    public function setTeamNameA(?string $teamNameA): self
    {
        $this->teamNameA = $teamNameA;

        return $this;
    }

    public function getLogoTeamA(): ?string
    {
        return $this->logoTeamA;
    }

    public function setLogoTeamA(?string $logoTeamA): self
    {
        $this->logoTeamA = $logoTeamA;

        return $this;
    }

    public function getTeamNameB(): ?string
    {
        return $this->teamNameB;
    }

    public function setTeamNameB(?string $teamNameB): self
    {
        $this->teamNameB = $teamNameB;

        return $this;
    }

    public function getLogoTeamB(): ?string
    {
        return $this->logoTeamB;
    }

    public function setLogoTeamB(?string $logoTeamB): self
    {
        $this->logoTeamB = $logoTeamB;

        return $this;
    }

    public function getStartDate(): ?\DateTimeImmutable
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTimeImmutable $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * @return Collection<int, Widget>
     */
    public function getWidgets(): Collection
    {
        return $this->widgets;
    }

    public function addWidget(Widget $widget): self
    {
        if (!$this->widgets->contains($widget)) {
            $this->widgets->add($widget);
            $widget->setMatchGroup($this);
        }

        return $this;
    }

    public function removeWidget(Widget $widget): self
    {
        if ($this->widgets->removeElement($widget)) {
            // set the owning side to null (unless already changed)
            if ($widget->getMatchGroup() === $this) {
                $widget->setMatchGroup(null);
            }
        }

        return $this;
    }
}
