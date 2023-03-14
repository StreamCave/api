<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\DeleteCameraGroup;
use App\Repository\CameraGroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: CameraGroupRepository::class)]
#[ApiResource(operations: [
    new Get(
        uriTemplate: '/camera-groups/{uuid}',
        uriVariables: "uuid",
        status: 200,
        schemes: ['https'],
        openapiContext: ['summary' => 'Récupérer les données d\'un groupe de camera'],
        normalizationContext: ['groups' => ['camera_group:read']],
        security: 'is_granted("ROLE_ADMIN") or object.getWidgets().getModel().getOverlay().getUserOwner() == user',
        securityMessage: 'Vous n\'avez pas accès à ce groupe de camera',
    ),
    new GetCollection(
        uriTemplate: '/camera-groups',
        status: 200,
        schemes: ['https'],
        openapiContext: ['summary' => 'Récupérer les données de tous les groupes de camera'],
        normalizationContext: ['groups' => ['camera_group:read']],
        security: 'is_granted("ROLE_ADMIN")',
        securityMessage: 'Seulement les administrateurs peuvent accéder à cette ressource.',
    ),
    new Post(
        uriTemplate: '/camera-groups/add',
        status: 201,
        schemes: ['https'],
        openapiContext: ['summary' => 'Ajouter un groupe de matchs'],
        normalizationContext: ['groups' => ['camera_group:read']],
        denormalizationContext: ['groups' => ['camera_group:write']],
    ),
    new Put(
        uriTemplate: '/camera-groups/{uuid}',
        uriVariables: "uuid",
        status: 200,
        schemes: ['https'],
        openapiContext: ['summary' => 'Modifier un groupe de camera'],
        normalizationContext: ['groups' => ['camera_group:read']],
        denormalizationContext: ['groups' => ['camera_group:write']],
        security: 'is_granted("ROLE_ADMIN") or object.getWidgets().getModel().getOverlay().getUserOwner() == user',
        securityMessage: 'Vous n\'avez pas accès à ce groupe de camera',
    ),
    new Delete(
        uriTemplate: '/camera-groups/{uuid}',
        uriVariables: "uuid",
        status: 204,
        schemes: ['https'],
        controller: DeleteCameraGroup::class,
        openapiContext: ['summary' => 'Supprimer un groupe de camera'],
        security: 'is_granted("ROLE_ADMIN") or object.getWidgets().getModel().getOverlay().getUserOwner() == user',
        securityMessage: 'Vous n\'avez pas accès à ce groupe de camera',
    )
], schemes: ['https'], normalizationContext: ['groups' => ['camera_group:read']], denormalizationContext: ['groups' => ['camera_group:write']], openapiContext: ['summary' => 'CameraGroup'])]
class CameraGroup
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['camera_group:read','widget:read','model:read','overlay:read', 'overlay:write'])]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Groups(['camera_group:read', 'camera_group:write','widget:read','model:read','overlay:read', 'overlay:write'])]
    private ?string $uuid;

    #[ORM\Column]
    #[Groups(['camera_group:read', 'camera_group:write','widget:read','model:read','overlay:read', 'overlay:write'])]
    private ?bool $visible = null;

    #[ORM\ManyToMany(targetEntity: Widget::class, mappedBy: 'cameraGroup')]
    #[Groups(['camera_group:read', 'camera_group:write','widget:read','model:read','overlay:read', 'overlay:write'])]
    private Collection $widgets;

    #[ORM\Column(length: 255)]
    #[Groups(['camera_group:read', 'camera_group:write','widget:read','model:read','overlay:read', 'overlay:write'])]
    private ?string $camId = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['camera_group:read', 'camera_group:write','widget:read','model:read','overlay:read', 'overlay:write'])]
    private ?string $roomName = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['camera_group:read', 'camera_group:write','widget:read','model:read','overlay:read', 'overlay:write'])]
    private ?string $roomPassword = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['camera_group:read', 'camera_group:write','widget:read','model:read','overlay:read', 'overlay:write'])]
    private ?string $name = null;

    public function __construct()
    {
        $this->widgets = new ArrayCollection();
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

    public function isVisible(): ?bool
    {
        return $this->visible;
    }

    public function setVisible(bool $visible): self
    {
        $this->visible = $visible;

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
            $widget->addCameraGroup($this);
        }

        return $this;
    }

    public function removeWidget(Widget $widget): self
    {
        if ($this->widgets->removeElement($widget)) {
            $widget->removeCameraGroup($this);
        }

        return $this;
    }

    public function getCamId(): ?string
    {
        return $this->camId;
    }

    public function setCamId(string $camId): self
    {
        $this->camId = $camId;

        return $this;
    }

    public function getRoomName(): ?string
    {
        return $this->roomName;
    }

    public function setRoomName(?string $roomName): self
    {
        $this->roomName = $roomName;

        return $this;
    }

    public function getRoomPassword(): ?string
    {
        return $this->roomPassword;
    }

    public function setRoomPassword(?string $roomPassword): self
    {
        $this->roomPassword = $roomPassword;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
