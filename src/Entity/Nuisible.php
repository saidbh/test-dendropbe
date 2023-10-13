<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
/**
 * @ORM\Entity(repositoryClass="App\Repository\NuisibleRepository")
 */
class Nuisible
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
     */
    private $name;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"read", "init"})
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"read", "init"})
     */
    private $updatedAt;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Arbre", mappedBy="nuisibles")
     * @Groups({"init"})
     */
    private $arbres;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Essence", mappedBy="nuisibles")
     * @Groups({"init"})
     */
    private $essences;

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
            $arbre->addNuisible($this);
        }

        return $this;
    }

    public function removeArbre(Arbre $arbre): self
    {
        if ($this->arbres->contains($arbre)) {
            $this->arbres->removeElement($arbre);
            $arbre->removeNuisible($this);
        }

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
            $essence->addNuisible($this);
        }

        return $this;
    }

    public function removeEssence(Essence $essence): self
    {
        if ($this->essences->contains($essence)) {
            $this->essences->removeElement($essence);
            $essence->removeNuisible($this);
        }

        return $this;
    }
}
