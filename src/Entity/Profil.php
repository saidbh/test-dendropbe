<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProfilRepository")
 */
class Profil
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"read", "auth"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotNull()
     * @Groups({"read", "post", "auth"})
     */
    private $name;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"read"})
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"read"})
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="boolean", options={"default" : 0})
     */
    private $isInit;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\User", mappedBy="profil", orphanRemoval=true)
     */
    private $users;

    /**
     * @ORM\Column(type="string", length=255, options={"defalut": "EXPERT"})
     * @Groups({"read", "post", "auth"})
     */
    private $groupeType;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Droit", inversedBy="profils")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"read", "post"})
     */
    private $droit;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"default":false})
     */
    private $deleted;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->isInit = 0;
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

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getIsInit(): ?bool
    {
        return $this->isInit;
    }

    public function setIsInit(bool $isInit): self
    {
        $this->isInit = $isInit;
        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->setProfil($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
            // set the owning side to null (unless already changed)
            if ($user->getProfil() === $this) {
                $user->setProfil(null);
            }
        }

        return $this;
    }

    public function getGroupeType(): ?string
    {
        return $this->groupeType;
    }

    public function setGroupeType(string $groupeType): self
    {
        $this->groupeType = $groupeType;

        return $this;
    }

    public function getDroit(): ?Droit
    {
        return $this->droit;
    }

    public function setDroit(?Droit $droit): self
    {
        $this->droit = $droit;

        return $this;
    }

    public function getDeleted(): ?bool
    {
        return $this->deleted;
    }

    public function setDeleted(?bool $deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }

}
