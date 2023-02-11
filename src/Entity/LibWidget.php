<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\LibWidgetRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: LibWidgetRepository::class)]
#[ApiResource(operations: [
    new Get(
        uriTemplate: '/libwidgets/{uuid}',
        uriVariables: "uuid",
        status: 200,
        schemes: ['https'],
        openapiContext: ['summary' => 'Récupérer les données d\'un libWidget'],
        normalizationContext: ['groups' => ['libwidgets:read']],
    ),
    new GetCollection(
        uriTemplate: '/libwidgets',
        status: 200,
        schemes: ['https'],
        openapiContext: ['summary' => 'Récupérer les données de tous les libWidget'],
        normalizationContext: ['groups' => ['libwidgets:read']],
    ),
    new Post(
        uriTemplate: '/libwidgets/add',
        status: 201,
        schemes: ['https'],
        openapiContext: ['summary' => 'Ajouter un libWidget'],
        normalizationContext: ['groups' => ['libwidgets:read']],
        denormalizationContext: ['groups' => ['libwidgets:write']],
    ),
    new Put(
        uriTemplate: '/libwidgets/{uuid}',
        uriVariables: "uuid",
        status: 200,
        schemes: ['https'],
        openapiContext: ['summary' => 'Modifier un libWidget'],
        normalizationContext: ['groups' => ['libwidgets:read']],
        denormalizationContext: ['groups' => ['libwidgets:write']],
    ),
    new Delete(
        uriTemplate: '/libwidgets/{uuid}',
        uriVariables: "uuid",
        status: 204,
        schemes: ['https'],
        openapiContext: ['summary' => 'Supprimer un libWidget'],
    )
], schemes: ['https'], normalizationContext: ['groups' => ['libwidgets:read']], denormalizationContext: ['groups' => ['libwidgets:write']], openapiContext: ['summary' => 'LibWidget'])]
class LibWidget
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'uuid', unique: true)]
    #[Groups(['libwidgets:read'])]
    private ?Uuid $uuid;

    #[ORM\Column(length: 255)]
    #[Groups(['libwidgets:read', 'libwidgets:write'])]
    private ?string $nameWidget = null;

    #[ORM\Column(length: 255)]
    #[Groups(['libwidgets:read', 'libwidgets:write'])]
    private ?string $nameGroup = null;

    public function __construct()
    {
        $this->uuid = Uuid::v4();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): ?Uuid
    {
        return $this->uuid;
    }

    public function setUuid(Uuid $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getNameWidget(): ?string
    {
        return $this->nameWidget;
    }

    public function setNameWidget(string $nameWidget): self
    {
        $this->nameWidget = $nameWidget;

        return $this;
    }

    public function getNameGroup(): ?string
    {
        return $this->nameGroup;
    }

    public function setNameGroup(string $nameGroup): self
    {
        $this->nameGroup = $nameGroup;

        return $this;
    }
}
