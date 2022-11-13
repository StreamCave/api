<?php

namespace App\Entity;

use App\Repository\CameraGroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CameraGroupRepository::class)]
class CameraGroup
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::GUID, unique: true)]
    private ?string $uuid = null;

    #[ORM\Column(length: 255)]
    private ?string $idNinja = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $uplayTag = null;

    #[ORM\Column(nullable: true)]
    private ?int $positionTop = null;

    #[ORM\Column(nullable: true)]
    private ?int $positionBottom = null;

    #[ORM\Column(nullable: true)]
    private ?int $positionLeft = null;

    #[ORM\Column(nullable: true)]
    private ?int $positionRight = null;

    #[ORM\OneToMany(mappedBy: 'cameraGroup', targetEntity: Widget::class)]
    private Collection $widgets;

    public function __construct()
    {
        $this->widgets = new ArrayCollection();
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

    public function getIdNinja(): ?string
    {
        return $this->idNinja;
    }

    public function setIdNinja(string $idNinja): self
    {
        $this->idNinja = $idNinja;

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

    public function getUplayTag(): ?string
    {
        return $this->uplayTag;
    }

    public function setUplayTag(?string $uplayTag): self
    {
        $this->uplayTag = $uplayTag;

        return $this;
    }

    public function getPositionTop(): ?int
    {
        return $this->positionTop;
    }

    public function setPositionTop(?int $positionTop): self
    {
        $this->positionTop = $positionTop;

        return $this;
    }

    public function getPositionBottom(): ?int
    {
        return $this->positionBottom;
    }

    public function setPositionBottom(?int $positionBottom): self
    {
        $this->positionBottom = $positionBottom;

        return $this;
    }

    public function getPositionLeft(): ?int
    {
        return $this->positionLeft;
    }

    public function setPositionLeft(?int $positionLeft): self
    {
        $this->positionLeft = $positionLeft;

        return $this;
    }

    public function getPositionRight(): ?int
    {
        return $this->positionRight;
    }

    public function setPositionRight(?int $positionRight): self
    {
        $this->positionRight = $positionRight;

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
            $widget->setCameraGroup($this);
        }

        return $this;
    }

    public function removeWidget(Widget $widget): self
    {
        if ($this->widgets->removeElement($widget)) {
            // set the owning side to null (unless already changed)
            if ($widget->getCameraGroup() === $this) {
                $widget->setCameraGroup(null);
            }
        }

        return $this;
    }
}
