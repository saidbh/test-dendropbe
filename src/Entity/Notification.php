<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\NotificationRepository")
 */
class Notification
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups("default")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups("default")
     */
    private $groupeId;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups("default")
     */
    private $type;

    /**
     * @ORM\Column(type="integer")
     * @Groups("default")
     */
    private $statut;

    /**
     * @ORM\Column(type="datetime")
     * @Groups("default")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups("default")
     */
    private $updatedAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGroupeId(): ?int
    {
        return $this->groupeId;
    }

    public function setGroupeId(?int $groupeId): self
    {
        $this->groupeId = $groupeId;

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

    public function getStatut(): ?int
    {
        return $this->statut;
    }

    public function setStatut(int $statut): self
    {
        $this->statut = $statut;

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
}
