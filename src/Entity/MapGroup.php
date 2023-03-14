<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\DeleteMapGroup;
use App\Repository\MapGroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: MapGroupRepository::class)]
#[ApiResource(operations: [
    new Get(
        uriTemplate: '/map-groups/{uuid}',
        uriVariables: "uuid",
        status: 200,
        schemes: ['https'],
        openapiContext: ['summary' => 'Récupérer les données d\'un MapGroup'],
        normalizationContext: ['groups' => ['map_group:read']],
    ),
    new GetCollection(
        uriTemplate: '/map-groups',
        status: 200,
        schemes: ['https'],
        openapiContext: ['summary' => 'Récupérer les données de tous les MapGroup'],
        normalizationContext: ['groups' => ['map_group:read']],
    ),
    new Post(
        uriTemplate: '/map-groups/add',
        status: 201,
        schemes: ['https'],
        openapiContext: ['summary' => 'Ajouter un MapGroup'],
        normalizationContext: ['groups' => ['map_group:read']],
        denormalizationContext: ['groups' => ['map_group:write']],
    ),
    new Put(
        uriTemplate: '/map-groups/{uuid}',
        uriVariables: "uuid",
        status: 200,
        schemes: ['https'],
        openapiContext: ['summary' => 'Modifier un MapGroup'],
        normalizationContext: ['groups' => ['map_group:read']],
        denormalizationContext: ['groups' => ['map_group:write']],
    ),
    new Delete(
        uriTemplate: '/map-groups/{uuid}',
        uriVariables: "uuid",
        status: 204,
        schemes: ['https'],
        controller: DeleteMapGroup::class,
        openapiContext: ['summary' => 'Supprimer un MapGroup'],
    )
], schemes: ['https'], normalizationContext: ['groups' => ['map_group:read']], denormalizationContext: ['groups' => ['map_group:write']], openapiContext: ['summary' => 'MapGroup'])]
class MapGroup
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['map_group:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Groups(['map_group:read','map_group:write','widget:read','model:read','overlay:read', 'overlay:write'])]
    private ?string $uuid;

    #[ORM\ManyToOne(inversedBy: 'mapGroups')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['map_group:read','map_group:write','widget:read','model:read','overlay:read', 'overlay:write'])]
    private ?LibMap $libMap = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['map_group:read','map_group:write','widget:read','model:read','overlay:read', 'overlay:write'])]
    private ?string $pickTeam = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['map_group:read','map_group:write','widget:read','model:read','overlay:read', 'overlay:write'])]
    private ?string $winTeam = null;

    #[ORM\ManyToMany(targetEntity: Widget::class, mappedBy: 'mapGroup')]
    private Collection $widgets;

    public function __construct()
    {
        $this->uuid = Uuid::v4();
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

    public function getLibMap(): ?LibMap
    {
        return $this->libMap;
    }

    public function setLibMap(?LibMap $libMap): self
    {
        $this->libMap = $libMap;

        return $this;
    }
    public function getWinTeam(): ?string
    {
        return $this->winTeam;
    }

    public function setWinTeam(string $winTeam): self
    {
        $this->winTeam = $winTeam;

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
            $widget->addMapGroup($this);
        }

        return $this;
    }

    public function removeWidget(Widget $widget): self
    {
        if ($this->widgets->removeElement($widget)) {
            $widget->removeMapGroup($this);
        }

        return $this;
    }

    public function getPickTeam(): ?string
    {
        return $this->pickTeam;
    }

    public function setPickTeam(?string $pickTeam): self
    {
        $this->pickTeam = $pickTeam;

        return $this;
    }
}
