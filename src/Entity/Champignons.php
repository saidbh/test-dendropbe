<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Swagger\Annotations as SWG;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ChampignonsRepository")
 * @UniqueEntity("name")
 */
class Champignons
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"read", "init"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"read", "init"})
     * @SWG\Property(type="string", description="Champignon name")
     */
    private $name;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"read", "init"})
     * @SWG\Property(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"read", "init"})
     */
    private $updatedAt;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Essence", mappedBy="champignons")
     * @Groups({"init"})
     */
    private $essences;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Arbre", mappedBy="Champignons")
     * @Groups({"init"})
     * @SWG\Property(type="string", description="attaque sur feuillux")
     */
    private $arbres;

    /**
     * @ORM\Column(type="json", nullable=true)
     * @Groups({"read", "init"})
     * @SWG\Property(type="array", @SWG\Items(type="string"))
     */
    private $imgUrl = [];

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read", "init"})
     * @SWG\Property(type="string", description="attaque sur feuillux")
     */
    private $attaqueF;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read", "init"})
     * @SWG\Property(type="string", description="attaque sur resineux")
     */
    private $attaqueR;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Arbre", mappedBy="etatSanColletChampignons")
     * @Groups({"init"})
     */
    private $etatSanColletChampignonsArbres;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"default" : 0})
     */
    private $isDeleted;

    /**
     * @ORM\Column(type="string", length=255, options={"default" : "F"})
     * @Groups({"read", "init"})
     */
    private $category;

    public function __construct()
    {
        $this->essences = new ArrayCollection();
        $this->arbres = new ArrayCollection();
        $this->etatSanColletChampignonsArbres = new ArrayCollection();
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
            $essence->addChampignon($this);
        }

        return $this;
    }

    public function removeEssence(Essence $essence): self
    {
        if ($this->essences->contains($essence)) {
            $this->essences->removeElement($essence);
            $essence->removeChampignon($this);
        }

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
            $arbre->addChampignon($this);
        }

        return $this;
    }

    public function removeArbre(Arbre $arbre): self
    {
        if ($this->arbres->contains($arbre)) {
            $this->arbres->removeElement($arbre);
            $arbre->removeChampignon($this);
        }

        return $this;
    }

    public function getImgUrl(): ?array
    {
        return $this->imgUrl;
    }

    public function setImgUrl(?array $imgUrl): self
    {
        $this->imgUrl = $imgUrl;

        return $this;
    }

    public function getAttaqueF(): ?string
    {
        return $this->attaqueF;
    }

    public function setAttaqueF(?string $attaqueF): self
    {
        $this->attaqueF = $attaqueF;

        return $this;
    }

    public function getAttaqueR(): ?string
    {
        return $this->attaqueR;
    }

    public function setAttaqueR(?string $attaqueR): self
    {
        $this->attaqueR = $attaqueR;
        return $this;
    }

    /**
     * @return Collection|Arbre[]
     */
    public function getEtatSanColletChampignonsArbres(): Collection
    {
        return $this->etatSanColletChampignonsArbres;
    }

    public function addEtatSanColletChampignonsArbre(Arbre $etatSanColletChampignonsArbre): self
    {
        if (!$this->etatSanColletChampignonsArbres->contains($etatSanColletChampignonsArbre)) {
            $this->etatSanColletChampignonsArbres[] = $etatSanColletChampignonsArbre;
            $etatSanColletChampignonsArbre->addEtatSanColletChampignon($this);
        }

        return $this;
    }

    public function removeEtatSanColletChampignonsArbre(Arbre $etatSanColletChampignonsArbre): self
    {
        if ($this->etatSanColletChampignonsArbres->contains($etatSanColletChampignonsArbre)) {
            $this->etatSanColletChampignonsArbres->removeElement($etatSanColletChampignonsArbre);
            $etatSanColletChampignonsArbre->removeEtatSanColletChampignon($this);
        }

        return $this;
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

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): self
    {
        $this->category = $category;

        return $this;
    }
}
