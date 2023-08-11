<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\DeleteWidgetController;
use App\Controller\EditComponent;
use App\Repository\WidgetRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: WidgetRepository::class)]
#[ApiResource(operations: [
    new Get(
        uriTemplate: '/widgets/{uuid}',
        uriVariables: "uuid",
        status: 200,
        schemes: ['https'],
        openapiContext: ['summary' => 'Récupérer les données d\'un widget'],
        normalizationContext: ['groups' => ['widget:read']],
        security: 'is_granted("ROLE_ADMIN") or object.getModel().getOverlay().getUserOwner() == user or object.getModel().getOverlay().getUserAccess() == user',
        securityMessage: 'Seulement les administrateurs peuvent accéder à cette ressource.',
    ),
    new GetCollection(
        uriTemplate: '/widgets',
        status: 200,
        schemes: ['https'],
        openapiContext: ['summary' => 'Récupérer les données de tous les widgets'],
        normalizationContext: ['groups' => ['widget:read']],
        security: 'is_granted("ROLE_ADMIN") or object.getModel().getOverlay().getUserOwner() == user or object.getModel().getOverlay().getUserAccess() == user',
        securityMessage: 'Seulement les administrateurs peuvent accéder à cette ressource.',
    ),
    new Post(
        uriTemplate: '/widgets/add',
        status: 201,
        schemes: ['https'],
        openapiContext: ['summary' => 'Ajouter un widget'],
        normalizationContext: ['groups' => ['widget:read']],
        denormalizationContext: ['groups' => ['widget:write']],
    ),
    new Post(
        uriTemplate: '/widgets/components',
        status: 200,
        schemes: ['https'],
        controller: EditComponent::class,
        normalizationContext: ['groups' => ['widget:read']],
        denormalizationContext: ['groups' => ['widget:write']],
        security: 'is_granted("ROLE_ADMIN") or object.getModel().getOverlay().getUserOwner() == user or object.getModel().getOverlay().getUserAccess() == user',
        securityMessage: 'Vous n\'avez pas accès à ce widget',
    ),
    new Put(
        uriTemplate: '/widgets/{uuid}',
        uriVariables: "uuid",
        status: 200,
        schemes: ['https'],
        openapiContext: ['summary' => 'Modifier un widget'],
        normalizationContext: ['groups' => ['widget:read']],
        denormalizationContext: ['groups' => ['widget:write']],
        security: 'is_granted("ROLE_ADMIN") or object.getModel().getOverlay().getUserOwner() == user or object.getModel().getOverlay().getUserAccess() == user',
        securityMessage: 'Vous n\'avez pas accès à ce widget',
    ),
    new Patch(
        uriTemplate: '/widgets/{uuid}',
        uriVariables: "uuid",
        status: 200,
        schemes: ['https'],
        openapiContext: ['summary' => 'Modifier un widget'],
        normalizationContext: ['groups' => ['widget:read']],
        denormalizationContext: ['groups' => ['widget:write']],
        security: 'is_granted("ROLE_ADMIN") or object.getModel().getOverlay().getUserOwner() == user or object.getModel().getOverlay().getUserAccess() == user',
        securityMessage: 'Vous n\'avez pas accès à ce widget',
    ),
    new Delete(
        uriTemplate: '/widgets/{uuid}',
        uriVariables: "uuid",
        status: 204,
        schemes: ['https'],
        controller: DeleteWidgetController::class,
        openapiContext: ['summary' => 'Supprimer un widget'],
        security: 'is_granted("ROLE_ADMIN") or object.getModel().getOverlay().getUserOwner() == user or object.getModel().getOverlay().getUserAccess() == user',
        securityMessage: 'Vous n\'avez pas accès à ce widget',
    )
], schemes: ['https'], normalizationContext: ['groups' => ['widget:read']], denormalizationContext: ['groups' => ['widget:write']], openapiContext: ['summary' => 'Widget'])]
class Widget
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['widget:read','overlay:read','model:read'])]
    #[ApiProperty(security: 'is_granted("ROLE_ADMIN")')]
    private ?int $id = null;

    #[ORM\Column(type: Types::GUID, unique: true)]
    #[Groups(['widget:read','widget:write','overlay:read','model:read', 'overlay:write'])]
    #[ApiProperty(security: 'is_granted("ROLE_ADMIN")')]
    private ?string $uuid;

    #[ORM\Column(length: 255)]
    #[Groups(['widget:read','widget:write','overlay:read','model:read', 'overlay:write'])]
    #[ApiProperty(securityPostDenormalize: 'is_granted("ROLE_ADMIN")')]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['widget:read','widget:write','overlay:read','model:read', 'overlay:write'])]
    #[ApiProperty(securityPostDenormalize: 'is_granted("ROLE_ADMIN")')]
    private ?string $image = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['widget:read','widget:write','overlay:read','model:read', 'overlay:write'])]
    #[ApiProperty(securityPostDenormalize: 'is_granted("ROLE_ADMIN")')]
    private ?string $description = null;

    #[ORM\Column]
    #[Groups(['widget:read','widget:write','overlay:read','model:read', 'overlay:write'])]
    private ?bool $visible = null;

    #[ORM\ManyToOne(cascade: ['persist'], inversedBy: 'widgets')]
    #[Groups(['widget:read','widget:write','overlay:read','model:read', 'overlay:write'])]
    #[ApiProperty(securityPostDenormalize: 'is_granted("ROLE_ADMIN")')]
    private ?InfoGroup $infoGroup = null;

    #[ORM\ManyToOne(cascade: ['persist'], inversedBy: 'widgets')]
    #[Groups(['widget:read','widget:write','overlay:read','model:read', 'overlay:write'])]
    #[ApiProperty(securityPostDenormalize: 'is_granted("ROLE_ADMIN")')]
    private ?TweetGroup $tweetGroup = null;

    #[ORM\ManyToOne(cascade: ['persist'], inversedBy: 'widgets')]
    #[Groups(['widget:read','widget:write','overlay:read','model:read', 'overlay:write'])]
    #[ApiProperty(securityPostDenormalize: 'is_granted("ROLE_ADMIN")')]
    private ?PollGroup $pollGroup = null;

    #[ORM\ManyToOne(cascade: ['persist'], inversedBy: 'widgets')]
    #[Groups(['widget:read','widget:write','overlay:read','model:read', 'overlay:write'])]
    #[ApiProperty(securityPostDenormalize: 'is_granted("ROLE_ADMIN")')]
    private ?PopupGroup $popupGroup = null;

    #[ORM\ManyToMany(targetEntity: CameraGroup::class, inversedBy: 'widgets', cascade: ['persist'])]
    #[Groups(['widget:read','widget:write','overlay:read','model:read', 'overlay:write'])]
    private Collection $cameraGroup;

    #[ORM\ManyToMany(targetEntity: MapGroup::class, inversedBy: 'widgets', cascade: ['persist'])]
    #[Groups(['widget:read','widget:write','overlay:read','model:read', 'overlay:write'])]
    private Collection $mapGroup;

    #[ORM\ManyToMany(targetEntity: PlanningGroup::class, inversedBy: 'widgets', cascade: ['persist'])]
    #[Groups(['widget:read','widget:write','overlay:read','model:read', 'overlay:write'])]
    private Collection $planningGroup;

    #[ORM\ManyToMany(targetEntity: MatchGroup::class, inversedBy: 'widgets')]
    #[Groups(['widget:read','widget:write','overlay:read','model:read', 'overlay:write'])]
    private Collection $matchGroup;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeInterface $createdDate;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $modifiedDate;

    #[ORM\ManyToOne(inversedBy: 'widgets', cascade: ['persist'])]
    #[Groups(['widget:read','widget:write','overlay:read','model:read', 'overlay:write'])]
    private ?Overlay $overlay = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[Groups(['widget:read','widget:write','overlay:read','model:read', 'overlay:write'])]
    private ?Brackets $bracket = null;

    #[ORM\ManyToOne(inversedBy: 'widgets')]
    #[Groups(['widget:read','widget:write','overlay:read','model:read', 'overlay:write'])]
    private ?TwitchGroup $twitchGroup = null;

    #[ORM\ManyToMany(targetEntity: StatGroup::class, inversedBy: 'widgets')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['widget:read','widget:write','overlay:read','model:read', 'overlay:write'])]
    private Collection $StatGroup;

    #[ORM\Column(nullable: true)]
    #[Groups(['widget:read','widget:write','overlay:read','model:read', 'overlay:write'])]
    private array $styles = [];


    public function __construct()
    {
        $this->createdDate = new \DateTimeImmutable();
        $this->modifiedDate = new \DateTime();
        $this->uuid = Uuid::v4();
        $this->cameraGroup = new ArrayCollection();
        $this->mapGroup = new ArrayCollection();
        $this->planningGroup = new ArrayCollection();
        $this->matchGroup = new ArrayCollection();
        $this->StatGroup = new ArrayCollection();
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function isVisible(): ?bool
    {
        return $this->visible;
    }

    public function setVisible(bool $visible): self
    {
        $this->visible = $visible;

        return $this;
    }

    public function getInfoGroup(): ?InfoGroup
    {
        return $this->infoGroup;
    }

    public function setInfoGroup(?InfoGroup $infoGroup): self
    {
        $this->infoGroup = $infoGroup;

        return $this;
    }

    public function getTweetGroup(): ?TweetGroup
    {
        return $this->tweetGroup;
    }

    public function setTweetGroup(?TweetGroup $tweetGroup): self
    {
        $this->tweetGroup = $tweetGroup;

        return $this;
    }

    public function getPollGroup(): ?PollGroup
    {
        return $this->pollGroup;
    }

    public function setPollGroup(?PollGroup $pollGroup): self
    {
        $this->pollGroup = $pollGroup;

        return $this;
    }

    public function getPopupGroup(): ?PopupGroup
    {
        return $this->popupGroup;
    }

    public function setPopupGroup(?PopupGroup $popupGroup): self
    {
        $this->popupGroup = $popupGroup;

        return $this;
    }

    /**
     * @return Collection<int, cameraGroup>
     */
    public function getCameraGroup(): Collection
    {
        return $this->cameraGroup;
    }

    public function addCameraGroup(cameraGroup $cameraGroup): self
    {
        if (!$this->cameraGroup->contains($cameraGroup)) {
            $this->cameraGroup->add($cameraGroup);
        }

        return $this;
    }

    public function removeCameraGroup(cameraGroup $cameraGroup): self
    {
        $this->cameraGroup->removeElement($cameraGroup);

        return $this;
    }

    /**
     * @return Collection<int, MapGroup>
     */
    public function getMapGroup(): Collection
    {
        return $this->mapGroup;
    }

    public function addMapGroup(MapGroup $mapGroup): self
    {
        if (!$this->mapGroup->contains($mapGroup)) {
            $this->mapGroup->add($mapGroup);
        }

        return $this;
    }

    public function removeMapGroup(MapGroup $mapGroup): self
    {
        $this->mapGroup->removeElement($mapGroup);

        return $this;
    }

    /**
     * @return Collection<int, PlanningGroup>
     */
    public function getPlanningGroup(): Collection
    {
        return $this->planningGroup;
    }

    public function addPlanningGroup(PlanningGroup $planningGroup): self
    {
        if (!$this->planningGroup->contains($planningGroup)) {
            $this->planningGroup->add($planningGroup);
        }

        return $this;
    }

    public function removePlanningGroup(PlanningGroup $planningGroup): self
    {
        $this->planningGroup->removeElement($planningGroup);

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

    public function getOverlay(): ?Overlay
    {
        return $this->overlay;
    }

    public function setOverlay(?Overlay $overlay): self
    {
        $this->overlay = $overlay;

        return $this;
    }

    /**
     * @return Collection<int, MatchGroup>
     */
    public function getMatchGroup(): Collection
    {
        return $this->matchGroup;
    }

    public function addMatchGroup(MatchGroup $matchGroup): self
    {
        if (!$this->matchGroup->contains($matchGroup)) {
            $this->matchGroup->add($matchGroup);
        }

        return $this;
    }

    public function removeMatchGroup(MatchGroup $matchGroup): self
    {
        $this->matchGroup->removeElement($matchGroup);

        return $this;
    }

    public function getBracket(): ?Brackets
    {
        return $this->bracket;
    }

    public function setBracket(?Brackets $bracket): self
    {
        $this->bracket = $bracket;

        return $this;
    }

    public function getTwitchGroup(): ?TwitchGroup
    {
        return $this->twitchGroup;
    }

    public function setTwitchGroup(?TwitchGroup $twitchGroup): self
    {
        $this->twitchGroup = $twitchGroup;

        return $this;
    }

    public function getStyles(): array
    {
        return $this->styles;
    }

    public function setStyles(?array $styles): self
    {
        $this->styles = $styles;

        return $this;
    }

    /**
     * @return Collection<int, StatGroup>
     */
    public function getStatGroup(): Collection
    {
        return $this->StatGroup;
    }

    public function addStatGroup(StatGroup $statGroup): static
    {
        if (!$this->StatGroup->contains($statGroup)) {
            $this->StatGroup->add($statGroup);
        }

        return $this;
    }

    public function removeStatGroup(StatGroup $statGroup): static
    {
        $this->StatGroup->removeElement($statGroup);

        return $this;
    }
}
