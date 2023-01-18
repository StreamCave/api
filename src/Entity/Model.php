<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\ModelRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ModelRepository::class)]
#[ApiResource(operations: [
    new Get(
        uriTemplate: '/models/{id}',
        requirements: ['id' => '\d+'],
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
        uriTemplate: '/models/{id}',
        requirements: ['id' => '\d+'],
        status: 200,
        schemes: ['https'],
        openapiContext: ['summary' => 'Modifier un modèle'],
        normalizationContext: ['groups' => ['model:read']],
        denormalizationContext: ['groups' => ['model:write']],
        security: 'is_granted("ROLE_ADMIN") or object.getOverlay().getUserOwner() == user',
        securityMessage: 'Vous n\'avez pas accès à ce modèle',
    ),
    new Delete(
        uriTemplate: '/models/{id}',
        requirements: ['id' => '\d+'],
        status: 204,
        schemes: ['https'],
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

    #[ORM\Column(type: Types::GUID, unique: true)]
    #[Groups(['model:read', 'model:write', 'overlay:read'])]
    #[ApiProperty(security: 'is_granted("ROLE_ADMIN")')]
    private ?string $uuid = null;

    #[ORM\Column(length: 255)]
    #[Groups(['model:read', 'model:write', 'overlay:read'])]
    #[ApiProperty(securityPostDenormalize: 'is_granted("ROLE_ADMIN")')]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['model:read', 'model:write', 'overlay:read'])]
    #[ApiProperty(securityPostDenormalize: 'is_granted("ROLE_ADMIN")')]
    private ?string $image = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['model:read', 'model:write', 'overlay:read'])]
    #[ApiProperty(securityPostDenormalize: 'is_granted("ROLE_ADMIN")')]
    private ?string $description = null;

    #[ORM\Column]
    #[Groups(['model:read', 'model:write', 'overlay:read'])]
    #[ApiProperty(securityPostDenormalize: 'is_granted("ROLE_ADMIN")')]
    private ?int $price = null;

    #[ORM\OneToMany(mappedBy: 'model', targetEntity: Widget::class)]
    #[Groups(['model:read', 'overlay:read'])]
    #[ApiProperty(securityPostDenormalize: 'is_granted("ROLE_ADMIN")')]
    private Collection $widgets;

    #[ORM\OneToOne(mappedBy: 'model', cascade: ['persist', 'remove'])]
    #[Groups(['model:read'])]
    #[ApiProperty(securityPostDenormalize: 'is_granted("ROLE_ADMIN")')]
    private ?Overlay $overlay = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeInterface $createdDate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $modifiedDate = null;

    public function __construct()
    {
        $this->widgets = new ArrayCollection();
        $this->createdDate = new \DateTimeImmutable();
        $this->modifiedDate = new \DateTime();
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
            $widget->setModel($this);
        }

        return $this;
    }

    public function removeWidget(Widget $widget): self
    {
        if ($this->widgets->removeElement($widget)) {
            // set the owning side to null (unless already changed)
            if ($widget->getModel() === $this) {
                $widget->setModel(null);
            }
        }

        return $this;
    }

    public function getOverlay(): ?Overlay
    {
        return $this->overlay;
    }

    public function setOverlay(?Overlay $overlay): self
    {
        // unset the owning side of the relation if necessary
        if ($overlay === null && $this->overlay !== null) {
            $this->overlay->setModel(null);
        }

        // set the owning side of the relation if necessary
        if ($overlay !== null && $overlay->getModel() !== $this) {
            $overlay->setModel($this);
        }

        $this->overlay = $overlay;

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
