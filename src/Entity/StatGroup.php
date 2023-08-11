<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\DeleteWidgetController;
use App\Controller\EditComponent;
use App\Repository\StatGroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: StatGroupRepository::class)]
#[ApiResource(operations: [
    new Get(
        uriTemplate: '/stat-group/{uuid}',
        uriVariables: "uuid",
        status: 200,
        schemes: ['https'],
        openapiContext: ['summary' => 'Récupérer les données du StatGroup'],
        normalizationContext: ['groups' => ['stat_group:read']]
    ),
    new GetCollection(
        uriTemplate: '/stat-group',
        status: 200,
        schemes: ['https'],
        openapiContext: ['summary' => 'Récupérer les données de tous les StatGroup'],
        normalizationContext: ['groups' => ['stat_group:read']]
    )
], schemes: ['https'], normalizationContext: ['groups' => ['stat_group:read']], denormalizationContext: ['groups' => ['stat_group:write']], openapiContext: ['summary' => 'StatGroup'])]
class StatGroup
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['stat_group:read','widget:read','overlay:read','model:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['stat_group:read','stat_group:write','widget:read','widget:write','overlay:read','model:read', 'overlay:write'])]
    private ?string $matchId = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['stat_group:read','stat_group:write','widget:read','widget:write','overlay:read','model:read', 'overlay:write'])]
    private ?string $overlayId = null;

    #[ORM\ManyToMany(targetEntity: Widget::class, mappedBy: 'StatGroup')]
    #[Groups(['stat_group:read','stat_group:write','widget:read','widget:write','overlay:read','model:read', 'overlay:write'])]
    private Collection $widgets;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['stat_group:read','stat_group:write','widget:read','widget:write','overlay:read','model:read', 'overlay:write'])]
    private ?string $status = null;

    public function __construct()
    {
        $this->widgets = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): static
    {
        $this->status = $status;

        return $this;
    }
}
