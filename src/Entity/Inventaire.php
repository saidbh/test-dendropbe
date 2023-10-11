<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Swagger\Annotations as SWG;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\InventaireRepository")
 */
class Inventaire
{
    const TYPE_INVENTORY = ['ARBRE', 'EB', 'ALIGNEMENT'];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"read"})
     * @SWG\Property(description="The unique identifer of the inventaire")
     */
    private $id;

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
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="inventaires")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"read"})
     */
    private $user;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Arbre", orphanRemoval=true)
     */
    private $arbre;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Epaysage", orphanRemoval=true)
     */
    private $epaysage;

    /**
     * @ORM\Column(type="boolean", options={"default" : 0})
     * @Groups({"read"})
     */
    private $isFinished;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"read"})
     */
    private $type;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"read"})
     */
    private $varietyGrade;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read"})
     */
    private $healthIndex;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read"})
     */
    private $aestheticIndex;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read"})
     */
    private $locationIndex;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"read"})
     */
    private $address;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"read"})
     */
    private $ville;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read"})
     */
    private $aestheticColumn;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read"})
     */
    private $healthColumn;


    public function getId(): ?int
    {
        return $this->id;
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getArbre(): ?Arbre
    {
        return $this->arbre;
    }

    public function setArbre(?Arbre $arbre): self
    {
        $this->arbre = $arbre;
        return $this;
    }

    public function getEpaysage(): ?Epaysage
    {
        return $this->epaysage;
    }

    public function setEpaysage(?Epaysage $epaysage): self
    {
        $this->epaysage = $epaysage;
        return $this;
    }

    public function getIsFinished(): ?bool
    {
        return $this->isFinished;
    }

    public function setIsFinished(bool $isFinished): self
    {
        $this->isFinished = $isFinished;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getVarietyGrade(): ?float
    {
        return $this->varietyGrade;
    }

    public function setVarietyGrade(?float $varietyGrade): self
    {
        $this->varietyGrade = $varietyGrade;

        return $this;
    }

    public function getHealthIndex(): ?int
    {
        return $this->healthIndex;
    }

    public function setHealthIndex(?int $healthIndex): self
    {
        $this->healthIndex = $healthIndex;

        return $this;
    }

    public function getAestheticIndex(): ?int
    {
        return $this->aestheticIndex;
    }

    public function setAestheticIndex(?int $aestheticIndex): self
    {
        $this->aestheticIndex = $aestheticIndex;

        return $this;
    }

    public function getLocationIndex(): ?int
    {
        return $this->locationIndex;
    }

    public function setLocationIndex(?int $locationIndex): self
    {
        $this->locationIndex = $locationIndex;
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

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(?string $ville): self
    {
        $this->ville = $ville;

        return $this;
    }

    public function getAestheticColumn(): ?int
    {
        return $this->aestheticColumn;
    }

    public function setAestheticColumn(?int $aestheticColumn): self
    {
        $this->aestheticColumn = $aestheticColumn;

        return $this;
    }

    public function getHealthColumn(): ?int
    {
        return $this->healthColumn;
    }

    public function setHealthColumn(?int $healthColumn): self
    {
        $this->healthColumn = $healthColumn;

        return $this;
    }

}
