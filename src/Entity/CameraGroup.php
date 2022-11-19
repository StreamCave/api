<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\CameraGroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CameraGroupRepository::class)]
#[ApiResource(operations: [
    new Get(
        uriTemplate: '/camera-groups/{id}',
        requirements: ['id' => '\d+'],
        status: 200,
        schemes: ['https'],
        openapiContext: ['summary' => 'Récupérer les données d\'un groupe de caméras'],
        normalizationContext: ['groups' => ['camera_group:read']],
    ),
    new GetCollection(
        uriTemplate: '/camera-groups',
        status: 200,
        schemes: ['https'],
        openapiContext: ['summary' => 'Récupérer les données de tous les groupes de caméras'],
        normalizationContext: ['groups' => ['camera_group:read']],
    ),
    new Post(
        uriTemplate: '/camera-groups/add',
        status: 201,
        schemes: ['https'],
        openapiContext: ['summary' => 'Ajouter un groupe de caméras'],
        normalizationContext: ['groups' => ['camera_group:read']],
        denormalizationContext: ['groups' => ['camera_group:write']],
    ),
    new Put(
        uriTemplate: '/camera-groups/{id}',
        requirements: ['id' => '\d+'],
        status: 200,
        schemes: ['https'],
        openapiContext: ['summary' => 'Modifier un groupe de caméras'],
        normalizationContext: ['groups' => ['camera_group:read']],
        denormalizationContext: ['groups' => ['camera_group:write']],
    ),
    new Delete(
        uriTemplate: '/camera-groups/{id}',
        requirements: ['id' => '\d+'],
        status: 204,
        schemes: ['https'],
        openapiContext: ['summary' => 'Supprimer un groupe de caméras'],
    )
], schemes: ['https'], normalizationContext: ['groups' => ['camera_group:read']], denormalizationContext: ['groups' => ['camera_group:write']], openapiContext: ['summary' => 'CameraGroup']
)]
class CameraGroup
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['camera_group:read'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::GUID, unique: true)]
    #[Groups(['camera_group:read'])]
    private ?string $uuid = null;

    #[ORM\Column(length: 255)]
    #[Groups(['camera_group:read', 'camera_group:write'])]
    private ?string $idNinja = null;

    #[ORM\Column(length: 255)]
    #[Groups(['camera_group:read', 'camera_group:write'])]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['camera_group:read', 'camera_group:write'])]
    private ?string $uplayTag = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['camera_group:read', 'camera_group:write'])]
    private ?int $positionTop = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['camera_group:read', 'camera_group:write'])]
    private ?int $positionBottom = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['camera_group:read', 'camera_group:write'])]
    private ?int $positionLeft = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['camera_group:read', 'camera_group:write'])]
    private ?int $positionRight = null;

    #[ORM\OneToMany(mappedBy: 'cameraGroup', targetEntity: Widget::class)]
    #[Groups(['camera_group:read'])]
    private Collection $widgets;

    public function __construct()
    {
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

    public function getIdNinja(): ?string
    {
        return $this->idNinja;
    }

    public function setIdNinja(string $idNinja): self
    {
        $this->idNinja = $idNinja;

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

    public function getUplayTag(): ?string
    {
        return $this->uplayTag;
    }

    public function setUplayTag(?string $uplayTag): self
    {
        $this->uplayTag = $uplayTag;

        return $this;
    }

    public function getPositionTop(): ?int
    {
        return $this->positionTop;
    }

    public function setPositionTop(?int $positionTop): self
    {
        $this->positionTop = $positionTop;

        return $this;
    }

    public function getPositionBottom(): ?int
    {
        return $this->positionBottom;
    }

    public function setPositionBottom(?int $positionBottom): self
    {
        $this->positionBottom = $positionBottom;

        return $this;
    }

    public function getPositionLeft(): ?int
    {
        return $this->positionLeft;
    }

    public function setPositionLeft(?int $positionLeft): self
    {
        $this->positionLeft = $positionLeft;

        return $this;
    }

    public function getPositionRight(): ?int
    {
        return $this->positionRight;
    }

    public function setPositionRight(?int $positionRight): self
    {
        $this->positionRight = $positionRight;

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
            $widget->setCameraGroup($this);
        }

        return $this;
    }

    public function removeWidget(Widget $widget): self
    {
        if ($this->widgets->removeElement($widget)) {
            // set the owning side to null (unless already changed)
            if ($widget->getCameraGroup() === $this) {
                $widget->setCameraGroup(null);
            }
        }

        return $this;
    }
}
