<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\TweetGroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: TweetGroupRepository::class)]
#[ApiResource(operations: [
    new Get(
        uriTemplate: '/tweet-groups/{id}',
        requirements: ['id' => '\d+'],
        status: 200,
        schemes: ['https'],
        openapiContext: ['summary' => 'Récupérer les données d\'un groupe de tweets'],
        normalizationContext: ['groups' => ['tweet_group:read']],
        security: 'is_granted("ROLE_ADMIN") or object.getWidgets().getModel().getOverlay().getUserOwner() == user',
        securityMessage: 'Vous n\'avez pas accès à ce groupe de tweets',
    ),
    new GetCollection(
        uriTemplate: '/tweet-groups',
        status: 200,
        schemes: ['https'],
        openapiContext: ['summary' => 'Récupérer les données de tous les groupes de tweets'],
        normalizationContext: ['groups' => ['tweet_group:read']],
        security: 'is_granted("ROLE_ADMIN")',
        securityMessage: 'Seulement les administrateurs peuvent accéder à cette ressource.',
    ),
    new Post(
        uriTemplate: '/tweet-groups/add',
        status: 201,
        schemes: ['https'],
        openapiContext: ['summary' => 'Ajouter un groupe de tweets'],
        normalizationContext: ['groups' => ['tweet_group:read']],
        denormalizationContext: ['groups' => ['tweet_group:write']],
    ),
    new Put(
        uriTemplate: '/tweet-groups/{id}',
        requirements: ['id' => '\d+'],
        status: 200,
        schemes: ['https'],
        openapiContext: ['summary' => 'Modifier un groupe de tweets'],
        normalizationContext: ['groups' => ['tweet_group:read']],
        denormalizationContext: ['groups' => ['tweet_group:write']],
        security: 'is_granted("ROLE_ADMIN") or object.getWidgets().getModel().getOverlay().getUserOwner() == user',
        securityMessage: 'Vous n\'avez pas accès à ce groupe de tweets',
    ),
    new Delete(
        uriTemplate: '/tweet-groups/{id}',
        requirements: ['id' => '\d+'],
        status: 204,
        schemes: ['https'],
        openapiContext: ['summary' => 'Supprimer un groupe de tweets'],
        security: 'is_granted("ROLE_ADMIN") or object.getWidgets().getModel().getOverlay().getUserOwner() == user',
        securityMessage: 'Vous n\'avez pas accès à ce groupe de tweets',
    )
], schemes: ['https'], normalizationContext: ['groups' => ['tweet_group:read']], denormalizationContext: ['groups' => ['tweet_group:write']], openapiContext: ['summary' => 'TweetGroup'])]
class TweetGroup
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['tweet_group:read'])]
    #[ApiProperty(security: 'is_granted("ROLE_ADMIN")')]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['tweet_group:read', 'tweet_group:write'])]
    #[ApiProperty(securityPostDenormalize: 'is_granted("ROLE_ADMIN")')]
    private ?string $pseudo = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['tweet_group:read', 'tweet_group:write'])]
    #[ApiProperty(securityPostDenormalize: 'is_granted("ROLE_ADMIN")')]
    private ?string $at = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['tweet_group:read', 'tweet_group:write'])]
    #[ApiProperty(securityPostDenormalize: 'is_granted("ROLE_ADMIN")')]
    private ?string $avatar = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['tweet_group:read', 'tweet_group:write'])]
    #[ApiProperty(securityPostDenormalize: 'is_granted("ROLE_ADMIN")')]
    private ?string $mediaType = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['tweet_group:read', 'tweet_group:write'])]
    #[ApiProperty(securityPostDenormalize: 'is_granted("ROLE_ADMIN")')]
    private ?string $mediaUrl = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['tweet_group:read', 'tweet_group:write'])]
    #[ApiProperty(securityPostDenormalize: 'is_granted("ROLE_ADMIN")')]
    private ?string $content = null;

    #[ORM\OneToMany(mappedBy: 'tweetGroup', targetEntity: Widget::class)]
    #[Groups(['tweet_group:read'])]
    #[ApiProperty(securityPostDenormalize: 'is_granted("ROLE_ADMIN")')]
    private Collection $widgets;

    public function __construct()
    {
        $this->widgets = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(?string $pseudo): self
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    public function getAt(): ?string
    {
        return $this->at;
    }

    public function setAt(?string $at): self
    {
        $this->at = $at;

        return $this;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): self
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function getMediaType(): ?string
    {
        return $this->mediaType;
    }

    public function setMediaType(?string $mediaType): self
    {
        $this->mediaType = $mediaType;

        return $this;
    }

    public function getMediaUrl(): ?string
    {
        return $this->mediaUrl;
    }

    public function setMediaUrl(?string $mediaUrl): self
    {
        $this->mediaUrl = $mediaUrl;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

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
            $widget->setTweetGroup($this);
        }

        return $this;
    }

    public function removeWidget(Widget $widget): self
    {
        if ($this->widgets->removeElement($widget)) {
            // set the owning side to null (unless already changed)
            if ($widget->getTweetGroup() === $this) {
                $widget->setTweetGroup(null);
            }
        }

        return $this;
    }
}
