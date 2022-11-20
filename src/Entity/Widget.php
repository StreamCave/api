<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\WidgetRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: WidgetRepository::class)]
#[ApiResource(operations: [
    new Get(
        uriTemplate: '/widgets/{id}',
        requirements: ['id' => '\d+'],
        status: 200,
        schemes: ['https'],
        openapiContext: ['summary' => 'Récupérer les données d\'un widget'],
        normalizationContext: ['groups' => ['widget:read']],
        security: 'is_granted("ROLE_ADMIN") or object.getModel().getOverlay().getUserOwner() == user or object.getModel().getOverlay().getUserAccess() == user',
        securityMessage: 'Vous n\'avez pas accès à ce widget',
    ),
    new GetCollection(
        uriTemplate: '/widgets',
        status: 200,
        schemes: ['https'],
        openapiContext: ['summary' => 'Récupérer les données de tous les widgets'],
        normalizationContext: ['groups' => ['widget:read']],
        security: 'is_granted("ROLE_ADMIN")',
        securityMessage: 'Seulement les administrateurs peuvent accéder à cette ressource.',
    ),
    new Post(
        uriTemplate: '/widgets/add',
        status: 201,
        schemes: ['https'],
        openapiContext: ['summary' => 'Ajouter un widget'],
        normalizationContext: ['groups' => ['widget:read']],
        denormalizationContext: ['groups' => ['widget:write']],
    ),
    new Put(
        uriTemplate: '/widgets/{id}',
        requirements: ['id' => '\d+'],
        status: 200,
        schemes: ['https'],
        openapiContext: ['summary' => 'Modifier un widget'],
        normalizationContext: ['groups' => ['widget:read']],
        denormalizationContext: ['groups' => ['widget:write']],
        security: 'is_granted("ROLE_ADMIN") or object.getModel().getOverlay().getUserOwner() == user or object.getModel().getOverlay().getUserAccess() == user',
        securityMessage: 'Vous n\'avez pas accès à ce widget',
    ),
    new Delete(
        uriTemplate: '/widgets/{id}',
        requirements: ['id' => '\d+'],
        status: 204,
        schemes: ['https'],
        openapiContext: ['summary' => 'Supprimer un widget'],
        security: 'is_granted("ROLE_ADMIN") or object.getModel().getOverlay().getUserOwner() == user or object.getModel().getOverlay().getUserAccess() == user',
        securityMessage: 'Vous n\'avez pas accès à ce widget',
    )
], schemes: ['https'], normalizationContext: ['groups' => ['widget:read']], denormalizationContext: ['groups' => ['widget:write']], openapiContext: ['summary' => 'Widget'])]
class Widget
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['widget:read','camera_group:read','info_group:read','match_group:read','poll_group:read','popup_group:read','tweet_group:read','model:read'])]
    #[ApiProperty(security: 'is_granted("ROLE_ADMIN")')]
    private ?int $id = null;

    #[ORM\Column(type: Types::GUID, unique: true)]
    #[Groups(['widget:read','widget:write','camera_group:read','info_group:read','match_group:read','poll_group:read','popup_group:read','tweet_group:read','model:read'])]
    #[ApiProperty(security: 'is_granted("ROLE_ADMIN")')]
    private ?string $uuid = null;

    #[ORM\Column(length: 255)]
    #[Groups(['widget:read','widget:write','camera_group:read','info_group:read','match_group:read','poll_group:read','popup_group:read','tweet_group:read','model:read'])]
    #[ApiProperty(security: ['POST' => 'is_granted("ROLE_ADMIN")', 'PUT' => 'is_granted("ROLE_ADMIN")'])]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['widget:read','widget:write','camera_group:read','info_group:read','match_group:read','poll_group:read','popup_group:read','tweet_group:read','model:read'])]
    #[ApiProperty(security: ['POST' => 'is_granted("ROLE_ADMIN")', 'PUT' => 'is_granted("ROLE_ADMIN")'])]
    private ?string $image = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['widget:read','widget:write','camera_group:read','info_group:read','match_group:read','poll_group:read','popup_group:read','tweet_group:read','model:read'])]
    #[ApiProperty(security: ['POST' => 'is_granted("ROLE_ADMIN")', 'PUT' => 'is_granted("ROLE_ADMIN")'])]
    private ?string $description = null;

    #[ORM\Column]
    #[Groups(['widget:read','widget:write','camera_group:read','info_group:read','match_group:read','poll_group:read','popup_group:read','tweet_group:read','model:read'])]
    private ?bool $visible = null;

    #[ORM\ManyToOne(inversedBy: 'widgets')]
    #[ApiProperty(security: ['POST' => 'is_granted("ROLE_ADMIN")', 'PUT' => 'is_granted("ROLE_ADMIN")'])]
    private ?MatchGroup $matchGroup = null;

    #[ORM\ManyToOne(inversedBy: 'widgets')]
    #[ApiProperty(security: ['POST' => 'is_granted("ROLE_ADMIN")', 'PUT' => 'is_granted("ROLE_ADMIN")'])]
    private ?InfoGroup $infoGroup = null;

    #[ORM\ManyToOne(inversedBy: 'widgets')]
    #[ApiProperty(security: ['POST' => 'is_granted("ROLE_ADMIN")', 'PUT' => 'is_granted("ROLE_ADMIN")'])]
    private ?CameraGroup $cameraGroup = null;

    #[ORM\ManyToOne(inversedBy: 'widgets')]
    #[ApiProperty(security: ['POST' => 'is_granted("ROLE_ADMIN")', 'PUT' => 'is_granted("ROLE_ADMIN")'])]
    private ?TweetGroup $tweetGroup = null;

    #[ORM\ManyToOne(inversedBy: 'widgets')]
    #[ApiProperty(security: ['POST' => 'is_granted("ROLE_ADMIN")', 'PUT' => 'is_granted("ROLE_ADMIN")'])]
    private ?PollGroup $pollGroup = null;

    #[ORM\ManyToOne(inversedBy: 'widgets')]
    #[ApiProperty(security: ['POST' => 'is_granted("ROLE_ADMIN")', 'PUT' => 'is_granted("ROLE_ADMIN")'])]
    private ?PopupGroup $popupGroup = null;

    #[ORM\ManyToOne(inversedBy: 'widgets')]
    #[ApiProperty(security: ['POST' => 'is_granted("ROLE_ADMIN")', 'PUT' => 'is_granted("ROLE_ADMIN")'])]
    private ?Model $model = null;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

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

    public function getMatchGroup(): ?MatchGroup
    {
        return $this->matchGroup;
    }

    public function setMatchGroup(?MatchGroup $matchGroup): self
    {
        $this->matchGroup = $matchGroup;

        return $this;
    }

    public function getInfoGroup(): ?InfoGroup
    {
        return $this->infoGroup;
    }

    public function setInfoGroup(?InfoGroup $infoGroup): self
    {
        $this->infoGroup = $infoGroup;

        return $this;
    }

    public function getCameraGroup(): ?CameraGroup
    {
        return $this->cameraGroup;
    }

    public function setCameraGroup(?CameraGroup $cameraGroup): self
    {
        $this->cameraGroup = $cameraGroup;

        return $this;
    }

    public function getTweetGroup(): ?TweetGroup
    {
        return $this->tweetGroup;
    }

    public function setTweetGroup(?TweetGroup $tweetGroup): self
    {
        $this->tweetGroup = $tweetGroup;

        return $this;
    }

    public function getPollGroup(): ?PollGroup
    {
        return $this->pollGroup;
    }

    public function setPollGroup(?PollGroup $pollGroup): self
    {
        $this->pollGroup = $pollGroup;

        return $this;
    }

    public function getPopupGroup(): ?PopupGroup
    {
        return $this->popupGroup;
    }

    public function setPopupGroup(?PopupGroup $popupGroup): self
    {
        $this->popupGroup = $popupGroup;

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
}
