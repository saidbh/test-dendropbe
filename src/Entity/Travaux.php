<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TravauxRepository")
 */
class Travaux
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $arbreId;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $essenceId;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $epaysageId;

    /**
     * @ORM\Column(type="integer")
     */
    private $inventaireId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $abattage;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $travauxColletMultiple = [];

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $travauxTroncMultiple = [];

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $travauxHouppierMultiple = [];

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $travauxCommentaire;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $travauxHouppierOther;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $travauxColletOther;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $travauxTroncOther;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $travauxTroncProtection;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $travaux = [];

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $travauxOther;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $travauxSoin;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $travauxProtection;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $nbreSujetConcerne;

    /**
     * @ORM\Column(type="boolean", options={"default":true}, nullable=true)
     */
    private $statusTravaux;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $dateTravaux;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $dateProVisite;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $userEditedDateTravaux;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getArbreId(): ?int
    {
        return $this->arbreId;
    }

    public function setArbreId(?int $arbreId): self
    {
        $this->arbreId = $arbreId;

        return $this;
    }

    public function getEssenceId(): ?int
    {
        return $this->essenceId;
    }

    public function setEssenceId(?int $essenceId): self
    {
        $this->essenceId = $essenceId;

        return $this;
    }

    public function getEpaysageId(): ?int
    {
        return $this->epaysageId;
    }

    public function setEpaysageId(?int $epaysageId): self
    {
        $this->epaysageId = $epaysageId;

        return $this;
    }

    public function getInventaireId(): ?int
    {
        return $this->inventaireId;
    }

    public function setInventaireId(int $inventaireId): self
    {
        $this->inventaireId = $inventaireId;

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

    public function getAbattage(): ?string
    {
        return $this->abattage;
    }

    public function setAbattage(?string $abattage): self
    {
        $this->abattage = $abattage;

        return $this;
    }

    public function getTravauxColletMultiple(): ?array
    {
        return $this->travauxColletMultiple;
    }

    public function setTravauxColletMultiple(?array $travauxColletMultiple): self
    {
        $this->travauxColletMultiple = $travauxColletMultiple;

        return $this;
    }

    public function getTravauxTroncMultiple(): ?array
    {
        return $this->travauxTroncMultiple;
    }

    public function setTravauxTroncMultiple(?array $travauxTroncMultiple): self
    {
        $this->travauxTroncMultiple = $travauxTroncMultiple;

        return $this;
    }

    public function getTravauxHouppierMultiple(): ?array
    {
        return $this->travauxHouppierMultiple;
    }

    public function setTravauxHouppierMultiple(?array $travauxHouppierMultiple): self
    {
        $this->travauxHouppierMultiple = $travauxHouppierMultiple;

        return $this;
    }

    public function getTravauxCommentaire(): ?string
    {
        return $this->travauxCommentaire;
    }

    public function setTravauxCommentaire(?string $travauxCommentaire): self
    {
        $this->travauxCommentaire = $travauxCommentaire;

        return $this;
    }

    public function getTravauxHouppierOther(): ?string
    {
        return $this->travauxHouppierOther;
    }

    public function setTravauxHouppierOther(?string $travauxHouppierOther): self
    {
        $this->travauxHouppierOther = $travauxHouppierOther;

        return $this;
    }

    public function getTravauxColletOther(): ?string
    {
        return $this->travauxColletOther;
    }

    public function setTravauxColletOther(?string $travauxColletOther): self
    {
        $this->travauxColletOther = $travauxColletOther;

        return $this;
    }

    public function getTravauxTroncOther(): ?string
    {
        return $this->travauxTroncOther;
    }

    public function setTravauxTroncOther(?string $travauxTroncOther): self
    {
        $this->travauxTroncOther = $travauxTroncOther;

        return $this;
    }

    public function getTravauxTroncProtection(): ?string
    {
        return $this->travauxTroncProtection;
    }

    public function setTravauxTroncProtection(?string $travauxTroncProtection): self
    {
        $this->travauxTroncProtection = $travauxTroncProtection;

        return $this;
    }

    public function getTravaux(): ?array
    {
        return $this->travaux;
    }

    public function setTravaux(?array $travaux): self
    {
        $this->travaux = $travaux;

        return $this;
    }

    public function getTravauxOther(): ?string
    {
        return $this->travauxOther;
    }

    public function setTravauxOther(?string $travauxOther): self
    {
        $this->travauxOther = $travauxOther;

        return $this;
    }

    public function getTravauxSoin(): ?string
    {
        return $this->travauxSoin;
    }

    public function setTravauxSoin(?string $travauxSoin): self
    {
        $this->travauxSoin = $travauxSoin;

        return $this;
    }

    public function getTravauxProtection(): ?string
    {
        return $this->travauxProtection;
    }

    public function setTravauxProtection(?string $travauxProtection): self
    {
        $this->travauxProtection = $travauxProtection;

        return $this;
    }

    public function getNbreSujetConcerne(): ?int
    {
        return $this->nbreSujetConcerne;
    }

    public function setNbreSujetConcerne(?int $nbreSujetConcerne): self
    {
        $this->nbreSujetConcerne = $nbreSujetConcerne;

        return $this;
    }

    public function getStatusTravaux(): ?bool
    {
        return $this->statusTravaux;
    }

    public function setStatusTravaux(?bool $statusTravaux): self
    {
        $this->statusTravaux = $statusTravaux;

        return $this;
    }

    public function getDateTravaux(): ?string
    {
        return $this->dateTravaux;
    }

    public function setDateTravaux(?string $dateTravaux): self
    {
        $this->dateTravaux = $dateTravaux;

        return $this;
    }

    public function getDateProVisite(): ?string
    {
        return $this->dateProVisite;
    }

    public function setDateProVisite(?string $dateProVisite): self
    {
        $this->dateProVisite = $dateProVisite;

        return $this;
    }

    public function getUserEditedDateTravaux(): ?\DateTimeInterface
    {
        return $this->userEditedDateTravaux;
    }

    public function setUserEditedDateTravaux(?\DateTimeInterface $userEditedDateTravaux): self
    {
        $this->userEditedDateTravaux = $userEditedDateTravaux;

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
