<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Swagger\Annotations as SWG;
/**
 * @ORM\Entity(repositoryClass="App\Repository\EspeceRepository")
 */
class Espece
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"read"})
     * @SWG\Property(description="The unique identifer of Espece")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"read"})
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read"})
     */
    private $imgUrl;


    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Arbre", mappedBy="espece")
     */
    private $arbres;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read"})
     */
    private $categorie;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Essence", mappedBy="espece")
     */
    private $essences;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read"})
     */
    private $cultivar;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read"})
     */
    private $nomFr;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read"})
     */
    private $genre;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"read"})
     */
    private $tarif;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     */
    private $userAdded;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"read"})
     */
    private $createdAt;

    /**
     *  @Groups({"read"})
     */
    protected $indiceEspece;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"default" : 0})
     */
    private $isDeleted;

    public function __construct()
    {
        $this->arbres = new ArrayCollection();
        $this->essences = new ArrayCollection();
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

    public function getImgUrl(): ?string
    {
        return $this->imgUrl;
    }

    public function setImgUrl(?string $imgUrl): self
    {
        $this->imgUrl = $imgUrl;

        return $this;
    }

    /**
     * @return Collection|Arbre[]
     */
    public function getArbres(): Collection
    {
        return $this->arbres;
    }

    public function addArbre(Arbre $arbre): self
    {
        if (!$this->arbres->contains($arbre)) {
            $this->arbres[] = $arbre;
            $arbre->setEspece($this);
        }

        return $this;
    }

    public function removeArbre(Arbre $arbre): self
    {
        if ($this->arbres->contains($arbre)) {
            $this->arbres->removeElement($arbre);
            // set the owning side to null (unless already changed)
            if ($arbre->getEspece() === $this) {
                $arbre->setEspece(null);
            }
        }
        return $this;
    }

    public function getCategorie(): ?string
    {
        return $this->categorie;
    }

    public function setCategorie(?string $categorie): self
    {
        $this->categorie = $categorie;
        return $this;
    }

    /**
     * @return Collection|Essence[]
     */
    public function getEssences(): Collection
    {
        return $this->essences;
    }

    public function addEssence(Essence $essence): self
    {
        if (!$this->essences->contains($essence)) {
            $this->essences[] = $essence;
            $essence->setEspece($this);
        }

        return $this;
    }

    public function removeEssence(Essence $essence): self
    {
        if ($this->essences->contains($essence)) {
            $this->essences->removeElement($essence);
            // set the owning side to null (unless already changed)
            if ($essence->getEspece() === $this) {
                $essence->setEspece(null);
            }
        }

        return $this;
    }

    public function getCultivar(): ?string
    {
        return $this->cultivar == null ? '' : $this->cultivar;
    }

    public function setCultivar(?string $cultivar): self
    {
        $this->cultivar = $cultivar;

        return $this;
    }

    public function getNomFr(): ?string
    {
        return $this->nomFr;
    }

    public function setNomFr(?string $nomFr): self
    {
        $this->nomFr = $nomFr;

        return $this;
    }

    public function getGenre(): ?string
    {
        return $this->genre;
    }

    public function setGenre(?string $genre): self
    {
        $this->genre = $genre;

        return $this;
    }

    public function getTarif(): ?float
    {
        return !$this->tarif ? 0 : $this->tarif;
    }

    public function setTarif(?float $tarif): self
    {
        $this->tarif = $tarif;

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

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getIndiceEspece(): ?float {
        return  ($this->getTarif() && is_numeric($this->getTarif())) ? $this->getTarif() / 10 : null;
    }

    public function getIsDeleted(): ?bool
    {
        return $this->isDeleted;
    }

    public function setIsDeleted(?bool $isDeleted): self
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

}
