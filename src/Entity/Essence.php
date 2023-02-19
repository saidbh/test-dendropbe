<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Swagger\Annotations as SWG;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EssenceRepository")
 */
class Essence
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"read"})
     * @SWG\Property(description="The unique identifer of the essence")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Espece", inversedBy="essences")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"read"})
     */
    private $espece;

    /**
     * @ORM\Column(type="array",  nullable=true)
     * @Groups({"read"})
     * @SWG\Property(type="array", @SWG\Items(type="string"))
     */
    private $critere = [];

    /**
     * @ORM\Column(type="integer",  nullable=true)
     * @Groups({"read"})
     */
    private $countSubject;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read"})
     */
    private $diametre;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read"})
     */
    private $hauteur;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read"})
     */
    private $stadeDev;

    /**
     * @ORM\Column(type="string", length=255,  nullable=true)
     * @Groups({"read"})
     */
    private $houppier;

    /**
     * @ORM\Column(type="array", nullable=true)
     * @Groups({"read"})
     * @SWG\Property(type="array", @SWG\Items(type="string"))
     */
    private $etatGeneral = [];

    /**
     * @ORM\Column(type="string", length=255,  nullable=true)
     * @Groups({"read"})
     */
    private $risque;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read"})
     */
    private $nbreSujetConcerne;

    /**
     * @ORM\Column(type="array",  nullable=true)
     * @Groups({"read"})
     * @SWG\Property(type="array", @SWG\Items(type="string"))
     */
    private $travaux = [];

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"read"})
     */
    private $dateTravaux;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"read"})
     */
    private $dateProVisite;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Epaysage", inversedBy="essence")
     * @ORM\JoinColumn(nullable=false)
     */
    private $epaysage;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     * @Groups({"read"})
     */
    private $codeSite;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     * @Groups({"read"})
     */
    private $numSujet;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"read"})
     */
    private $caract;

    /**
     * @ORM\Column(type="text", length=2000, nullable=true)
     * @Groups({"read"})
     */
    private $caractOther;

    /**
     * @ORM\Column(type="string", length=120, nullable=true)
     * @Groups({"read"})
     */
    private $domaine;

    /**
     * @ORM\Column(type="text", length=2000, nullable=true)
     * @Groups({"read"})
     */
    private $etatSanGeneralOther;

    /**
     * @ORM\Column(type="array", nullable=true)
     * @Groups({"read"})
     * @SWG\Property(type="array", @SWG\Items(type="string"))
     */
    private $nuisance = [];

    /**
     * @ORM\Column(type="array", nullable=true)
     * @Groups({"read"})
     * @SWG\Property(type="array", @SWG\Items(type="string"))
     */
    private $etatSanGeneralChampignons = [];

    /**
     * @ORM\Column(type="array", nullable=true)
     * @Groups({"read"})
     * @SWG\Property(type="array", @SWG\Items(type="string"))
     */
    private $etatSanGeneralParasite = [];

    /**
     * @ORM\Column(type="text", length=200, nullable=true)
     * @Groups({"read"})
     */
    private $critereOther;

    /**
     * @ORM\Column(type="array", nullable=true)
     * @Groups({"read"})
     * @SWG\Property(type="array", @SWG\Items(type="string"))
     */
    private $proximite = [];

    /**
     * @ORM\Column(type="text", length=2000, nullable=true)
     * @Groups({"read"})
     */
    private $proximiteOther;

    /**
     * @ORM\Column(type="array", nullable=true)
     * @Groups({"read"})
     * @SWG\Property(type="array", @SWG\Items(type="string"))
     */
    private $proximitewithDict = [];

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     * @Groups({"read"})
     */
    private $tauxFreq;

    /**
     * @ORM\Column(type="array", nullable=true)
     * @Groups({"read"})
     * @SWG\Property(type="array", @SWG\Items(type="string"))
     */
    private $typePassage = [];

    /**
     * @ORM\Column(type="text", length=2000, nullable=true)
     * @Groups({"read"})
     */
    private $typePassageOther;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     * @Groups({"read"})
     */
    private $accessibilite;

    /**
     * @ORM\Column(type="text", length=2000, nullable=true)
     * @Groups({"read"})
     */
    private $accessibiliteOther;

    /**
     * @ORM\Column(type="text", length=2000, nullable=true)
     * @Groups({"read"})
     */
    private $travauxOther;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read"})
     */
    private $travauxTypeIntervention;

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     * @Groups({"read"})
     */
    private $travauxCom;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"read"})
     */
    private $travauxSoin;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"read"})
     */
    private $travauxProtection;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read"})
     */
    private $img1;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read"})
     */
    private $img2;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read"})
     */
    private $img3;

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
     * @ORM\Column(type="array", nullable=true)
     * @Groups({"read"})
     * @SWG\Property(type="array", @SWG\Items(type="string"))
     */
    private $etatSanGeneral = [];

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"read"})
     */
    private $userEditedDateTravaux;

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

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     * @Groups({"read"})
     */
    private $abattage;

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     * @Groups({"read"})
     */
    private $etatSanGeneralParasiteAutres;

    /**
     * @ORM\Column(type="boolean", options={"default":true}, nullable=true)
     * @Groups({"read"})
     */
    private $statusTravaux;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read"})
     */
    private $etatSanGeneralChampignonsAutres;

    public function __construct()
    {

    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCritere(): ?array
    {
        return $this->critere;
    }

    public function setCritere(?array $critere): self
    {
        $this->critere = $critere;

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

    public function getDiametre(): ?int
    {
        return $this->diametre;
    }

    public function setDiametre(?int $diametre): self
    {
        $this->diametre = $diametre;

        return $this;
    }

    public function getHauteur(): ?int
    {
        return $this->hauteur;
    }

    public function setHauteur(?int $hauteur): self
    {
        $this->hauteur = $hauteur;

        return $this;
    }

    public function getStadeDev(): ?string
    {
        return $this->stadeDev;
    }

    public function setStadeDev(?string $stadeDev): self
    {
        $this->stadeDev = $stadeDev;

        return $this;
    }

    public function getHouppier(): ?string
    {
        return $this->houppier;
    }

    public function setHouppier(?string $houppier): self
    {
        $this->houppier = $houppier;
        return $this;
    }

    public function getEtatGeneral(): ?array
    {
        return $this->etatGeneral;
    }

    public function setEtatGeneral(?array $etatGeneral): self
    {
        $this->etatGeneral = $etatGeneral;

        return $this;
    }

    public function getRisque(): ?string
    {
        return $this->risque;
    }

    public function setRisque(?string $risque): self
    {
        $this->risque = $risque;

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

    public function getTravaux(): ?array
    {
        return $this->travaux;
    }

    public function setTravaux(?array $travaux): self
    {
        $this->travaux = $travaux;

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

    public function getEpaysage(): ?Epaysage
    {
        return $this->epaysage;
    }

    public function setEpaysage(?Epaysage $epaysage): self
    {
        $this->epaysage = $epaysage;

        return $this;
    }

    public function getCodeSite(): ?string
    {
        return $this->codeSite;
    }

    public function setCodeSite(?string $codeSite): self
    {
        $this->codeSite = $codeSite;

        return $this;
    }

    public function getNumSujet(): ?string
    {
        return $this->numSujet;
    }

    public function setNumSujet(?string $numSujet): self
    {
        $this->numSujet = $numSujet;

        return $this;
    }

    public function getCaract(): ?string
    {
        return $this->caract;
    }

    public function setCaract(?string $caract): self
    {
        $this->caract = $caract;

        return $this;
    }

    public function getCaractOther(): ?string
    {
        return $this->caractOther;
    }

    public function setCaractOther(?string $caractOther): self
    {
        $this->caractOther = $caractOther;

        return $this;
    }

    public function getDomaine(): ?string
    {
        return $this->domaine;
    }

    public function setDomaine(?string $domaine): self
    {
        $this->domaine = $domaine;

        return $this;
    }

    public function getEtatSanGeneralOther(): ?string
    {
        return $this->etatSanGeneralOther;
    }

    public function setEtatSanGeneralOther(?string $etatSanGeneralOther): self
    {
        $this->etatSanGeneralOther = $etatSanGeneralOther;

        return $this;
    }

    public function getNuisance(): ?array
    {
        return $this->nuisance;
    }

    public function setNuisance(?array $nuisance): self
    {
        $this->nuisance = $nuisance;

        return $this;
    }

    public function getEtatSanGeneralChampignons(): ?array
    {
        return $this->etatSanGeneralChampignons;
    }

    public function setEtatSanGeneralChampignons(?array $etatSanGeneralChampignons): self
    {
        $this->etatSanGeneralChampignons = $etatSanGeneralChampignons;

        return $this;
    }

    public function getEtatSanGeneralParasite(): ?array
    {
        return $this->etatSanGeneralParasite;
    }

    public function setEtatSanGeneralParasite(?array $etatSanGeneralParasite): self
    {
        $this->etatSanGeneralParasite = $etatSanGeneralParasite;

        return $this;
    }

    public function getCritereOther(): ?string
    {
        return $this->critereOther;
    }

    public function setCritereOther(?string $critereCom): self
    {
        $this->critereOther = $critereCom;

        return $this;
    }

    public function getProximite(): ?array
    {
        return $this->proximite;
    }

    public function setProximite(?array $proximite): self
    {
        $this->proximite = $proximite;

        return $this;
    }

    public function getProximiteOther(): ?string
    {
        return $this->proximiteOther;
    }

    public function setProximiteOther(?string $proximiteOther): self
    {
        $this->proximiteOther = $proximiteOther;

        return $this;
    }

    public function getProximitewithDict(): ?array
    {
        return $this->proximitewithDict;
    }

    public function setProximitewithDict(?array $proximitewithDict): self
    {
        $this->proximitewithDict = $proximitewithDict;

        return $this;
    }

    public function getTauxFreq(): ?string
    {
        return $this->tauxFreq;
    }

    public function setTauxFreq(?string $tauxFreq): self
    {
        $this->tauxFreq = $tauxFreq;

        return $this;
    }

    public function getTypePassage(): ?array
    {
        return $this->typePassage;
    }

    public function setTypePassage(?array $typePassage): self
    {
        $this->typePassage = $typePassage;

        return $this;
    }

    public function getTypePassageOther(): ?string
    {
        return $this->typePassageOther;
    }

    public function setTypePassageOther(?string $typePassageOther): self
    {
        $this->typePassageOther = $typePassageOther;

        return $this;
    }

    public function getAccessibilite(): ?string
    {
        return $this->accessibilite;
    }

    public function setAccessibilite(?string $accessibilite): self
    {
        $this->accessibilite = $accessibilite;

        return $this;
    }

    public function getAccessibiliteOther(): ?string
    {
        return $this->accessibiliteOther;
    }

    public function setAccessibiliteOther(?string $accessibiliteOther): self
    {
        $this->accessibiliteOther = $accessibiliteOther;

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

    public function getTravauxTypeIntervention(): ?string
    {
        return $this->travauxTypeIntervention;
    }

    public function setTravauxTypeIntervention(?string $travauxTypeIntervention): self
    {
        $this->travauxTypeIntervention = $travauxTypeIntervention;

        return $this;
    }

    public function getTravauxCom(): ?string
    {
        return $this->travauxCom;
    }

    public function setTravauxCom(?string $travauxCom): self
    {
        $this->travauxCom = $travauxCom;

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

    public function getImg1(): ?string
    {
        return $this->img1;
    }

    public function setImg1(?string $img1): self
    {
        $this->img1 = $img1;

        return $this;
    }

    public function getImg2(): ?string
    {
        return $this->img2;
    }

    public function setImg2(?string $img2): self
    {
        $this->img2 = $img2;

        return $this;
    }

    public function getImg3(): ?string
    {
        return $this->img3;
    }

    public function setImg3(?string $img3): self
    {
        $this->img3 = $img3;

        return $this;
    }

    public function getVarietyGrade(): ?int
    {
        return $this->varietyGrade;
    }

    public function setVarietyGrade(?int $varietyGrade): self
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

    public function getEtatSanGeneral(): ?array
    {
        return $this->etatSanGeneral;
    }

    public function setEtatSanGeneral(?array $etatSanGeneral): self
    {
        $this->etatSanGeneral = $etatSanGeneral;

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

    public function getAbattage(): ?string
    {
        return $this->abattage;
    }

    public function setAbattage(?string $abattage): self
    {
        $this->abattage = $abattage;

        return $this;
    }

    public function getEtatSanGeneralParasiteAutres(): ?string
    {
        return $this->etatSanGeneralParasiteAutres;
    }

    public function setEtatSanGeneralParasiteAutres(?string $etatSanGeneralParasiteAutres): self
    {
        $this->etatSanGeneralParasiteAutres = $etatSanGeneralParasiteAutres;

        return $this;
    }

    public function getStatusTravaux(): ?bool
    {
        return $this->statusTravaux;
    }

    public function setStatusTravaux(bool $statusTravaux): self
    {
        $this->statusTravaux = $statusTravaux;

        return $this;
    }

    public function getEtatSanGeneralChampignonsAutres(): ?string
    {
        return $this->etatSanGeneralChampignonsAutres;
    }

    public function setEtatSanGeneralChampignonsAutres(?string $etatSanGeneralChampignonsAutres): self
    {
        $this->etatSanGeneralChampignonsAutres = $etatSanGeneralChampignonsAutres;

        return $this;
    }
}
