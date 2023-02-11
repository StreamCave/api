<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\InfoGroupController;
use App\Repository\InfoGroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: InfoGroupRepository::class)]
#[ApiResource(operations: [
    new Get(
        uriTemplate: '/info-groups/{uuid}',
        uriVariables: [
            "uuid" => new Link(
                fromClass: InfoGroup::class,
            )
        ],
        status: 200,
        schemes: ['https'],
        controller: InfoGroupController::class,
        openapiContext: ['summary' => 'Récupérer les données d\'un groupe d\'informations'],
        normalizationContext: ['groups' => ['info_group:read']],
        security: 'is_granted("ROLE_ADMIN") or object.getWidgets().getModel().getOverlay().getUserOwner() == user',
        securityMessage: 'Vous n\'avez pas accès à ce groupe d\'informations',
    ),
    new GetCollection(
        uriTemplate: '/info-groups',
        status: 200,
        schemes: ['https'],
        openapiContext: ['summary' => 'Récupérer les données de tous les groupes d\'informations'],
        normalizationContext: ['groups' => ['info_group:read']],
        security: 'is_granted("ROLE_ADMIN")',
        securityMessage: 'Seulement les administrateurs peuvent accéder à cette ressource.',
    ),
    new Post(
        uriTemplate: '/info-groups/add',
        status: 201,
        schemes: ['https'],
        openapiContext: ['summary' => 'Ajouter un groupe d\'informations'],
        normalizationContext: ['groups' => ['info_group:read']],
        denormalizationContext: ['groups' => ['info_group:write']],
    ),
    new Put(
        uriTemplate: '/info-groups/{id}',
        requirements: ['id' => '\d+'],
        status: 200,
        schemes: ['https'],
        openapiContext: ['summary' => 'Modifier un groupe d\'informations'],
        normalizationContext: ['groups' => ['info_group:read']],
        denormalizationContext: ['groups' => ['info_group:write']],
        security: 'is_granted("ROLE_ADMIN") or object.getWidgets().getModel().getOverlay().getUserOwner() == user',
        securityMessage: 'Vous n\'avez pas accès à ce groupe d\'informations',
    ),
    new Delete(
        uriTemplate: '/info-groups/{id}',
        requirements: ['id' => '\d+'],
        status: 204,
        schemes: ['https'],
        openapiContext: ['summary' => 'Supprimer un groupe d\'informations'],
        security: 'is_granted("ROLE_ADMIN") or object.getWidgets().getModel().getOverlay().getUserOwner() == user',
        securityMessage: 'Vous n\'avez pas accès à ce groupe d\'informations',
    )
], schemes: ['https'], normalizationContext: ['groups' => ['info_group:read']], denormalizationContext: ['groups' => ['info_group:write']], openapiContext: ['summary' => 'InfoGroup'])]
class InfoGroup
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['info_group:read','widget:read'])]
    #[ApiProperty(security: 'is_granted("ROLE_ADMIN")')]
    private ?int $id = null;

    #[ORM\Column(type: Types::GUID, unique: true)]
    #[Groups(['info_group:read','info_group:write','widget:read','model:read','overlay:read', 'overlay:write'])]
    #[ApiProperty(security: 'is_granted("ROLE_ADMIN")')]
    private ?string $uuid;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['info_group:read','info_group:write','widget:read','model:read','overlay:read', 'overlay:write'])]
    private ?string $titre = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['info_group:read','info_group:write','widget:read','model:read','overlay:read', 'overlay:write'])]
    private ?string $description = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['info_group:read','info_group:write','widget:read','model:read','overlay:read', 'overlay:write'])]
    private ?string $logo = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['info_group:read','info_group:write','widget:read','model:read','overlay:read', 'overlay:write'])]
    private array $textScroll = [];

    #[ORM\OneToMany(mappedBy: 'infoGroup', targetEntity: Widget::class)]
    #[Groups(['info_group:read','info_group:write'])]
    #[ApiProperty(securityPostDenormalize: 'is_granted("ROLE_ADMIN")')]
    private Collection $widgets;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeInterface $createdDate;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $modifiedDate;

    public function __construct()
    {
        $this->widgets = new ArrayCollection();
        $this->createdDate = new \DateTimeImmutable();
        $this->modifiedDate = new \DateTime();
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

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(?string $titre): self
    {
        $this->titre = $titre;

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

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(?string $logo): self
    {
        $this->logo = $logo;

        return $this;
    }

    public function getTextScroll(): array
    {
        return $this->textScroll;
    }

    public function setTextScroll(?array $textScroll): self
    {
        $this->textScroll = $textScroll;

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
            $widget->setInfoGroup($this);
        }

        return $this;
    }

    public function removeWidget(Widget $widget): self
    {
        if ($this->widgets->removeElement($widget)) {
            // set the owning side to null (unless already changed)
            if ($widget->getInfoGroup() === $this) {
                $widget->setInfoGroup(null);
            }
        }

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
