<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\DeleteModelController;
use App\Repository\ModelRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ModelRepository::class)]
#[ApiResource(operations: [
    new Get(
        uriTemplate: '/models/{uuid}',
        uriVariables: "uuid",
        status: 200,
        schemes: ['https'],
        openapiContext: ['summary' => 'Récupérer les données d\'un modèle'],
        normalizationContext: ['groups' => ['model:read']],
        security: 'is_granted("ROLE_ADMIN") or object.getOverlay().getUserOwner() == user',
        securityMessage: 'Vous n\'avez pas accès à ce modèle',
    ),
    new GetCollection(
        uriTemplate: '/models',
        status: 200,
        schemes: ['https'],
        openapiContext: ['summary' => 'Récupérer les données de tous les modèles'],
        normalizationContext: ['groups' => ['model:read']],
        security: 'is_granted("ROLE_ADMIN")',
        securityMessage: 'Seulement les administrateurs peuvent accéder à cette ressource.',
    ),
    new Post(
        uriTemplate: '/models/add',
        status: 201,
        schemes: ['https'],
        openapiContext: ['summary' => 'Ajouter un modèle'],
        normalizationContext: ['groups' => ['model:read']],
        denormalizationContext: ['groups' => ['model:write']],
    ),
    new Put(
        uriTemplate: '/models/{uuid}',
        uriVariables: "uuid",
        status: 200,
        schemes: ['https'],
        openapiContext: ['summary' => 'Modifier un modèle'],
        normalizationContext: ['groups' => ['model:read']],
        denormalizationContext: ['groups' => ['model:write']],
        security: 'is_granted("ROLE_ADMIN") or object.getOverlay().getUserOwner() == user',
        securityMessage: 'Vous n\'avez pas accès à ce modèle',
    ),
    new Delete(
        uriTemplate: '/models/{uuid}',
        uriVariables: "uuid",
        status: 204,
        schemes: ['https'],
        controller: DeleteModelController::class,
        openapiContext: ['summary' => 'Supprimer un modèle'],
        security: 'is_granted("ROLE_ADMIN") or object.getOverlay().getUserOwner() == user',
        securityMessage: 'Vous n\'avez pas accès à ce modèle',
    )
],schemes: ['https'], normalizationContext: ['groups' => ['model:read']], denormalizationContext: ['groups' => ['model:write']], openapiContext: ['summary' => 'Model'])]
class Model
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['model:read', 'overlay:read'])]
    #[ApiProperty(security: 'is_granted("ROLE_ADMIN")')]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Groups(['model:read', 'model:write', 'overlay:read'])]
    #[ApiProperty(security: 'is_granted("ROLE_ADMIN")')]
    private ?string $uuid;

    #[ORM\Column(length: 255)]
    #[Groups(['model:read', 'model:write', 'overlay:read', 'overlay:write'])]
    #[ApiProperty(securityPostDenormalize: 'is_granted("ROLE_ADMIN")')]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['model:read', 'model:write', 'overlay:read', 'overlay:write'])]
    #[ApiProperty(securityPostDenormalize: 'is_granted("ROLE_ADMIN")')]
    private ?string $image = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['model:read', 'model:write', 'overlay:read', 'overlay:write'])]
    #[ApiProperty(securityPostDenormalize: 'is_granted("ROLE_ADMIN")')]
    private ?string $description = null;

    #[ORM\Column]
    #[Groups(['model:read', 'model:write', 'overlay:read', 'overlay:write'])]
    #[ApiProperty(securityPostDenormalize: 'is_granted("ROLE_ADMIN")')]
    private ?int $price = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeInterface $createdDate;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $modifiedDate;

    #[ORM\OneToMany(mappedBy: 'Model', targetEntity: Overlay::class, cascade: ['persist'])]
    private Collection $overlays;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['model:read', 'model:write', 'overlay:read', 'overlay:write'])]
    private ?string $preview = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['model:read', 'model:write', 'overlay:read', 'overlay:write'])]
    private array $tags = [];

    #[Groups(['model:read', 'model:write', 'overlay:read', 'overlay:write'])]
    #[ORM\Column(nullable: true)]
    private array $rules = [];

    public function __construct()
    {
        $this->createdDate = new \DateTimeImmutable();
        $this->modifiedDate = new \DateTime();
        $this->uuid = Uuid::v4();
        $this->overlays = new ArrayCollection();
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

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;

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

    /**
     * @return Collection<int, Overlay>
     */
    public function getOverlays(): Collection
    {
        return $this->overlays;
    }

    public function addOverlay(Overlay $overlay): self
    {
        if (!$this->overlays->contains($overlay)) {
            $this->overlays->add($overlay);
            $overlay->setModel($this);
        }

        return $this;
    }

    public function removeOverlay(Overlay $overlay): self
    {
        if ($this->overlays->removeElement($overlay)) {
            // set the owning side to null (unless already changed)
            if ($overlay->getModel() === $this) {
                $overlay->setModel(null);
            }
        }

        return $this;
    }

    public function getPreview(): ?string
    {
        return $this->preview;
    }

    public function setPreview(?string $preview): self
    {
        $this->preview = $preview;

        return $this;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function setTags(?array $tags): self
    {
        $this->tags = $tags;

        return $this;
    }

    public function getRules(): array
    {
        return $this->rules;
    }

    public function setRules(?array $rules): self
    {
        $this->rules = $rules;

        return $this;
    }
}
