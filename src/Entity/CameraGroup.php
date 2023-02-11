<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\CameraGroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CameraGroupRepository::class)]
#[ApiResource]
class CameraGroup
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $height = null;

    #[ORM\Column(length: 255)]
    private ?string $width = null;

    #[ORM\Column(nullable: true)]
    private ?float $positionX = null;

    #[ORM\Column(nullable: true)]
    private ?float $positionY = null;

    #[ORM\Column]
    private ?bool $visible = null;

    #[ORM\Column]
    private ?bool $muet = null;

    #[ORM\ManyToMany(targetEntity: Widget::class, mappedBy: 'cameraGroup')]
    private Collection $widgets;

    public function __construct()
    {
        $this->widgets = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getHeight(): ?string
    {
        return $this->height;
    }

    public function setHeight(string $height): self
    {
        $this->height = $height;

        return $this;
    }

    public function getWidth(): ?string
    {
        return $this->width;
    }

    public function setWidth(string $width): self
    {
        $this->width = $width;

        return $this;
    }

    public function getPositionX(): ?float
    {
        return $this->positionX;
    }

    public function setPositionX(?float $positionX): self
    {
        $this->positionX = $positionX;

        return $this;
    }

    public function getPositionY(): ?float
    {
        return $this->positionY;
    }

    public function setPositionY(?float $positionY): self
    {
        $this->positionY = $positionY;

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

    public function isMuet(): ?bool
    {
        return $this->muet;
    }

    public function setMuet(bool $muet): self
    {
        $this->muet = $muet;

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
            $widget->addCameraGroup($this);
        }

        return $this;
    }

    public function removeWidget(Widget $widget): self
    {
        if ($this->widgets->removeElement($widget)) {
            $widget->removeCameraGroup($this);
        }

        return $this;
    }
}
