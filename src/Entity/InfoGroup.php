<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\InfoGroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: InfoGroupRepository::class)]
#[ApiResource(operations: [
    new Get(
        uriTemplate: '/info-groups/{id}',
        requirements: ['id' => '\d+'],
        status: 200,
        schemes: ['https'],
        openapiContext: ['summary' => 'Récupérer les données d\'un groupe d\'informations'],
        normalizationContext: ['groups' => ['info_group:read']],
    ),
    new GetCollection(
        uriTemplate: '/info-groups',
        status: 200,
        schemes: ['https'],
        openapiContext: ['summary' => 'Récupérer les données de tous les groupes d\'informations'],
        normalizationContext: ['groups' => ['info_group:read']],
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
    ),
    new Delete(
        uriTemplate: '/info-groups/{id}',
        requirements: ['id' => '\d+'],
        status: 204,
        schemes: ['https'],
        openapiContext: ['summary' => 'Supprimer un groupe d\'informations'],
    )
], schemes: ['https'], normalizationContext: ['groups' => ['info_group:read']], denormalizationContext: ['groups' => ['info_group:write']], openapiContext: ['summary' => 'InfoGroup'])]
class InfoGroup
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['info_group:read'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::GUID, unique: true)]
    #[Groups(['info_group:read','info_group:write'])]
    private ?string $uuid = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['info_group:read','info_group:write'])]
    private ?string $titre = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['info_group:read','info_group:write'])]
    private ?string $description = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['info_group:read','info_group:write'])]
    private ?string $logo = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['info_group:read','info_group:write'])]
    private array $textScroll = [];

    #[ORM\OneToMany(mappedBy: 'infoGroup', targetEntity: Widget::class)]
    #[Groups(['info_group:read','info_group:write'])]
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
}
