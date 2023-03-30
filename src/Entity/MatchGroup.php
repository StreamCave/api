<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\DeleteMatchGroup;
use App\Controller\GetMatchGroupByOverlay;
use App\Repository\MatchGroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: MatchGroupRepository::class)]
#[ApiResource(operations: [
    new Get(
        uriTemplate: '/match-groups/{uuid}',
        uriVariables: "uuid",
        status: 200,
        schemes: ['https'],
        openapiContext: ['summary' => 'Récupérer les données d\'un groupe de matchs'],
        normalizationContext: ['groups' => ['match_group:read']],
        security: 'is_granted("ROLE_ADMIN") or object.getWidgets().getModel().getOverlay().getUserOwner() == user',
        securityMessage: 'Vous n\'avez pas accès à ce groupe de matchs',
    ),
    new GetCollection(
        uriTemplate: '/match-groups',
        status: 200,
        schemes: ['https'],
        openapiContext: ['summary' => 'Récupérer les données de tous les groupes de matchs'],
        normalizationContext: ['groups' => ['match_group:read']],
        security: 'is_granted("ROLE_ADMIN")',
        securityMessage: 'Seulement les administrateurs peuvent accéder à cette ressource.',
    ),
    new Get(
        uriTemplate: '/match-groups/overlays/{overlayuuid}',
        uriVariables: "overlayuuid",
        defaults: ['_api_receive' => false],
        status: 200,
        schemes: ['https'],
        controller: GetMatchGroupByOverlay::class,
        openapiContext: ['summary' => 'Récupérer les données de tous les groupes de matchs d\'un overlay'],
        normalizationContext: ['groups' => ['match_group:read']],
        security: 'is_granted("ROLE_ADMIN") or object.getWidgets().getModel().getOverlay().getUserOwner() == user',
        securityMessage: 'Vous n\'avez pas accès à ce groupe de matchs',
    ),
    new Post(
        uriTemplate: '/match-groups/add',
        status: 201,
        schemes: ['https'],
        openapiContext: ['summary' => 'Ajouter un groupe de matchs'],
        normalizationContext: ['groups' => ['match_group:read']],
        denormalizationContext: ['groups' => ['match_group:write']],
    ),
    new Put(
        uriTemplate: '/match-groups/{uuid}',
        uriVariables: "uuid",
        status: 200,
        schemes: ['https'],
        openapiContext: ['summary' => 'Modifier un groupe de matchs'],
        normalizationContext: ['groups' => ['match_group:read']],
        denormalizationContext: ['groups' => ['match_group:write']],
        security: 'is_granted("ROLE_ADMIN") or object.getWidgets().getModel().getOverlay().getUserOwner() == user',
        securityMessage: 'Vous n\'avez pas accès à ce groupe de matchs',
    ),
    new Delete(
        uriTemplate: '/match-groups/{uuid}',
        uriVariables: "uuid",
        status: 204,
        schemes: ['https'],
        controller: DeleteMatchGroup::class,
        openapiContext: ['summary' => 'Supprimer un groupe de matchs'],
        security: 'is_granted("ROLE_ADMIN") or object.getWidgets().getModel().getOverlay().getUserOwner() == user',
        securityMessage: 'Vous n\'avez pas accès à ce groupe de matchs',
    )
], schemes: ['https'], normalizationContext: ['groups' => ['match_group:read']], denormalizationContext: ['groups' => ['match_group:write']], openapiContext: ['summary' => 'MatchGroup'])]
class MatchGroup
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['match_group:read','widget:read','model:read','overlay:read', 'overlay:write'])]
    #[ApiProperty(security: 'is_granted("ROLE_ADMIN")')]
    private ?int $id = null;

    #[ORM\Column(type: Types::GUID, unique: true)]
    #[Groups(['match_group:read', 'match_group:write','widget:read','model:read','overlay:read', 'overlay:write'])]
    #[ApiProperty(security: 'is_granted("ROLE_ADMIN")')]
    private ?string $uuid;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['match_group:read', 'match_group:write','widget:read','model:read','overlay:read', 'overlay:write'])]
    private ?string $teamNameA = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['match_group:read', 'match_group:write','widget:read','model:read','overlay:read', 'overlay:write'])]
    private ?string $logoTeamA = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['match_group:read', 'match_group:write','widget:read','model:read','overlay:read', 'overlay:write'])]
    private array $playersTeamA = [];

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['match_group:read', 'match_group:write','widget:read','model:read','overlay:read', 'overlay:write'])]
    private ?string $scoreA = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['match_group:read', 'match_group:write','widget:read','model:read','overlay:read', 'overlay:write'])]
    private ?string $teamNameB = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['match_group:read', 'match_group:write','widget:read','model:read','overlay:read', 'overlay:write'])]
    private ?string $logoTeamB = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['match_group:read', 'match_group:write','widget:read','model:read','overlay:read', 'overlay:write'])]
    private array $playersTeamB = [];

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['match_group:read', 'match_group:write','widget:read','model:read','overlay:read', 'overlay:write'])]
    private ?string $scoreB = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['match_group:read', 'match_group:write','widget:read','model:read','overlay:read', 'overlay:write'])]
    private ?\DateTimeImmutable $startDate = null;


    #[ORM\Column(nullable: true)]
    #[Groups(['match_group:read', 'match_group:write','widget:read','model:read','overlay:read', 'overlay:write'])]
    private ?bool $nextMatch = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['match_group:read', 'match_group:write','widget:read','model:read','overlay:read', 'overlay:write'])]
    private ?bool $visible = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['match_group:read', 'match_group:write','widget:read','model:read','overlay:read', 'overlay:write'])]
    private ?string $overlayId = null;

    #[ORM\ManyToMany(targetEntity: Widget::class, mappedBy: 'matchGroup')]
    private Collection $widgets;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['match_group:read', 'match_group:write','widget:read','model:read','overlay:read', 'overlay:write'])]
    private ?string $rounds = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['match_group:read', 'match_group:write','widget:read','model:read','overlay:read', 'overlay:write'])]
    private ?string $hours = null;

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

    public function getScoreA(): ?string
    {
        return $this->scoreA;
    }

    public function setScoreA(?string $scoreA): self
    {
        $this->scoreA = $scoreA;

        return $this;
    }

    public function getScoreB(): ?string
    {
        return $this->scoreB;
    }

    public function setScoreB(?string $scoreB): self
    {
        $this->scoreB = $scoreB;

        return $this;
    }

    public function getPlayersTeamA(): array
    {
        return $this->playersTeamA;
    }

    public function setPlayersTeamA(?array $playersTeamA): self
    {
        $this->playersTeamA = $playersTeamA;

        return $this;
    }

    public function getPlayersTeamB(): array
    {
        return $this->playersTeamB;
    }

    public function setPlayersTeamB(?array $playersTeamB): self
    {
        $this->playersTeamB = $playersTeamB;

        return $this;
    }

    public function isNextMatch(): ?bool
    {
        return $this->nextMatch;
    }

    public function setNextMatch(?bool $nextMatch): self
    {
        $this->nextMatch = $nextMatch;

        return $this;
    }

    public function getOverlayId(): ?string
    {
        return $this->overlayId;
    }

    public function setOverlayId(?string $overlayId): self
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

    public function addWidget(Widget $widget): self
    {
        if (!$this->widgets->contains($widget)) {
            $this->widgets->add($widget);
            $widget->addMatchGroup($this);
        }

        return $this;
    }

    public function removeWidget(Widget $widget): self
    {
        if ($this->widgets->removeElement($widget)) {
            $widget->removeMatchGroup($this);
        }

        return $this;
    }

    public function isVisible(): ?bool
    {
        return $this->visible;
    }

    public function setVisible(?bool $visible): self
    {
        $this->visible = $visible;

        return $this;
    }

    public function getRounds(): ?string
    {
        return $this->rounds;
    }

    public function setRounds(?string $rounds): self
    {
        $this->rounds = $rounds;

        return $this;
    }

    public function getHours(): ?string
    {
        return $this->hours;
    }

    public function setHours(?string $hours): self
    {
        $this->hours = $hours;

        return $this;
    }
}
