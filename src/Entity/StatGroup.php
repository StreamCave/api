<?php

namespace App\Entity;

use App\Repository\StatGroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: StatGroupRepository::class)]
class StatGroup
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['widget:read','overlay:read','model:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['widget:read','widget:write','overlay:read','model:read', 'overlay:write'])]
    private ?string $uuid = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['widget:read','widget:write','overlay:read','model:read', 'overlay:write'])]
    private ?string $matchId = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['widget:read','widget:write','overlay:read','model:read', 'overlay:write'])]
    private ?string $overlayId = null;

    #[ORM\ManyToMany(targetEntity: Widget::class, mappedBy: 'StatGroup')]
    #[Groups(['widget:read','widget:write','overlay:read','model:read', 'overlay:write'])]
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

    public function setUuid(string $uuid): static
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getMatchId(): ?string
    {
        return $this->matchId;
    }

    public function setMatchId(string $matchId): static
    {
        $this->matchId = $matchId;

        return $this;
    }

    public function getOverlayId(): ?string
    {
        return $this->overlayId;
    }

    public function setOverlayId(?string $overlayId): static
    {
        $this->overlayId = $overlayId;

        return $this;
    }

    /**
     * @return Collection<int, Widget>
     */
    public function getWidgets(): Collection
    {
        return $this->widgets;
    }

    public function addWidget(Widget $widget): static
    {
        if (!$this->widgets->contains($widget)) {
            $this->widgets->add($widget);
            $widget->addStatGroup($this);
        }

        return $this;
    }

    public function removeWidget(Widget $widget): static
    {
        if ($this->widgets->removeElement($widget)) {
            $widget->removeStatGroup($this);
        }

        return $this;
    }
}
