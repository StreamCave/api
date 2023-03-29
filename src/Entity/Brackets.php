<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\DeleteCameraGroup;
use App\Repository\BracketsRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: BracketsRepository::class)]
#[ApiResource(operations: [
    new Get(
        uriTemplate: '/brackets/{uuid}',
        uriVariables: "uuid",
        status: 200,
        schemes: ['https'],
        openapiContext: ['summary' => 'Récupérer les données d\'un bracket'],
        normalizationContext: ['groups' => ['bracket:read']],
    ),
    new GetCollection(
        uriTemplate: '/bracket',
        status: 200,
        schemes: ['https'],
        openapiContext: ['summary' => 'Récupérer les données de tous les bracket'],
        normalizationContext: ['groups' => ['bracket:read']],
    ),
    new Post(
        uriTemplate: '/brackets/add',
        status: 201,
        schemes: ['https'],
        openapiContext: ['summary' => 'Ajouter un bracket'],
        normalizationContext: ['groups' => ['bracket:read']],
        denormalizationContext: ['groups' => ['bracket:write']],
    ),
    new Put(
        uriTemplate: '/brackets/{uuid}',
        uriVariables: "uuid",
        status: 200,
        schemes: ['https'],
        openapiContext: ['summary' => 'Modifier un bracket'],
        normalizationContext: ['groups' => ['bracket:read']],
        denormalizationContext: ['groups' => ['bracket:write']],
    ),
    new Delete(
        uriTemplate: '/brackets/{uuid}',
        uriVariables: "uuid",
        status: 204,
        schemes: ['https'],
        controller: DeleteCameraGroup::class,
        openapiContext: ['summary' => 'Supprimer un bracket'],
        security: 'is_granted("ROLE_ADMIN")',
        securityMessage: 'Vous n\'avez pas accès à ce bracket',
    )
], schemes: ['https'], normalizationContext: ['groups' => ['bracket:read']], denormalizationContext: ['groups' => ['bracket:write']], openapiContext: ['summary' => 'CameraGroup'])]
class Brackets
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['bracket:read','widget:read','model:read','overlay:read', 'overlay:write'])]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Groups(['bracket:read', 'bracket:write','widget:read','model:read','overlay:read', 'overlay:write'])]
    private ?string $uuid;

    #[ORM\Column(length: 255)]
    #[Groups(['bracket:read', 'bracket:write','widget:read','model:read','overlay:read', 'overlay:write'])]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Groups(['bracket:read', 'bracket:write','widget:read','model:read','overlay:read', 'overlay:write'])]
    private ?string $game = null;

    #[ORM\Column]
    #[Groups(['bracket:read', 'bracket:write','widget:read','model:read','overlay:read', 'overlay:write'])]
    private array $bracket = [];

    #[ORM\Column]
    #[Groups(['bracket:read', 'bracket:write','widget:read','model:read','overlay:read', 'overlay:write'])]
    private ?bool $visible = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['bracket:read', 'bracket:write','widget:read','model:read','overlay:read', 'overlay:write'])]
    private ?string $overlayId = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['bracket:read', 'bracket:write','widget:read','model:read','overlay:read', 'overlay:write'])]
    private ?string $type = null;

    public function __construct()
    {
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

    public function getGame(): ?string
    {
        return $this->game;
    }

    public function setGame(string $game): self
    {
        $this->game = $game;

        return $this;
    }

    public function getBracket(): array
    {
        return $this->bracket;
    }

    public function setBracket(array $bracket): self
    {
        $this->bracket = $bracket;

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
