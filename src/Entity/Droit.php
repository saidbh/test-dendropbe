<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Swagger\Annotations as SWG;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DroitRepository")
 */
class Droit
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"read", "init"})
     * @SWG\Property(description="The unique identifer of the droit")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"read", "init", "post"})
     * @SWG\Property(type="string", maxLength=255)
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
     * @SWG\Property(type="datetime")
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="boolean", options={"default" : 0})
     */
    private $isInit;

    public function __construct()
    {
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

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
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
}
