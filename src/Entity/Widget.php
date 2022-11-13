<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\WidgetRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WidgetRepository::class)]
#[ApiResource]
class Widget
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::GUID, unique: true)]
    private ?string $uuid = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private ?bool $visible = null;

    #[ORM\ManyToOne(inversedBy: 'widgets')]
    private ?MatchGroup $matchGroup = null;

    #[ORM\ManyToOne(inversedBy: 'widgets')]
    private ?InfoGroup $infoGroup = null;

    #[ORM\ManyToOne(inversedBy: 'widgets')]
    private ?CameraGroup $cameraGroup = null;

    #[ORM\ManyToOne(inversedBy: 'widgets')]
    private ?TweetGroup $tweetGroup = null;

    #[ORM\ManyToOne(inversedBy: 'widgets')]
    private ?PollGroup $pollGroup = null;

    #[ORM\ManyToOne(inversedBy: 'widgets')]
    private ?PopupGroup $popupGroup = null;

    #[ORM\ManyToOne(inversedBy: 'widgets')]
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
