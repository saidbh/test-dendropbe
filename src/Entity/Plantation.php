<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Swagger\Annotations as SWG;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PlantationRepository")
 */
class Plantation
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"read"})
     * @SWG\Property(description="The unique identifer of plantation")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"read"})
     */
    private $hauteur;
    /**
     * @ORM\Column(type="integer")
     * @Groups({"read"})
     */
    private $diametre;


    /**
     * @ORM\Column(type="integer")
     * @Groups({"read"})
     */
    private $countSubject;


    /**
     * @ORM\Column(type="datetime")
     * @Groups({"read"})
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Espece")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"read"})
     */
    private $espece;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"read"})
     */
    private $updatedAt;


    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"read"})
     */
    private $ville;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read"})
     */
    private $pays;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"read"})
     */
    private $address;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"read"})
     */
    private $dateEcheance;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateReport;


    /**
     * @ORM\OneToOne(targetEntity="App\Entity\User", cascade={"persist", "remove"})
     */
    private $userValidate;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $userAdded;

    /**
     * @ORM\OneToOne(targetEntity=Inventaire::class, cascade={"persist", "remove"})
     * @Groups({"readplantation"})
     */
    private $inventory;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHauteur(): ?int
    {
        return $this->hauteur;
    }

    public function setHauteur(int $hauteur): self
    {
        $this->hauteur = $hauteur;

        return $this;
    }

    public function getDiametre(): ?int
    {
        return $this->diametre;
    }

    public function setDiametre(int $diametre): self
    {
        $this->diametre = $diametre;

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

    public function getEspece(): ?Espece
    {
        return $this->espece;
    }

    public function setEspece(?Espece $espece): self
    {
        $this->espece = $espece;

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

    public function getCountSubject(): ?int
    {
        return $this->countSubject;
    }

    public function setCountSubject(int $countSubject): self
    {
        $this->countSubject = $countSubject;

        return $this;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(string $ville): self
    {
        $this->ville = $ville;

        return $this;
    }

    public function getPays(): ?string
    {
        return $this->pays;
    }

    public function setPays(?string $pays): self
    {
        $this->pays = $pays;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

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

    public function getDateReport(): ?\DateTimeInterface
    {
        return $this->dateReport;
    }

    public function setDateReport(?\DateTimeInterface $dateReport): self
    {
        $this->dateReport = $dateReport;

        return $this;
    }

    public function getUserValidate(): ?User
    {
        return $this->userValidate;
    }

    public function setUserValidate(?User $userValidate): self
    {
        $this->userValidate = $userValidate;
        return $this;
    }

    public function getUserAdded(): ?User
    {
        return $this->userAdded;
    }

    public function setUserAdded(?User $userAdded): self
    {
        $this->userAdded = $userAdded;

        return $this;
    }

    public function getInventory(): ?Inventaire
    {
        return $this->inventory;
    }

    public function setInventory(?Inventaire $inventory): self
    {
        $this->inventory = $inventory;

        return $this;
    }
}
