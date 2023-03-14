<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\DeletePlanningGroup;
use App\Repository\PlanningGroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: PlanningGroupRepository::class)]
#[ApiResource(operations: [
    new Get(
        uriTemplate: '/planning-groups/{uuid}',
        uriVariables: "uuid",
        status: 200,
        schemes: ['https'],
        openapiContext: ['summary' => 'Récupérer les données d\'un groupe de planning'],
        normalizationContext: ['groups' => ['planning_group:read']],
        security: 'is_granted("ROLE_ADMIN") or object.getWidgets().getModel().getOverlay().getUserOwner() == user',
        securityMessage: 'Vous n\'avez pas accès à ce groupe de planning',
    ),
    new GetCollection(
        uriTemplate: '/planning-groups',
        status: 200,
        schemes: ['https'],
        openapiContext: ['summary' => 'Récupérer les données de tous les groupes de planning'],
        normalizationContext: ['groups' => ['planning_group:read']],
        security: 'is_granted("ROLE_ADMIN")',
        securityMessage: 'Seulement les administrateurs peuvent accéder à cette ressource.',
    ),
    new Post(
        uriTemplate: '/planning-groups/add',
        status: 201,
        schemes: ['https'],
        openapiContext: ['summary' => 'Ajouter un groupe de planning'],
        normalizationContext: ['groups' => ['planning_group:read']],
        denormalizationContext: ['groups' => ['planning_group:write']],
    ),
    new Put(
        uriTemplate: '/planning-groups/{uuid}',
        uriVariables: "uuid",
        status: 200,
        schemes: ['https'],
        openapiContext: ['summary' => 'Modifier un groupe de matchs'],
        normalizationContext: ['groups' => ['planning_group:read']],
        denormalizationContext: ['groups' => ['planning_group:write']],
        security: 'is_granted("ROLE_ADMIN") or object.getWidgets().getModel().getOverlay().getUserOwner() == user',
        securityMessage: 'Vous n\'avez pas accès à ce groupe de planning',
    ),
    new Delete(
        uriTemplate: '/planning-groups/{uuid}',
        uriVariables: "uuid",
        status: 204,
        schemes: ['https'],
        controller: DeletePlanningGroup::class,
        openapiContext: ['summary' => 'Supprimer un groupe de matchs'],
        security: 'is_granted("ROLE_ADMIN") or object.getWidgets().getModel().getOverlay().getUserOwner() == user',
        securityMessage: 'Vous n\'avez pas accès à ce groupe de planning',
    )
], schemes: ['https'], normalizationContext: ['groups' => ['planning_group:read']], denormalizationContext: ['groups' => ['planning_group:write']], openapiContext: ['summary' => 'PlanningGroup'])]
class PlanningGroup
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['planning_group:read','widget:read','model:read','overlay:read', 'overlay:write'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::GUID, unique: true)]
    #[Groups(['planning_group:read', 'planning_group:write','widget:read','model:read','overlay:read', 'overlay:write'])]
    private ?string $uuid;

    #[ORM\Column(length: 255)]
    #[Groups(['planning_group:read', 'planning_group:write','widget:read','model:read','overlay:read', 'overlay:write'])]
    private ?string $teamA = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['planning_group:read', 'planning_group:write','widget:read','model:read','overlay:read', 'overlay:write'])]
    private ?string $logoA = null;

    #[ORM\Column(length: 255)]
    #[Groups(['planning_group:read', 'planning_group:write','widget:read','model:read','overlay:read', 'overlay:write'])]
    private ?string $teamB = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['planning_group:read', 'planning_group:write','widget:read','model:read','overlay:read', 'overlay:write'])]
    private ?string $logoB = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['planning_group:read', 'planning_group:write','widget:read','model:read','overlay:read', 'overlay:write'])]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\ManyToMany(targetEntity: Widget::class, mappedBy: 'planningGroup')]
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

    public function getTeamA(): ?string
    {
        return $this->teamA;
    }

    public function setTeamA(string $teamA): self
    {
        $this->teamA = $teamA;

        return $this;
    }

    public function getLogoA(): ?string
    {
        return $this->logoA;
    }

    public function setLogoA(?string $logoA): self
    {
        $this->logoA = $logoA;

        return $this;
    }

    public function getTeamB(): ?string
    {
        return $this->teamB;
    }

    public function setTeamB(string $teamB): self
    {
        $this->teamB = $teamB;

        return $this;
    }

    public function getLogoB(): ?string
    {
        return $this->logoB;
    }

    public function setLogoB(?string $logoB): self
    {
        $this->logoB = $logoB;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): self
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
            $widget->addPlanningGroup($this);
        }

        return $this;
    }

    public function removeWidget(Widget $widget): self
    {
        if ($this->widgets->removeElement($widget)) {
            $widget->removePlanningGroup($this);
        }

        return $this;
    }
}
