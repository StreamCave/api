<?php

namespace App\Entity;

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

#[ORM\Entity(repositoryClass: OverlayRepository::class)]
#[ApiResource(operations: [
    new Get(
        uriTemplate: '/overlays/{id}',
        requirements: ['id' => '\d+'],
        status: 200,
        schemes: ['https'],
        openapiContext: ['summary' => 'Récupérer les données d\'un overlay'],
        normalizationContext: ['groups' => ['overlay:read']],
    ),
    new GetCollection(
        uriTemplate: '/overlays',
        status: 200,
        schemes: ['https'],
        openapiContext: ['summary' => 'Récupérer les données de tous les overlays'],
        normalizationContext: ['groups' => ['overlay:read']],
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
    ),
    new Delete(
        uriTemplate: '/overlays/{id}',
        requirements: ['id' => '\d+'],
        status: 204,
        schemes: ['https'],
        openapiContext: ['summary' => 'Supprimer un overlay'],
    )
], schemes: ['https'], normalizationContext: ['groups' => ['overlay:read']], denormalizationContext: ['groups' => ['overlay:write']], openapiContext: ['summary' => 'Overlay'])]
class Overlay
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['overlay:read','model:read'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::GUID, unique: true)]
    #[Groups(['overlay:read','overlay:write','model:read'])]
    private ?string $uuid = null;

    #[ORM\Column(length: 255)]
    #[Groups(['overlay:read','overlay:write','model:read'])]
    private ?string $name = null;

    #[ORM\OneToOne(inversedBy: 'overlay', cascade: ['persist', 'remove'])]
    #[Groups(['overlay:read'])]
    private ?Model $model = null;

    #[ORM\ManyToOne(inversedBy: 'overlays')]
    #[Groups(['overlay:read','overlay:write'])]
    private ?User $userOwner = null;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'overlaysAccess')]
    #[Groups(['overlay:read','overlay:write'])]
    private Collection $userAccess;

    public function __construct()
    {
        $this->userAccess = new ArrayCollection();
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
}
