<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\AnswerGroupRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: AnswerGroupRepository::class)]
#[ApiResource(operations: [
    new Get(
        uriTemplate: '/answer-groups/{uuid}',
        uriVariables: "uuid",
        status: 200,
        schemes: ['https'],
        openapiContext: ['summary' => 'Récupérer les données d\'un groupe de réponses'],
        normalizationContext: ['groups' => ['answer_group:read']],
        security: 'is_granted("ROLE_ADMIN") or object.getWidgets().getModel().getOverlay().getUserOwner() == user',
        securityMessage: 'Vous n\'avez pas accès à ce groupe de camera',
    ),
    new GetCollection(
        uriTemplate: '/answer-groups',
        status: 200,
        schemes: ['https'],
        openapiContext: ['summary' => 'Récupérer les données de tous les groupes de réponses'],
        normalizationContext: ['groups' => ['answer_group:read']],
        security: 'is_granted("ROLE_ADMIN")',
        securityMessage: 'Seulement les administrateurs peuvent accéder à cette ressource.',
    ),
    new Post(
        uriTemplate: '/answer-groups/add',
        status: 201,
        schemes: ['https'],
        openapiContext: ['summary' => 'Ajouter un groupe de réponses'],
        normalizationContext: ['groups' => ['answer_group:read']],
        denormalizationContext: ['groups' => ['answer_group:write']],
    ),
    new Put(
        uriTemplate: '/answer-groups/{uuid}',
        uriVariables: "uuid",
        status: 200,
        schemes: ['https'],
        openapiContext: ['summary' => 'Modifier un groupe de réponses'],
        normalizationContext: ['groups' => ['answer_group:read']],
        denormalizationContext: ['groups' => ['answer_group:write']],
        security: 'is_granted("ROLE_ADMIN") or object.getWidgets().getModel().getOverlay().getUserOwner() == user',
        securityMessage: 'Vous n\'avez pas accès à ce groupe de camera',
    ),
    new Delete(
        uriTemplate: '/answer-groups/{uuid}',
        uriVariables: "uuid",
        status: 204,
        schemes: ['https'],
        openapiContext: ['summary' => 'Supprimer un groupe de réponses'],
        security: 'is_granted("ROLE_ADMIN") or object.getWidgets().getModel().getOverlay().getUserOwner() == user',
        securityMessage: 'Vous n\'avez pas accès à ce groupe de camera',
    )
], schemes: ['https'], normalizationContext: ['groups' => ['answer_group:read']], denormalizationContext: ['groups' => ['answer_group:write']], openapiContext: ['summary' => 'AnswerGroup'])]
class AnswerGroup
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['answer_group:read','widget:read','model:read','overlay:read', 'overlay:write', 'poll_group:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['answer_group:read', 'answer_group:write','widget:read','model:read','overlay:read', 'overlay:write', 'poll_group:read'])]
    private ?string $uuid = null;

    #[ORM\ManyToOne(inversedBy: 'answerGroups')]
    #[Groups(['answer_group:read', 'answer_group:write','widget:read','model:read','overlay:read', 'overlay:write', 'poll_group:read'])]
    private ?PollGroup $pollGroup = null;

    #[ORM\Column(length: 255)]
    #[Groups(['answer_group:read', 'answer_group:write','widget:read','model:read','overlay:read', 'overlay:write', 'poll_group:read'])]
    private ?string $answer = null;

    #[ORM\Column(length: 255)]
    #[Groups(['answer_group:read', 'answer_group:write','widget:read','model:read','overlay:read', 'overlay:write', 'poll_group:read'])]
    private ?string $vote = null;

    #[ORM\Column(length: 255)]
    #[Groups(['answer_group:read', 'answer_group:write','widget:read','model:read','overlay:read', 'overlay:write', 'poll_group:read'])]
    private ?string $usernameVoter = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getAnswer(): ?string
    {
        return $this->answer;
    }

    public function setAnswer(string $answer): self
    {
        $this->answer = $answer;

        return $this;
    }

    public function getVote(): ?string
    {
        return $this->vote;
    }

    public function setVote(string $vote): self
    {
        $this->vote = $vote;

        return $this;
    }

    public function getUsernameVoter(): ?string
    {
        return $this->usernameVoter;
    }

    public function setUsernameVoter(string $usernameVoter): self
    {
        $this->usernameVoter = $usernameVoter;

        return $this;
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
}
