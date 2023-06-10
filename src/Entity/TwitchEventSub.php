<?php

namespace App\Entity;

use App\Repository\TwitchEventSubRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TwitchEventSubRepository::class)]
class TwitchEventSub
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $type = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $broadcasterUserId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $sessionId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $eventSubTwitchId = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getBroadcasterUserId(): ?string
    {
        return $this->broadcasterUserId;
    }

    public function setBroadcasterUserId(?string $broadcasterUserId): self
    {
        $this->broadcasterUserId = $broadcasterUserId;

        return $this;
    }

    public function getSessionId(): ?string
    {
        return $this->sessionId;
    }

    public function setSessionId(?string $sessionId): self
    {
        $this->sessionId = $sessionId;

        return $this;
    }

    public function getEventSubTwitchId(): ?string
    {
        return $this->eventSubTwitchId;
    }

    public function setEventSubTwitchId(?string $eventSubTwitchId): self
    {
        $this->eventSubTwitchId = $eventSubTwitchId;

        return $this;
    }
}
