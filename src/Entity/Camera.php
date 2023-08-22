<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\DeleteCameraGroup;
use App\Controller\EditCameraVisibleByTeam;
use App\Repository\CameraRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CameraRepository::class)]
#[ApiResource(operations: [
    new Get(
        uriTemplate: '/cameras/{uuid}',
        uriVariables: "uuid",
        status: 200,
        schemes: ['https'],
        openapiContext: ['summary' => 'Récupérer les données d\'une camera'],
        normalizationContext: ['groups' => ['camera:read']],
    ),
    new GetCollection(
        uriTemplate: '/cameras',
        status: 200,
        schemes: ['https'],
        openapiContext: ['summary' => 'Récupérer les données de toutes les camera'],
        normalizationContext: ['groups' => ['camera:read']],
    ),
    new Post(
        uriTemplate: '/cameras/add',
        status: 201,
        schemes: ['https'],
        openapiContext: ['summary' => 'Ajouter une camera'],
        normalizationContext: ['groups' => ['camera:read']],
        denormalizationContext: ['groups' => ['camera:write']],
    ),
    new Put(
        uriTemplate: '/cameras/{uuid}',
        uriVariables: "uuid",
        status: 200,
        schemes: ['https'],
        openapiContext: ['summary' => 'Modifier une camera'],
        normalizationContext: ['groups' => ['camera:read']],
        denormalizationContext: ['groups' => ['camera:write']],
    ),
    new Put(
        uriTemplate: "/cameras/team/{team}/overlay/{overlayId}",
        defaults: ['_api_receive' => false],
        status: 200,
        schemes: ['https'],
        openapiContext: ['summary' => 'Modifier la visibilité d\'une camera par équipe'],
        normalizationContext: ['groups' => ['camera:read']],
        denormalizationContext: ['groups' => ['camera:write']],
    ),
    new Delete(
        uriTemplate: '/cameras/{uuid}',
        uriVariables: "uuid",
        status: 204,
        schemes: ['https'],
        openapiContext: ['summary' => 'Supprimer une camera'],
    )
], schemes: ['https'], normalizationContext: ['groups' => ['camera:read']], denormalizationContext: ['groups' => ['camera:write']], openapiContext: ['summary' => 'Camera'])]
class Camera
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['camera:read','camera_group:read','widget:read','model:read','overlay:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['camera:read','camera:write','camera_group:read', 'camera_group:write','widget:read','model:read','overlay:read', 'overlay:write'])]
    private ?string $uuid = null;

    #[ORM\Column]
    #[Groups(['camera:read','camera:write','camera_group:read', 'camera_group:write','widget:read','model:read','overlay:read', 'overlay:write'])]
    private ?bool $visible = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['camera:read','camera:write','camera_group:read', 'camera_group:write','widget:read','model:read','overlay:read', 'overlay:write'])]
    private ?string $socketId = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['camera:read','camera:write','camera_group:read', 'camera_group:write','widget:read','model:read','overlay:read', 'overlay:write'])]
    private ?string $team = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['camera:read','camera:write','camera_group:read', 'camera_group:write','widget:read','model:read','overlay:read', 'overlay:write'])]
    private ?string $metadata = null;

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    #[Groups(['camera:read','camera:write','camera_group:read', 'camera_group:write','widget:read','model:read','overlay:read', 'overlay:write'])]
    private array $styles = [];

    #[ORM\ManyToMany(targetEntity: CameraGroup::class, mappedBy: 'cameras', cascade: ['persist'])]
    #[Groups(['camera:read','camera:write','camera_group:read', 'camera_group:write','widget:read','model:read','overlay:read', 'overlay:write'])]
    private Collection $cameraGroups;

    public function __construct()
    {
        $this->cameraGroups = new ArrayCollection();
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

    public function isVisible(): ?bool
    {
        return $this->visible;
    }

    public function setVisible(bool $visible): self
    {
        $this->visible = $visible;

        return $this;
    }

    public function getSocketId(): ?string
    {
        return $this->socketId;
    }

    public function setSocketId(?string $socketId): self
    {
        $this->socketId = $socketId;

        return $this;
    }

    public function getTeam(): ?string
    {
        return $this->team;
    }

    public function setTeam(?string $team): self
    {
        $this->team = $team;

        return $this;
    }

    public function getMetadata(): ?string
    {
        return $this->metadata;
    }

    public function setMetadata(?string $metadata): self
    {
        $this->metadata = $metadata;

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
     * @return Collection<int, CameraGroup>
     */
    public function getCameraGroups(): Collection
    {
        return $this->cameraGroups;
    }

    public function addCameraGroup(CameraGroup $cameraGroup): self
    {
        if (!$this->cameraGroups->contains($cameraGroup)) {
            $this->cameraGroups->add($cameraGroup);
            $cameraGroup->addCamera($this);
        }

        return $this;
    }

    public function removeCameraGroup(CameraGroup $cameraGroup): self
    {
        if ($this->cameraGroups->removeElement($cameraGroup)) {
            $cameraGroup->removeCamera($this);
        }

        return $this;
    }
}
