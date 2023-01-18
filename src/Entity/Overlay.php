<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
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
        uriTemplate: '/overlays/{id}',
        requirements: ['id' => '\d+'],
        status: 200,
        schemes: ['https'],
        openapiContext: ['summary' => 'Récupérer les données d\'un overlay'],
        normalizationContext: ['groups' => ['overlay:read']],
        security: 'is_granted("ROLE_ADMIN") or object.getUserOwner() == user or is_granted("OVERLAY_VIEW", object)',
        securityMessage: 'Vous n\'avez pas accès à cet overlay',
    ),
    new GetCollection(
        uriTemplate: '/overlays',
        status: 200,
        schemes: ['https'],
        openapiContext: ['summary' => 'Récupérer les données de tous les overlays'],
        normalizationContext: ['groups' => ['overlay:read']],
        security: 'is_granted("ROLE_ADMIN")',
        securityMessage: 'Seulement les administrateurs peuvent accéder à cette ressource.',
    ),
    new Post(
        uriTemplate: '/overlays/add',
        status: 201,
        schemes: ['https'],
        openapiContext: ['summary' => 'Ajouter un overlay'],
        normalizationContext: ['groups' => ['overlay:read']],
        denormalizationContext: ['groups' => ['overlay:write']],
    ),
    new Put(
        uriTemplate: '/overlays/{id}',
        requirements: ['id' => '\d+'],
        status: 200,
        schemes: ['https'],
        openapiContext: ['summary' => 'Modifier un overlay'],
        normalizationContext: ['groups' => ['overlay:read']],
        denormalizationContext: ['groups' => ['overlay:write']],
        security: 'is_granted("ROLE_ADMIN") or object.getUserOwner() == user or is_granted("OVERLAY_EDIT", object)',
        securityMessage: 'Vous n\'avez pas accès à cet overlay',
    ),
    new Delete(
        uriTemplate: '/overlays/{id}',
        requirements: ['id' => '\d+'],
        status: 204,
        schemes: ['https'],
        openapiContext: ['summary' => 'Supprimer un overlay'],
        security: 'is_granted("ROLE_ADMIN") or object.getUserOwner() == user',
        securityMessage: 'Vous n\'avez pas accès à cet overlay',
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

    #[ORM\Column(type: Types::GUID, unique: true)]
    #[Groups(['overlay:read','overlay:write'])]
    #[ApiProperty(security: 'is_granted("ROLE_ADMIN")')]
    private ?string $uuid = null;

    #[ORM\Column(length: 255)]
    #[Groups(['overlay:read','overlay:write'])]
    private ?string $name = null;

    #[ORM\OneToOne(inversedBy: 'overlay', cascade: ['persist', 'remove'])]
    #[Groups(['overlay:read','overlay:write'])]
    private ?Model $model = null;

    #[ORM\ManyToOne(inversedBy: 'overlays')]
    #[Groups(['overlay:read','overlay:write'])]
    #[ApiProperty(securityPostDenormalize: 'is_granted("UPDATE","ROLE_ADMIN")')]
    private ?User $userOwner = null;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'overlaysAccess')]
    #[Groups(['overlay:read','overlay:write'])]
    private Collection $userAccess;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['overlay:read','overlay:write'])]
    private ?string $image = null;

    public function __construct()
    {
        $this->userAccess = new ArrayCollection();
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getModel(): ?Model
    {
        return $this->model;
    }

    public function setModel(?Model $model): self
    {
        $this->model = $model;

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

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }
}
