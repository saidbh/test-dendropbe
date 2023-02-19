<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Swagger\Annotations as SWG;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ForfaitRepository")
 */
class Forfait
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"read", "auth"})
     * @SWG\Property(description="The unique identifer of the forfait")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     * @Groups({"read", "post", "auth"})
     * @SWG\Property(type="string", maxLength=50, enum={"Agile", "Flex"})
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
     * @ORM\OneToMany(targetEntity="App\Entity\Groupe", mappedBy="forfait")
     */
    private $groupes;

    /**
     * @ORM\Column(type="string", length=20)
     * @Groups({"post", "read", "auth"})
     * @SWG\Property(type="string")
     */
    private $codeForfait;


    public function __construct()
    {
        $this->groupes = new ArrayCollection();
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
     * @return Collection|Groupe[]
     */
    public function getGroupes(): Collection
    {
        return $this->groupes;
    }

    public function addGroupe(Groupe $groupe): self
    {
        if (!$this->groupes->contains($groupe)) {
            $this->groupes[] = $groupe;
            $groupe->setForfait($this);
        }

        return $this;
    }

    public function removeGroupe(Groupe $groupe): self
    {
        if ($this->groupes->contains($groupe)) {
            $this->groupes->removeElement($groupe);
            // set the owning side to null (unless already changed)
            if ($groupe->getForfait() === $this) {
                $groupe->setForfait(null);
            }
        }

        return $this;
    }

    public function getCodeForfait(): ?string
    {
        return $this->codeForfait;
    }

    public function setCodeForfait(string $codeForfait): self
    {
        $this->codeForfait = $codeForfait;

        return $this;
    }

}
