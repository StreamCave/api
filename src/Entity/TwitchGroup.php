<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\DeleteTwitchGroup;
use App\Repository\TwitchGroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: TwitchGroupRepository::class)]
#[ApiResource(operations: [
    new Get(
        uriTemplate: '/twitch-groups/{uuid}',
        uriVariables: "uuid",
        status: 200,
        schemes: ['https'],
        openapiContext: ['summary' => 'Récupérer les données d\'un groupe de Twitch'],
        normalizationContext: ['groups' => ['twitch_group:read']],
        security: 'is_granted("ROLE_ADMIN")',
        securityMessage: 'Seulement les administrateurs peuvent accéder à cette ressource.',
    ),
    new GetCollection(
        uriTemplate: '/twitch-groups',
        status: 200,
        schemes: ['https'],
        openapiContext: ['summary' => 'Récupérer les données de tous les groupes de Twitch'],
        normalizationContext: ['groups' => ['twitch_group:read']],
        security: 'is_granted("ROLE_ADMIN")',
        securityMessage: 'Seulement les administrateurs peuvent accéder à cette ressource.',
    ),
    new Post(
        uriTemplate: '/twitch-groups/add',
        status: 201,
        schemes: ['https'],
        openapiContext: ['summary' => 'Ajouter un groupe de Twitch'],
        normalizationContext: ['groups' => ['twitch_group:read']],
        denormalizationContext: ['groups' => ['twitch_group:write']],
    ),
    new Put(
        uriTemplate: '/twitch-groups/{uuid}',
        uriVariables: "uuid",
        status: 200,
        schemes: ['https'],
        openapiContext: ['summary' => 'Modifier un groupe de Twitch'],
        normalizationContext: ['groups' => ['twitch_group:read']],
        denormalizationContext: ['groups' => ['twitch_group:write']],
        security: 'is_granted("ROLE_ADMIN") or object.getWidgets().getModel().getOverlay().getUserOwner() == user',
        securityMessage: 'Vous n\'avez pas accès à ce groupe de Twitch',
    ),
    new Delete(
        uriTemplate: '/twitch-groups/{uuid}',
        uriVariables: "uuid",
        status: 204,
        schemes: ['https'],
        controller: DeleteTwitchGroup::class,
        openapiContext: ['summary' => 'Supprimer un groupe de Twitch'],
        security: 'is_granted("ROLE_ADMIN") or object.getWidgets().getModel().getOverlay().getUserOwner() == user',
        securityMessage: 'Vous n\'avez pas accès à ce groupe de Twitch',
    )
], schemes: ['https'], normalizationContext: ['groups' => ['twitch_group:read']], denormalizationContext: ['groups' => ['twitch_group:write']], openapiContext: ['summary' => 'TwitchGroup'])]
class TwitchGroup
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['twitch_group:read','widget:read','model:read','overlay:read', 'overlay:write'])]
    #[ApiProperty(security: 'is_granted("ROLE_ADMIN")')]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Groups(['twitch_group:read', 'twitch_group:write','widget:read','model:read','overlay:read', 'overlay:write'])]
    #[ApiProperty(security: 'is_granted("ROLE_ADMIN")')]
    private ?string $uuid;

    #[ORM\OneToMany(mappedBy: 'twitchGroup', targetEntity: Widget::class)]
    private Collection $widgets;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['twitch_group:read', 'twitch_group:write','widget:read','model:read','overlay:read', 'overlay:write'])]
    private ?string $twitchId = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['twitch_group:read', 'twitch_group:write','widget:read','model:read','overlay:read', 'overlay:write'])]
    private ?bool $visible = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['twitch_group:read', 'twitch_group:write','widget:read','model:read','overlay:read', 'overlay:write'])]
    private ?string $overlayId = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['twitch_group:read', 'twitch_group:write','widget:read','model:read','overlay:read', 'overlay:write'])]
    private ?string $type = null;

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
            $widget->setTwitchGroup($this);
        }

        return $this;
    }

    public function removeWidget(Widget $widget): self
    {
        if ($this->widgets->removeElement($widget)) {
            // set the owning side to null (unless already changed)
            if ($widget->getTwitchGroup() === $this) {
                $widget->setTwitchGroup(null);
            }
        }

        return $this;
    }

    public function getTwitchId(): ?string
    {
        return $this->twitchId;
    }

    public function setTwitchId(string $twitchId): self
    {
        $this->twitchId = $twitchId;

        return $this;
    }

    public function isVisible(): ?bool
    {
        return $this->visible;
    }

    public function setVisible(?bool $visible): self
    {
        $this->visible = $visible;

        return $this;
    }

    public function getOverlayId(): ?string
    {
        return $this->overlayId;
    }

    public function setOverlayId(?string $overlayId): self
    {
        $this->overlayId = $overlayId;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }
}
