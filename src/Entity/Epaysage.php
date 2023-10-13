<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Swagger\Annotations as SWG;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EpaysageRepository")
 */
class Epaysage
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"read"})
     * @SWG\Property(description="The unique identifer of the inventaire")
     */
    private $id;

    /**
     * @ORM\Column(type="polygon")
     * @Groups({"read"})
     * @SWG\Property(type="array", @SWG\Items(type="string"))
     */
    private $coord;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Essence", mappedBy="epaysage", orphanRemoval=true)
     * @Groups({"read"})
     */
    private $essence;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read"})
     */
    private $address;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read"})
     */
    private $ville;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read"})
     */
    private $pays;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"read"})
     */
    private $area;

    public function __construct()
    {
        $this->essence = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCoord()
    {
        return $this->coord;
    }

    public function setCoord($coord): self
    {
        $this->coord = $coord;
        return $this;
    }

    /**
     * @return Collection|Essence[]
     */
    public function getEssence(): Collection
    {
        return $this->essence;
    }

    public function addEssence(Essence $essence): self
    {
        if (!$this->essence->contains($essence)) {
            $this->essence[] = $essence;
            $essence->setEpaysage($this);
        }

        return $this;
    }

    public function removeEssence(Essence $essence): self
    {
        if ($this->essence->contains($essence)) {
            $this->essence->removeElement($essence);
            // set the owning side to null (unless already changed)
            if ($essence->getEpaysage() === $this) {
                $essence->setEpaysage(null);
            }
        }

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

    public function getPays(): ?string
    {
        return $this->pays;
    }

    public function setPays(?string $pays): self
    {
        $this->pays = $pays;

        return $this;
    }

    public function getArea(): ?float
    {
        return $this->area;
    }

    public function setArea(?float $area): self
    {
        $this->area = $area;

        return $this;
    }
}
