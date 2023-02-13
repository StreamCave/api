<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\LibMapRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: LibMapRepository::class)]
#[ApiResource(operations: [
    new Get(
        uriTemplate: '/libmaps/{uuid}',
        uriVariables: "uuid",
        status: 200,
        schemes: ['https'],
        openapiContext: ['summary' => 'Récupérer les données d\'un LibMap'],
        normalizationContext: ['groups' => ['libmaps:read']],
    ),
    new GetCollection(
        uriTemplate: '/libmaps',
        status: 200,
        schemes: ['https'],
        openapiContext: ['summary' => 'Récupérer les données de tous les LibMap'],
        normalizationContext: ['groups' => ['libmaps:read']],
    ),
    new Post(
        uriTemplate: '/libmaps/add',
        status: 201,
        schemes: ['https'],
        openapiContext: ['summary' => 'Ajouter un LibMap'],
        normalizationContext: ['groups' => ['libmaps:read']],
        denormalizationContext: ['groups' => ['libmaps:write']],
    ),
    new Put(
        uriTemplate: '/libmaps/{uuid}',
        uriVariables: "uuid",
        status: 200,
        schemes: ['https'],
        openapiContext: ['summary' => 'Modifier un LibMap'],
        normalizationContext: ['groups' => ['libmaps:read']],
        denormalizationContext: ['groups' => ['libmaps:write']],
    ),
    new Delete(
        uriTemplate: '/libmaps/{uuid}',
        uriVariables: "uuid",
        status: 204,
        schemes: ['https'],
        openapiContext: ['summary' => 'Supprimer un LibMap'],
    )
], schemes: ['https'], normalizationContext: ['groups' => ['libmaps:read']], denormalizationContext: ['groups' => ['libmaps:write']], openapiContext: ['summary' => 'LibMap'])]
class LibMap
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['libmaps:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Groups(['libmaps:read', 'libmaps:write', 'map_group:read', 'map_group:write'])]
    private ?string $uuid;

    #[ORM\Column(length: 255)]
    #[Groups(['libmaps:read', 'libmaps:write', 'map_group:read', 'map_group:write'])]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['libmaps:read', 'libmaps:write', 'map_group:read', 'map_group:write'])]
    private ?string $image = null;

    #[ORM\OneToMany(mappedBy: 'libMap', targetEntity: MapGroup::class)]
    #[Groups(['libmaps:read', 'map_group:read'])]
    private Collection $mapGroups;

    public function __construct()
    {
        $this->mapGroups = new ArrayCollection();
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

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @return Collection<int, MapGroup>
     */
    public function getMapGroups(): Collection
    {
        return $this->mapGroups;
    }

    public function addMapGroup(MapGroup $mapGroup): self
    {
        if (!$this->mapGroups->contains($mapGroup)) {
            $this->mapGroups->add($mapGroup);
            $mapGroup->setLibMap($this);
        }

        return $this;
    }

    public function removeMapGroup(MapGroup $mapGroup): self
    {
        if ($this->mapGroups->removeElement($mapGroup)) {
            // set the owning side to null (unless already changed)
            if ($mapGroup->getLibMap() === $this) {
                $mapGroup->setLibMap(null);
            }
        }

        return $this;
    }
}
