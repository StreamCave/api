<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\R6StatsPlayersRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: R6StatsPlayersRepository::class)]
#[ApiResource(operations: [
    new Get(
        uriTemplate: '/stats/game/r6/players/{uuid}',
        uriVariables: "uuid",
        status: 200,
        schemes: ['https'],
        openapiContext: ['summary' => 'Récupérer les données'],
        normalizationContext: ['groups' => ['r6_stats_players:read']]
    ),
    new GetCollection(
        uriTemplate: '/stats/game/r6/players',
        status: 200,
        schemes: ['https'],
        openapiContext: ['summary' => 'Récupérer les données'],
        normalizationContext: ['groups' => ['r6_stats_players:read']],
        security: 'is_granted("ROLE_ADMIN")',
        securityMessage: 'Seulement les administrateurs peuvent accéder à cette ressource.',
    ),
], schemes: ['https'], normalizationContext: ['groups' => ['r6_stats_players:read']], denormalizationContext: ['groups' => ['r6_stats_players:write']], openapiContext: ['summary' => 'R6StatsPlayers'])]
class R6StatsPlayers
{
    #[ORM\Id]
    #[ORM\Column]
    #[Groups(['r6_stats_players:read'])]
    private ?string $id = null;

    #[ORM\Id]
    #[ORM\Column(length: 255)]
    #[Groups(['r6_stats_players:read', 'r6_stats_players:write'])]
    private ?string $matchId = null;

    #[ORM\Id]
    #[ORM\Column(nullable: true)]
    #[Groups(['r6_stats_players:read', 'r6_stats_players:write'])]
    private ?int $round = null;

    #[ORM\Column(length: 255)]
    #[Groups(['r6_stats_players:read', 'r6_stats_players:write'])]
    private ?string $pseudo = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['r6_stats_players:read', 'r6_stats_players:write'])]
    private ?int $kills = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['r6_stats_players:read', 'r6_stats_players:write'])]
    private ?int $deaths = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['r6_stats_players:read', 'r6_stats_players:write'])]
    private ?int $assists = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['r6_stats_players:read', 'r6_stats_players:write'])]
    private ?int $hp = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['r6_stats_players:read', 'r6_stats_players:write'])]
    private ?int $score = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['r6_stats_players:read', 'r6_stats_players:write'])]
    private ?string $operator = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['r6_stats_players:read', 'r6_stats_players:write'])]
    private ?string $team = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getMatchId(): ?string
    {
        return $this->matchId;
    }

    public function setMatchId(string $matchId): static
    {
        $this->matchId = $matchId;

        return $this;
    }

    public function getRound(): ?int
    {
        return $this->round;
    }

    public function setRound(?int $round): static
    {
        $this->round = $round;

        return $this;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): static
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    public function getKills(): ?int
    {
        return $this->kills;
    }

    public function setKills(?int $kills): static
    {
        $this->kills = $kills;

        return $this;
    }

    public function getDeaths(): ?int
    {
        return $this->deaths;
    }

    public function setDeaths(?int $deaths): static
    {
        $this->deaths = $deaths;

        return $this;
    }

    public function getAssists(): ?int
    {
        return $this->assists;
    }

    public function setAssists(?int $assists): static
    {
        $this->assists = $assists;

        return $this;
    }

    public function getHp(): ?int
    {
        return $this->hp;
    }

    public function setHp(?int $hp): static
    {
        $this->hp = $hp;

        return $this;
    }

    public function getScore(): ?int
    {
        return $this->score;
    }

    public function setScore(?int $score): static
    {
        $this->score = $score;

        return $this;
    }

    public function getOperator(): ?string
    {
        return $this->operator;
    }

    public function setOperator(?string $operator): static
    {
        $this->operator = $operator;

        return $this;
    }

    public function getTeam(): ?string
    {
        return $this->team;
    }

    public function setTeam(?string $team): static
    {
        $this->team = $team;

        return $this;
    }
}
