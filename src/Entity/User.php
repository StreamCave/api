<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ApiResource]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $uuid = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $email = null;

    #[ORM\OneToMany(mappedBy: 'userOwner', targetEntity: Overlay::class)]
    private Collection $overlays;

    #[ORM\ManyToMany(targetEntity: Overlay::class, mappedBy: 'userAccess')]
    private Collection $overlaysAccess;

    public function __construct()
    {
        $this->overlays = new ArrayCollection();
        $this->overlaysAccess = new ArrayCollection();
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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->uuid;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return Collection<int, Overlay>
     */
    public function getOverlays(): Collection
    {
        return $this->overlays;
    }

    public function addOverlay(Overlay $overlay): self
    {
        if (!$this->overlays->contains($overlay)) {
            $this->overlays->add($overlay);
            $overlay->setUserOwner($this);
        }

        return $this;
    }

    public function removeOverlay(Overlay $overlay): self
    {
        if ($this->overlays->removeElement($overlay)) {
            // set the owning side to null (unless already changed)
            if ($overlay->getUserOwner() === $this) {
                $overlay->setUserOwner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Overlay>
     */
    public function getOverlaysAccess(): Collection
    {
        return $this->overlaysAccess;
    }

    public function addOverlaysAccess(Overlay $overlaysAccess): self
    {
        if (!$this->overlaysAccess->contains($overlaysAccess)) {
            $this->overlaysAccess->add($overlaysAccess);
            $overlaysAccess->addUserAccess($this);
        }

        return $this;
    }

    public function removeOverlaysAccess(Overlay $overlaysAccess): self
    {
        if ($this->overlaysAccess->removeElement($overlaysAccess)) {
            $overlaysAccess->removeUserAccess($this);
        }

        return $this;
    }
}
