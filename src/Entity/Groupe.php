<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Swagger\Annotations as SWG;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GroupeRepository")
 */
class Groupe
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"read", "auth"})
     * @SWG\Property(description="The unique identifer of the groupe")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"read", "post", "auth"})
     * @SWG\Property(type="string", maxLength=255)
     */
    private $name;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"read"})
     * @SWG\Property(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"read"})
     * @SWG\Property(type="datetime")
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"read", "post", "auth"})
     * @SWG\Property(type="string", enum={"DENDROMAP", "FORMULE PREMUIM"})
     */
    private $groupeType;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isInit;

    /**
     * @ORM\Column(type="integer", nullable=true, options={"default" : 1})
     * @Groups({"read", "post", "auth"})
     * @SWG\Property(type="integer")
     */
    private $licence;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Forfait", inversedBy="groupes")
     * @Groups({"read", "post", "auth"})
     */
    private $forfait;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\User", mappedBy="groupe", orphanRemoval=true)
     */
    private $users;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"read"})
     * @SWG\Property(type="boolean", description="for all users subscride by Stripe")
     */
    private $isStripped;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read"})
     * @SWG\Property(type="string", description="Id when subscribed to Scripe")
     */
    private $subId;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read"})
     * @SWG\Property(type="string")
     */
    private $customerId;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"read"})
     * @SWG\Property(type="datetime")
     */
    private $dateEcheance;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"read"})
     * @SWG\Property(type="datetime")
     */
    private $dateSubscribed;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read", "update"})
     * @SWG\Property(type="string")
     */
    private $siret;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read", "update"})
     * @SWG\Property(type="string")
     */
    private $numCertification;

    /**
     * @ORM\Column(type="string", length=5, nullable=true)
     * @Groups({"read", "update"})
     * @SWG\Property(type="string")
     */
    private $cp;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"read", "update"})
     * @SWG\Property(type="string")
     */
    private $ville;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read"})
     * @SWG\Property(type="string")
     */
    private $imgLogo;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read", "update"})
     * @SWG\Property(type="string")
     */
    private $addressSociete;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"read", "update"})
     * @SWG\Property(type="string")
     */
    private $nameSociete;

    public function __construct()
    {
        $this->users = new ArrayCollection();
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

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
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

    public function getIsInit(): ?bool
    {
        return $this->isInit;
    }

    public function setIsInit(bool $isInit): self
    {
        $this->isInit = $isInit;
        return $this;
    }

    public function getLicence(): ?int
    {
        return $this->licence;
    }

    public function setLicence(?int $licence): self
    {
        $this->licence = $licence;
        return $this;
    }

    public function getForfait(): ?Forfait
    {
        return $this->forfait;
    }

    public function setForfait(?Forfait $forfait): self
    {
        $this->forfait = $forfait;
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
            $user->setGroupe($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
            // set the owning side to null (unless already changed)
            if ($user->getGroupe() === $this) {
                $user->setGroupe(null);
            }
        }

        return $this;
    }

    public function getIsStripped(): ?bool
    {
        return $this->isStripped;
    }

    public function setIsStripped(?bool $isStripped): self
    {
        $this->isStripped = $isStripped;

        return $this;
    }

    public function getSubId(): ?string
    {
        return $this->subId;
    }

    public function setSubId(?string $subId): self
    {
        $this->subId = $subId;

        return $this;
    }

    public function getCustomerId(): ?string
    {
        return $this->customerId;
    }

    public function setCustomerId(?string $customerId): self
    {
        $this->customerId = $customerId;

        return $this;
    }

    public function getDateEcheance(): ?\DateTimeInterface
    {
        return $this->dateEcheance;
    }

    public function setDateEcheance(?\DateTimeInterface $dateEcheance): self
    {
        $this->dateEcheance = $dateEcheance;

        return $this;
    }

    public function getDateSubscribed(): ?\DateTimeInterface
    {
        return $this->dateSubscribed;
    }

    public function setDateSubscribed(?\DateTimeInterface $dateSubscribed): self
    {
        $this->dateSubscribed = $dateSubscribed;

        return $this;
    }

    public function getSiret(): ?string
    {
        return $this->siret;
    }

    public function setSiret(?string $siret): self
    {
        $this->siret = $siret;

        return $this;
    }

    public function getNumCertification(): ?string
    {
        return $this->numCertification;
    }

    public function setNumCertification(?string $numCertification): self
    {
        $this->numCertification = $numCertification;

        return $this;
    }

    public function getCp(): ?string
    {
        return $this->cp;
    }

    public function setCp(?string $cp): self
    {
        $this->cp = $cp;

        return $this;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(?string $ville): self
    {
        $this->ville = $ville;

        return $this;
    }

    public function getImgLogo(): ?string
    {
        return $this->imgLogo;
    }

    public function setImgLogo(?string $imgLogo): self
    {
        $this->imgLogo = $imgLogo;

        return $this;
    }

    public function getAddressSociete(): ?string
    {
        return $this->addressSociete;
    }

    public function setAddressSociete(?string $addressSociete): self
    {
        $this->addressSociete = $addressSociete;

        return $this;
    }

    public function getNameSociete(): ?string
    {
        return $this->nameSociete;
    }

    public function setNameSociete(?string $nameSociete): self
    {
        $this->nameSociete = $nameSociete;

        return $this;
    }
}
