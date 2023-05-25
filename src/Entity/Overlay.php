<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\ChangeModelOverlayController;
use App\Controller\CreateOverlayController;
use App\Controller\DeleteOverlayController;
use App\Controller\GetAllOverlaysController;
use App\Repository\OverlayRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: OverlayRepository::class)]
#[ApiResource(operations: [
    new Get(
        uriTemplate: '/overlays/{uuid}',
        uriVariables: "uuid",
        status: 200,
        schemes: ['https'],
        openapiContext: ['summary' => 'Récupérer les données d\'un overlay'],
        normalizationContext: ['groups' => ['overlay:read']],
        security: 'object.getUserOwner() == user or object.getUserAccess() == user or is_granted("OVERLAY_EDIT", object)',
        securityMessage: 'Seulement les administrateurs peuvent accéder à cette ressource.',
        securityPostDenormalize: 'object.getUserOwner() == user or object.getUserAccess() == user or is_granted("OVERLAY_EDIT", object)',
        securityPostDenormalizeMessage: 'Vous n\'avez pas accès à cet overlay',
        securityPostValidation: 'object.getUserOwner() == user or object.getUserAccess() == user or is_granted("OVERLAY_EDIT", object)',
        securityPostValidationMessage: 'Vous n\'avez pas accès à cet overlay',
    ),
    new GetCollection(
        uriTemplate: '/overlays',
        status: 200,
        schemes: ['https'],
        openapiContext: ['summary' => 'Récupérer les données de tous les overlays'],
        normalizationContext: ['groups' => ['overlay:read']],
        security: 'is_granted("ROLE_ADMIN")',
        securityMessage: 'Seulement les administrateurs peuvent accéder à la liste des overlays.',
    ),
    new GetCollection(
        uriTemplate: '/allOverlays/{uuid}',
        uriVariables: "uuid",
        status: 200,
        schemes: ['https'],
        controller: GetAllOverlaysController::class,
        openapiContext: ['summary' => 'Récupérer les données de tous les overlays'],
        normalizationContext: ['groups' => ['overlay:read']]
    ),
    new Post(
        uriTemplate: '/overlays/add',
        status: 201,
        schemes: ['https'],
        controller: CreateOverlayController::class,
        openapiContext: ['summary' => 'Ajouter un overlay'],
        normalizationContext: ['groups' => ['overlay:read']],
        denormalizationContext: ['groups' => ['overlay:write']],
    ),
    new Put(
        uriTemplate: '/overlays/{uuid}',
        uriVariables: "uuid",
        status: 200,
        schemes: ['https'],
        controller: ChangeModelOverlayController::class,
        openapiContext: ['summary' => 'Modifier un overlay'],
        normalizationContext: ['groups' => ['overlay:read']],
        denormalizationContext: ['groups' => ['overlay:write']],
        security: 'object.getUserOwner() == user or is_granted("OVERLAY_EDIT", object)',
        securityMessage: 'Vous n\'avez pas accès à cet overlay',
        securityPostDenormalize: 'object.getUserOwner() == user or is_granted("OVERLAY_EDIT", object)',
        securityPostDenormalizeMessage: 'Vous n\'avez pas accès à cet overlay',
        securityPostValidation: 'object.getUserOwner() == user or is_granted("OVERLAY_EDIT", object)',
        securityPostValidationMessage: 'Vous n\'avez pas accès à cet overlay',
    ),
    new Delete(
        uriTemplate: '/overlays/{uuid}',
        uriVariables: "uuid",
        status: 204,
        schemes: ['https'],
        controller: DeleteOverlayController::class,
        openapiContext: ['summary' => 'Supprimer un overlay'],
        security: 'object.getUserOwner() == user',
        securityMessage: 'Vous n\'avez pas accès à cet overlay',
        securityPostDenormalize: 'object.getUserOwner() == user',
        securityPostDenormalizeMessage: 'Vous n\'avez pas accès à cet overlay',
        securityPostValidation: 'object.getUserOwner() == user',
        securityPostValidationMessage: 'Vous n\'avez pas accès à cet overlay',
    )
], schemes: ['https'], normalizationContext: ['groups' => ['overlay:read']], denormalizationContext: ['groups' => ['overlay:write']], openapiContext: ['summary' => 'Overlay'])]
class Overlay
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['overlay:read'])]
    #[ApiProperty(security: 'is_granted("ROLE_ADMIN")')]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Groups(['overlay:read','overlay:write'])]
    private ?string $uuid;

    #[ORM\Column(length: 255)]
    #[Groups(['overlay:read','overlay:write'])]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'overlays')]
    #[Groups(['overlay:read','overlay:write'])]
    #[ApiProperty(securityPostDenormalize: 'is_granted("UPDATE","ROLE_ADMIN")')]
    private ?User $userOwner = null;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'overlaysAccess')]
    #[Groups(['overlay:read','overlay:write'])]
    private Collection $userAccess;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeInterface $createdDate;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $modifiedDate;
    
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['overlay:read','overlay:write'])]
    private ?string $image = null;

    #[ORM\ManyToOne(inversedBy: 'overlays', cascade: ['persist'])]
    #[Groups(['overlay:read','overlay:write'])]
    private ?Model $Model = null;

    #[ORM\OneToMany(mappedBy: 'overlay', targetEntity: Widget::class)]
    #[Groups(['overlay:read','overlay:write'])]
    private Collection $widgets;

    public function __construct()
    {
        $this->userAccess = new ArrayCollection();
        $this->createdDate = new \DateTimeImmutable();
        $this->modifiedDate = new \DateTime();
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getUserOwner(): ?User
    {
        return $this->userOwner;
    }

    public function setUserOwner(?User $userOwner): self
    {
        $this->userOwner = $userOwner;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUserAccess(): Collection
    {
        return $this->userAccess;
    }

    public function addUserAccess(User $userAccess): self
    {
        if (!$this->userAccess->contains($userAccess)) {
            $this->userAccess->add($userAccess);
        }

        return $this;
    }

    public function removeUserAccess(User $userAccess): self
    {
        $this->userAccess->removeElement($userAccess);

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

    public function getModel(): ?Model
    {
        return $this->Model;
    }

    public function setModel(?Model $Model): self
    {
        $this->Model = $Model;

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
            $widget->setOverlay($this);
        }

        return $this;
    }

    public function removeWidget(Widget $widget): self
    {
        if ($this->widgets->removeElement($widget)) {
            // set the owning side to null (unless already changed)
            if ($widget->getOverlay() === $this) {
                $widget->setOverlay(null);
            }
        }

        return $this;
    }
}
