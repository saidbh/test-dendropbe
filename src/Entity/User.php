<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Symfony\Component\Serializer\Annotation\Groups;
use Swagger\Annotations as SWG;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"read", "auth"})
     * @SWG\Property(description="The unique identifer of the user")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255,  nullable=true)
     * @Groups({"read", "auth"})
     * @SWG\Property(type="string", description="name of user")
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read", "auth"})
     * @SWG\Property(type="string", description="prenom of user")
     */
    private $prenom;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"read", "auth"})
     * @SWG\Property(type="string", description="username of user")
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"read", "auth"})
     * @SWG\Property(type="string", description="name of user")
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     * @SWG\Property(type="string", description="Password ")
     */
    private $password;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"read", "auth"})
     * @SWG\Property(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"read", "auth"})
     */
    private $updatedAt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Profil", inversedBy="users")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"read", "auth"})
     */
    private $profil;

    /**
     * @ORM\Column(type="boolean", options={"default" : 1})
     * @Groups({"read"})
     */
    private $isActive;

    /**
     * @ORM\Column(type="boolean", options={"default" : 0})
     * @Groups({"read"})
     * @SWG\Property(type="bool", description="if user already validate his email")
     */
    private $emailActive;

    /**
     * @ORM\Column(type="boolean", options={"default" : 0})
     */
    private $isInit;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotBlank(message="Ajouter une image au format png, jpeg, et jpg")
     * @Assert\File(mimeTypes={"image/png", "image/jpeg", "image/jpg" }, maxSize="5M")
     * @Groups({"read", "auth"})
     * @SWG\Property(type="string", description="Image url of profile picture")
     */
    private $img;

    /**
     * @ORM\Column(type="boolean", options={"default" : 0})
     *
     */
    private $isRoot;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Inventaire", mappedBy="user")
     */
    private $inventaires;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Groupe", inversedBy="users")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"read", "auth"})
     */
    private $groupe;

    /**
     * @ORM\Column(type="boolean", options={"default":false}, nullable=true)
     */
    private $deleted;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read"})
     * @SWG\Property(type="string", description="city of user")
     */
    private $city;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read"})
     * @SWG\Property(type="string", description="phone")
     */
    private $phoneNumber;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read"})
     * @SWG\Property(type="string", description="zipCode user")
     */
    private $zipCode;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read"})
     * @SWG\Property(type="string", description="address")
     */
    private $address;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read"})
     * @SWG\Property(type="string", description="Name of societe")
     */
    private $address2;

    public function __construct()
    {
        $this->inventaires = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(?string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
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

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

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

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getProfil(): ?Profil
    {
        return $this->profil;
    }

    public function setProfil(?Profil $profil): self
    {
        $this->profil = $profil;

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getEmailActive(): ?bool
    {
        return $this->emailActive;
    }

    public function setEmailActive(bool $emailActive): self
    {
        $this->emailActive = $emailActive;

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

    public function getImg(): ?string
    {
        return $this->img;
    }

    public function setImg(?string $img): self
    {
        $this->img = $img;

        return $this;
    }

    public function getIsRoot(): ?bool
    {
        return $this->isRoot;
    }

    public function setIsRoot(bool $isRoot): self
    {
        $this->isRoot = $isRoot;

        return $this;
    }

    /**
     * @return Collection|Inventaire[]
     */
    public function getInventaires(): Collection
    {
        return $this->inventaires;
    }

    public function addInventaire(Inventaire $inventaire): self
    {
        if (!$this->inventaires->contains($inventaire)) {
            $this->inventaires[] = $inventaire;
            $inventaire->setUser($this);
        }

        return $this;
    }

    public function removeInventaire(Inventaire $inventaire): self
    {
        if ($this->inventaires->contains($inventaire)) {
            $this->inventaires->removeElement($inventaire);
            // set the owning side to null (unless already changed)
            if ($inventaire->getUser() === $this) {
                $inventaire->setUser(null);
            }
        }

        return $this;
    }

    public function getGroupe(): ?Groupe
    {
        return $this->groupe;
    }

    public function setGroupe(?Groupe $groupe): self
    {
        $this->groupe = $groupe;

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

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): self
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getZipCode(): ?string
    {
        return $this->zipCode;
    }

    public function setZipCode(?string $zipCode): self
    {
        $this->zipCode = $zipCode;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getAddress2(): ?string
    {
        return $this->address2;
    }

    public function setAddress2(?string $address2): self
    {
        $this->address2 = $address2;

        return $this;
    }

}
