<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\MatchGroupController;
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
        uriVariables: [
            "uuid" => new Link(
                fromClass: MatchGroup::class,
            )
        ],
        status: 200,
        schemes: ['https'],
        controller: MatchGroupController::class,
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
    new Post(
        uriTemplate: '/match-groups/add',
        status: 201,
        schemes: ['https'],
        openapiContext: ['summary' => 'Ajouter un groupe de matchs'],
        normalizationContext: ['groups' => ['match_group:read']],
        denormalizationContext: ['groups' => ['match_group:write']],
    ),
    new Put(
        uriTemplate: '/match-groups/{id}',
        requirements: ['id' => '\d+'],
        status: 200,
        schemes: ['https'],
        openapiContext: ['summary' => 'Modifier un groupe de matchs'],
        normalizationContext: ['groups' => ['match_group:read']],
        denormalizationContext: ['groups' => ['match_group:write']],
        security: 'is_granted("ROLE_ADMIN") or object.getWidgets().getModel().getOverlay().getUserOwner() == user',
        securityMessage: 'Vous n\'avez pas accès à ce groupe de matchs',
    ),
    new Delete(
        uriTemplate: '/match-groups/{id}',
        requirements: ['id' => '\d+'],
        status: 204,
        schemes: ['https'],
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

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['match_group:read', 'match_group:write','widget:read','model:read','overlay:read', 'overlay:write'])]
    private ?string $teamNameB = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['match_group:read', 'match_group:write','widget:read','model:read','overlay:read', 'overlay:write'])]
    private ?string $logoTeamB = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['match_group:read', 'match_group:write','widget:read','model:read','overlay:read', 'overlay:write'])]
    private ?\DateTimeImmutable $startDate = null;

    #[ORM\OneToMany(mappedBy: 'matchGroup', targetEntity: Widget::class)]
    #[Groups(['match_group:read', 'match_group:write'])]
    #[ApiProperty(securityPostDenormalize: 'is_granted("ROLE_ADMIN")')]
    private Collection $widgets;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeInterface $createdDate;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $modifiedDate;

    public function __construct()
    {
        $this->widgets = new ArrayCollection();
        $this->createdDate = new \DateTimeImmutable();
        $this->modifiedDate = new \DateTime();
        $this->uuid = Uuid::v4();
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

    public function getCreatedDate(): ?\DateTimeInterface
    {
        return $this->createdDate;
    }

    public function setCreatedDate(\DateTimeInterface $createdDate): self
    {
        $this->createdDate = $createdDate;

        return $this;
    }

    public function getModifiedDate(): ?\DateTimeInterface
    {
        return $this->modifiedDate;
    }

    public function setModifiedDate(\DateTimeInterface $modifiedDate): self
    {
        $this->modifiedDate = $modifiedDate;

        return $this;
    }
}
