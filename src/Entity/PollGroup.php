<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\DeletePollGroup;
use App\Repository\PollGroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: PollGroupRepository::class)]
#[ApiResource(operations: [
    new Get(
        uriTemplate: '/poll-groups/{uuid}',
        uriVariables: "uuid",
        status: 200,
        schemes: ['https'],
        openapiContext: ['summary' => 'Récupérer les données d\'un groupe de sondages'],
        normalizationContext: ['groups' => ['poll_group:read']],
        security: 'is_granted("ROLE_ADMIN")',
        securityMessage: 'Seulement les administrateurs peuvent accéder à cette ressource.',
    ),
    new GetCollection(
        uriTemplate: '/poll-groups',
        status: 200,
        schemes: ['https'],
        openapiContext: ['summary' => 'Récupérer les données de tous les groupes de sondages'],
        normalizationContext: ['groups' => ['poll_group:read']],
        security: 'is_granted("ROLE_ADMIN")',
        securityMessage: 'Seulement les administrateurs peuvent accéder à cette ressource.',
    ),
    new Post(
        uriTemplate: '/poll-groups/add',
        status: 201,
        schemes: ['https'],
        openapiContext: ['summary' => 'Ajouter un groupe de sondages'],
        normalizationContext: ['groups' => ['poll_group:read']],
        denormalizationContext: ['groups' => ['poll_group:write']],
    ),
    new Put(
        uriTemplate: '/poll-groups/{uuid}',
        uriVariables: "uuid",
        status: 200,
        schemes: ['https'],
        openapiContext: ['summary' => 'Modifier un groupe de sondages'],
        normalizationContext: ['groups' => ['poll_group:read']],
        denormalizationContext: ['groups' => ['poll_group:write']],
        security: 'is_granted("ROLE_ADMIN") or object.getWidgets().getModel().getOverlay().getUserOwner() == user',
        securityMessage: 'Vous n\'avez pas accès à ce groupe de sondages',
    ),
    new Delete(
        uriTemplate: '/poll-groups/{uuid}',
        uriVariables: "uuid",
        status: 204,
        schemes: ['https'],
        controller: DeletePollGroup::class,
        openapiContext: ['summary' => 'Supprimer un groupe de sondages'],
        security: 'is_granted("ROLE_ADMIN") or object.getWidgets().getModel().getOverlay().getUserOwner() == user',
        securityMessage: 'Vous n\'avez pas accès à ce groupe de sondages',
    )
], schemes: ['https'], normalizationContext: ['groups' => ['poll_group:read']], denormalizationContext: ['groups' => ['poll_group:write']], openapiContext: ['summary' => 'PollGroup'])]
class PollGroup
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['poll_group:read','widget:read','model:read','overlay:read', 'overlay:write'])]
    #[ApiProperty(security: 'is_granted("ROLE_ADMIN")')]
    private ?int $id = null;

    #[ORM\Column(type: Types::GUID, unique: true)]
    #[Groups(['poll_group:read', 'poll_group:write','widget:read','model:read','overlay:read', 'overlay:write'])]
    #[ApiProperty(security: 'is_granted("ROLE_ADMIN")')]
    private ?string $uuid;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['poll_group:read', 'poll_group:write','widget:read','model:read','overlay:read', 'overlay:write'])]
    private ?string $question = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['poll_group:read', 'poll_group:write','widget:read','model:read','overlay:read', 'overlay:write'])]
    private ?int $time = null;

    #[ORM\OneToMany(mappedBy: 'pollGroup', targetEntity: Widget::class)]
    #[Groups(['poll_group:read', 'poll_group:write','widget:read','model:read','overlay:read', 'overlay:write'])]
    #[ApiProperty(securityPostDenormalize: 'is_granted("ROLE_ADMIN")')]
    private Collection $widgets;

    #[ORM\OneToMany(mappedBy: 'pollGroup', targetEntity: AnswerGroup::class)]
    #[Groups(['poll_group:read', 'poll_group:write','widget:read','model:read','overlay:read', 'overlay:write'])]
    private Collection $answerGroups;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['poll_group:read', 'poll_group:write','widget:read','model:read','overlay:read', 'overlay:write'])]
    private ?string $overlayId = null;

    #[ORM\Column(length: 255)]
    #[Groups(['poll_group:read', 'poll_group:write','widget:read','model:read','overlay:read', 'overlay:write'])]
    private ?string $channel = null;

    #[ORM\Column]
    #[Groups(['poll_group:read', 'poll_group:write','widget:read','model:read','overlay:read', 'overlay:write'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['poll_group:read', 'poll_group:write','widget:read','model:read','overlay:read', 'overlay:write'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column]
    #[Groups(['poll_group:read', 'poll_group:write','widget:read','model:read','overlay:read', 'overlay:write'])]
    private ?bool $pollStarted = null;

    #[ORM\Column]
    #[Groups(['poll_group:read', 'poll_group:write','widget:read','model:read','overlay:read', 'overlay:write'])]
    private ?bool $visible = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['poll_group:read', 'poll_group:write','widget:read','model:read','overlay:read', 'overlay:write'])]
    private array $choices = [];

    public function __construct()
    {
        $this->widgets = new ArrayCollection();
        $this->uuid = Uuid::v4();
        $this->answerGroups = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
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

    public function getQuestion(): ?string
    {
        return $this->question;
    }

    public function setQuestion(string $question): self
    {
        $this->question = $question;

        return $this;
    }

    public function getTime(): ?int
    {
        return $this->time;
    }

    public function setTime(?int $time): self
    {
        $this->time = $time;

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
            $widget->setPollGroup($this);
        }

        return $this;
    }

    public function removeWidget(Widget $widget): self
    {
        if ($this->widgets->removeElement($widget)) {
            // set the owning side to null (unless already changed)
            if ($widget->getPollGroup() === $this) {
                $widget->setPollGroup(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, AnswerGroup>
     */
    public function getAnswerGroups(): Collection
    {
        return $this->answerGroups;
    }

    public function addAnswerGroup(AnswerGroup $answerGroup): self
    {
        if (!$this->answerGroups->contains($answerGroup)) {
            $this->answerGroups->add($answerGroup);
            $answerGroup->setPollGroup($this);
        }

        return $this;
    }

    public function removeAnswerGroup(AnswerGroup $answerGroup): self
    {
        if ($this->answerGroups->removeElement($answerGroup)) {
            // set the owning side to null (unless already changed)
            if ($answerGroup->getPollGroup() === $this) {
                $answerGroup->setPollGroup(null);
            }
        }

        return $this;
    }

    public function getOverlayId(): ?string
    {
        return $this->overlayId;
    }

    public function setOverlayId(string $overlayId): self
    {
        $this->overlayId = $overlayId;

        return $this;
    }

    public function getChannel(): ?string
    {
        return $this->channel;
    }

    public function setChannel(string $channel): self
    {
        $this->channel = $channel;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function isPollStarted(): ?bool
    {
        return $this->pollStarted;
    }

    public function setPollStarted(bool $pollStarted): self
    {
        $this->pollStarted = $pollStarted;

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

    public function getChoices(): array
    {
        return $this->choices;
    }

    public function setChoices(?array $choices): self
    {
        $this->choices = $choices;

        return $this;
    }
}
