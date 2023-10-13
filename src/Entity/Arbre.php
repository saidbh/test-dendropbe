<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Swagger\Annotations as SWG;
use Symfony\Component\Serializer\Annotation\Groups;
use CrEOF\Spatial\PHP\Types\Geometry\Point;
/**
 * @ORM\Entity(repositoryClass="App\Repository\ArbreRepository")
 */
class Arbre
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"read"})
     * @SWG\Property(description="The unique identifer of the user")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Espece", inversedBy="arbres")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"read"})
     */
    private $espece;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read"})
     */
    private $diametre;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"read"})
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"read"})
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="point")
     * @var Point
     */
    private $coord;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read"})
     */
    private $codeSite;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read"})
     */
    private $numSujet;

    /**
     * @ORM\Column(type="array", nullable=true)
     * @Groups({"read"})
     * @SWG\Property(type="array", @SWG\Items(type="string"))
     */
    private $critere = [];

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read"})
     */
    private $implantation;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read"})
     */
    private $domaine;

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
    private $proximite = [];

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
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
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read"})
     */
    private $accessibilite;

    // PREND DEUX VALEURS SIMPLE ET ENUM
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read"})
     */
    private $abattage;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read"})
     */
    private $travauxCollet;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read"})
     */
    private $travauxTronc;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read"})
     */
    private $travauxHouppier;

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
     * @ORM\Column(type="text", length=2000, nullable=true)
     */
    private $comProVisite;

    /**
     * @ORM\Column(type="array", nullable=true)
     * @Groups({"read"})
     * @SWG\Property(type="array", @SWG\Items(type="string"))
     */
    private $caractPied = [];

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read"})
     */
    private $caractTronc;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read"})
     */
    private $hauteur;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read"})
     */
    private $portArbre;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read"})
     */
    private $stadeDev;

    /**
     * @ORM\Column(type="array", nullable=true, nullable=true)
     * @Groups({"read"})
     * @SWG\Property(type="array", @SWG\Items(type="string"))
     */
    private $etatSanCollet = [];

    /**
     * @ORM\Column(type="array", nullable=true, nullable=true)
     * @Groups({"read"})
     * @SWG\Property(type="array", @SWG\Items(type="string"))
     */
    private $etatSanTronc = [];

    /**
     * @ORM\Column(type="array", nullable=true, nullable=true)
     * @Groups({"read"})
     * @SWG\Property(type="array", @SWG\Items(type="string"))
     */
    private $etatSanHouppier = [];

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Nuisible", inversedBy="arbres")
     * @Groups({"read"})
     */
    private $nuisanceNuisibles;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, nullable=true)
     * @Groups({"read"})
     */
    private $address;

    /**
     * @ORM\Column(type="text", nullable=true, length=2000)
     */
    private $comAccess;

    /**
     * @ORM\Column(type="array", nullable=true)
     * @Groups({"read"})
     * @SWG\Property(type="array", @SWG\Items(type="string"))
     */
    private $dict = [];

    /**
     * @ORM\Column(type="json_array")
     * @Groups({"read"})
     * @SWG\Property(type="array", @SWG\Items(type="string"))
     */
    private $risque;

    /**
     * @ORM\Column(type="text", length=2000, nullable=true)
     * @Groups({"read"})
     */
    private $proximiteOther;

    /**
     * @ORM\Column(type="text", length=2000, nullable=true)
     * @Groups({"read"})
     */
    private $accessibiliteOther;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"read"})
     */
    private $caractPiedOther;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read"})
     * @SWG\Property(type="array", @SWG\Items(type="string"))
     */
    private $caractTroncMultiples;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read"})
     */
    private $etatSanColletCavite;

    /**
     * @ORM\Column(type="array", nullable=true)
     * @Groups({"read"})
     * @SWG\Property(type="array", @SWG\Items(type="string"))
     */
    private $etatSanColletChampignons = [];

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read"})
     */
    private $etatSanTroncCavite;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read"})
     */
    private $etatSanTroncCorpsEtranger;

    /**
     * @ORM\Column(type="array", nullable=true)
     * @Groups({"read"})
     * @SWG\Property(type="array", @SWG\Items(type="string"))
     */
    private $etatSanTroncChampignons = [];

    /**
     * @ORM\Column(type="array", nullable=true)
     * @Groups({"read"})
     * @SWG\Property(type="array", @SWG\Items(type="string"))
     */
    private $etatSanTroncNuisibles = [];

    /**
     * @ORM\Column(type="array", nullable=true)
     * @Groups({"read"})
     * @SWG\Property(type="array", @SWG\Items(type="string"))
     */
    private $etatSanHouppierChampignons = [];

    /**
     * @ORM\Column(type="array", nullable=true)
     * @Groups({"read"})
     * @SWG\Property(type="array", @SWG\Items(type="string"))
     */
    private $etatSanHouppierNuisibles = [];

    /**
     * @ORM\Column(type="array", nullable=true)
     * @Groups({"read"})
     * @SWG\Property(type="array", @SWG\Items(type="string"))
     */
    private $risqueGeneral = [];

    /**
     * @ORM\Column(type="text", length=2000, nullable=true)
     */
    private $travauxCommentaire;

    /**
     * @ORM\Column(type="text", length=2000, nullable=true)
     * @Groups({"read"})
     */
    private $critereOther;

    /**
     * @ORM\Column(type="text", length=2000, nullable=true)
     * @Groups({"read"})
     */
    private $risqueGeneralOther;

    /**
     * @ORM\Column(type="text", length=2000, nullable=true)
     * @Groups({"read"})
     */
    private $typePassageOther;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $typeIntervention;

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
     * @ORM\Column(type="text", length=2000, nullable=true)
     * @Groups({"read"})
     */
    private $travauxTroncOther;

    /**
     * @ORM\Column(type="text", length=2000, nullable=true)
     * @Groups({"read"})
     */
    private $travauxColletOther;

    /**
     * @ORM\Column(type="string", length=2000, nullable=true)
     * @Groups({"read"})
     */
    private $travauxHouppierOther;

    /**
     * @ORM\Column(type="array", nullable=true)
     * @Groups({"read"})
     * @SWG\Property(type="array", @SWG\Items(type="string"))
     */
    private $proximiteWithDict = [];

    /**
     * @ORM\Column(type="string", length=155, nullable=true)
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
    private $travauxTroncProtection;

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
     * @ORM\Column(type="string", length=150, nullable=true)
     * @Groups({"read"})
     */
    private $etatSanTroncNuisiblesAutres;

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     * @Groups({"read"})
     */
    private $etatSanHouppierNuisiblesAutres;

    /**
     * @ORM\Column(type="array", nullable=true)
     * @Groups({"read"})
     * @SWG\Property(type="array", @SWG\Items(type="string"))
     */
    private $travauxColletMultiple = [];

    /**
     * @ORM\Column(type="array", nullable=true)
     * @Groups({"read"})
     * @SWG\Property(type="array", @SWG\Items(type="string"))
     */
    private $travauxTroncMultiple = [];

    /**
     * @ORM\Column(type="array", nullable=true)
     * @Groups({"read"})
     * @SWG\Property(type="array", @SWG\Items(type="string"))
     */
    private $travauxHouppierMultiple = [];

    /**
     * @ORM\Column(type="boolean", options={"default":true}, nullable=true)
     * @Groups({"read"})
     */
    private $statusTravaux;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read"})
     */
    private $etatSanColletChampignonsAutres;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read"})
     */
    private $etatSanTroncChampignonsAutres;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read"})
     */
    private $etatSanHouppierChampignonsAutres;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $etatSanColletOther;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $etatSanTroncOther;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $etatSanHouppierOther;

    /**
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    private $etatSanGeneralOther;

    public function __construct()
    {
        $this->nuisanceNuisibles = new ArrayCollection();
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

    public function getDiametre(): ?int
    {
        return $this->diametre;
    }

    public function setDiametre(?int $diametre): self
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

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getCoord()
    {
        return $this->coord;
    }

    public function setCoord(Point $coord): self
    {
        $this->coord = $coord;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

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

    public function getCritere(): ?array
    {
        return $this->critere;
    }

    public function setCritere(?array $critere): self
    {
        $this->critere = $critere;

        return $this;
    }

    public function getImplantation(): ?string
    {
        return $this->implantation;
    }

    public function setImplantation(?string $implantation): self
    {
        $this->implantation = $implantation;

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

    public function getNuisance(): ?array
    {
        return $this->nuisance;
    }

    public function setNuisance(?array $nuisance): self
    {
        $this->nuisance = $nuisance;

        return $this;
    }

    public function getProximite(): ?array
    {
        return $this->proximite;
    }

    public function setProximite(array $proximite): self
    {
        $this->proximite = $proximite;

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

    public function getAccessibilite(): ?string
    {
        return $this->accessibilite;
    }

    public function setAccessibilite(?string $accessibilite): self
    {
        $this->accessibilite = $accessibilite;

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

    public function getTravauxCollet(): ?string
    {
        return $this->travauxCollet;
    }

    public function setTravauxCollet(?string $travauxCollet): self
    {
        $this->travauxCollet = $travauxCollet;

        return $this;
    }

    public function getTravauxTronc(): ?string
    {
        return $this->travauxTronc;
    }

    public function setTravauxTronc(?string $travauxTronc): self
    {
        $this->travauxTronc = $travauxTronc;

        return $this;
    }

    public function getTravauxHouppier(): ?string
    {
        return $this->travauxHouppier;
    }

    public function setTravauxHouppier(?string $travauxHouppier): self
    {
        $this->travauxHouppier = $travauxHouppier;

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

    public function getComProVisite(): ?string
    {
        return $this->comProVisite;
    }

    public function setComProVisite(?string $comProVisite): self
    {
        $this->comProVisite = $comProVisite;

        return $this;
    }

    public function getCaractPied(): ?array
    {
        return $this->caractPied;
    }

    public function setCaractPied(?array $caractPied): self
    {
        $this->caractPied = $caractPied;

        return $this;
    }

    public function getCaractTronc(): ?string
    {
        return $this->caractTronc;
    }

    public function setCaractTronc(?string $caractTronc): self
    {
        $this->caractTronc = $caractTronc;

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

    public function getPortArbre(): ?string
    {
        return $this->portArbre;
    }

    public function setPortArbre(?string $portArbre): self
    {
        $this->portArbre = $portArbre;

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

    public function getEtatSanCollet(): ?array
    {
        return $this->etatSanCollet;
    }

    public function setEtatSanCollet(?array $etatSanCollet): self
    {
        $this->etatSanCollet = $etatSanCollet;

        return $this;
    }

    public function getEtatSanTronc(): ?array
    {
        return $this->etatSanTronc;
    }

    public function setEtatSanTronc(?array $etatSanTronc): self
    {
        $this->etatSanTronc = $etatSanTronc;

        return $this;
    }

    public function getEtatSanHouppier(): ?array
    {
        return $this->etatSanHouppier;
    }

    public function setEtatSanHouppier(?array $etatSanHouppier): self
    {
        $this->etatSanHouppier = $etatSanHouppier;
        return $this;
    }


    /**
     * @return Collection|Nuisible[]
     */
    public function getNuisanceNuisibles(): Collection
    {
        return $this->nuisanceNuisibles;
    }

    public function addNuisanceNuisibles(Nuisible $nuisible): self
    {
        if (!$this->nuisanceNuisibles->contains($nuisible)) {
            $this->nuisanceNuisibles[] = $nuisible;
        }

        return $this;
    }

    public function removeNuisanceNuisibles(Nuisible $nuisible): self
    {
        if ($this->nuisanceNuisibles->contains($nuisible)) {
            $this->nuisanceNuisibles->removeElement($nuisible);
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

    public function getComAccess(): ?string
    {
        return $this->comAccess;
    }

    public function setComAccess(?string $comAccess): self
    {
        $this->comAccess = $comAccess;

        return $this;
    }

    public function getDict(): ?array
    {
        return $this->dict;
    }

    public function setDict(?array $dict): self
    {
        $this->dict = $dict;

        return $this;
    }

    public function getRisque()
    {
        return $this->risque;
    }

    public function setRisque($risque): self
    {
        $this->risque = $risque;

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

    public function getAccessibiliteOther(): ?string
    {
        return $this->accessibiliteOther;
    }

    public function setAccessibiliteOther(?string $accessibiliteOther): self
    {
        $this->accessibiliteOther = $accessibiliteOther;

        return $this;
    }

    public function getCaractPiedOther(): ?string
    {
        return $this->caractPiedOther;
    }

    public function setCaractPiedOther(?string $caractPiedOther): self
    {
        $this->caractPiedOther = $caractPiedOther;

        return $this;
    }

    public function getCaractTroncMultiples(): ?int
    {
        return $this->caractTroncMultiples;
    }

    public function setCaractTroncMultiples(?int $caractTroncMultiples): self
    {
        $this->caractTroncMultiples = $caractTroncMultiples;

        return $this;
    }

    public function getEtatSanColletCavite(): ?int
    {
        return $this->etatSanColletCavite;
    }

    public function setEtatSanColletCavite(?int $etatSanColletCavite): self
    {
        $this->etatSanColletCavite = $etatSanColletCavite;

        return $this;
    }

    public function getEtatSanColletChampignons(): ?array
    {
        return $this->etatSanColletChampignons;
    }

    public function setEtatSanColletChampignons(?array $etatSanColletChampignons): self
    {

        $this->etatSanColletChampignons = $etatSanColletChampignons;
        return $this;
    }

    public function getEtatSanTroncCavite(): ?int
    {
        return $this->etatSanTroncCavite;
    }

    public function setEtatSanTroncCavite(?int $etatSanTroncCavite): self
    {
        $this->etatSanTroncCavite = $etatSanTroncCavite;

        return $this;
    }

    public function getEtatSanTroncCorpsEtranger(): ?string
    {
        return $this->etatSanTroncCorpsEtranger;
    }

    public function setEtatSanTroncCorpsEtranger(?string $etatSanTroncCorpsEtranger): self
    {
        $this->etatSanTroncCorpsEtranger = $etatSanTroncCorpsEtranger;

        return $this;
    }

    public function getEtatSanTroncChampignons(): ?array
    {
        return $this->etatSanTroncChampignons;
    }

    public function setEtatSanTroncChampignons(?array $etatSanTroncChampignons): self
    {
        $this->etatSanTroncChampignons = $etatSanTroncChampignons;

        return $this;
    }

    public function getEtatSanTroncNuisibles(): ?array
    {
        return $this->etatSanTroncNuisibles;
    }

    public function setEtatSanTroncNuisibles(?array $etatSanTroncNuisibles): self
    {
        $this->etatSanTroncNuisibles = $etatSanTroncNuisibles;
        return $this;
    }

    public function getEtatSanHouppierChampignons(): ?array
    {
        return $this->etatSanHouppierChampignons;
    }

    public function setEtatSanHouppierChampignons(?array $etatSanHouppierChampignons): self
    {
        $this->etatSanHouppierChampignons = $etatSanHouppierChampignons;
        return $this;
    }

    public function getEtatSanHouppierNuisibles(): ?array
    {
        return $this->etatSanHouppierNuisibles;
    }

    public function setEtatSanHouppierNuisibles(?array $etatSanHouppierNuisible): self
    {
        $this->etatSanHouppierNuisibles = $etatSanHouppierNuisible;
        return $this;
    }

    public function getRisqueGeneral(): ?array
    {
        return array_values(array_unique($this->risqueGeneral ?? []));
    }

    public function setRisqueGeneral(?array $risqueGeneral): self
    {
        $this->risqueGeneral = $risqueGeneral;
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

    public function getCritereOther(): ?string
    {
        return $this->critereOther;
    }

    public function setCritereOther(?string $critereOther): self
    {
        $this->critereOther = $critereOther;

        return $this;
    }

    public function getRisqueGeneralOther(): ?string
    {
        return $this->risqueGeneralOther;
    }

    public function setRisqueGeneralOther(?string $risqueGeneralOther): self
    {
        $this->risqueGeneralOther = $risqueGeneralOther;

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

    public function getTypeIntervention(): ?string
    {
        return $this->typeIntervention;
    }

    public function setTypeIntervention(?string $typeIntervention): self
    {
        $this->typeIntervention = $typeIntervention;

        return $this;
    }

    public function getEtatSanGeneral(): ?array
    {
        return array_values(array_unique($this->etatSanGeneral));
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

    public function getTravauxTroncOther(): ?string
    {
        return $this->travauxTroncOther;
    }

    public function setTravauxTroncOther(?string $travauxTroncOther): self
    {
        $this->travauxTroncOther = $travauxTroncOther;

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

    public function getTravauxHouppierOther(): ?string
    {
        return $this->travauxHouppierOther;
    }

    public function setTravauxHouppierOther(?string $travauxHouppierOther): self
    {
        $this->travauxHouppierOther = $travauxHouppierOther;

        return $this;
    }

    public function getProximiteWithDict(): ?array
    {
        return $this->proximiteWithDict;
    }

    public function setProximiteWithDict(?array $proximiteWithDict): self
    {
        $this->proximiteWithDict = $proximiteWithDict;

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

    public function getTravauxTroncProtection(): ?string
    {
        return $this->travauxTroncProtection;
    }

    public function setTravauxTroncProtection(?string $travauxTroncProtection): self
    {
        $this->travauxTroncProtection = $travauxTroncProtection;

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

    public function getEtatSanTroncNuisiblesAutres(): ?string
    {
        return $this->etatSanTroncNuisiblesAutres;
    }

    public function setEtatSanTroncNuisiblesAutres(?string $etatSanTroncNuisiblesAutres): self
    {
        $this->etatSanTroncNuisiblesAutres = $etatSanTroncNuisiblesAutres;

        return $this;
    }

    public function getEtatSanHouppierNuisiblesAutres(): ?string
    {
        return $this->etatSanHouppierNuisiblesAutres;
    }

    public function setEtatSanHouppierNuisiblesAutres(?string $etatSanHouppierNuisiblesAutres): self
    {
        $this->etatSanHouppierNuisiblesAutres = $etatSanHouppierNuisiblesAutres;

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

    public function getStatusTravaux(): ?bool
    {
        return $this->statusTravaux;
    }

    public function setStatusTravaux(bool $statusTravaux): self
    {
        $this->statusTravaux = $statusTravaux;

        return $this;
    }

    public function getEtatSanColletChampignonsAutres(): ?string
    {
        return $this->etatSanColletChampignonsAutres;
    }

    public function setEtatSanColletChampignonsAutres(?string $etatSanColletChampignonsAutres): self
    {
        $this->etatSanColletChampignonsAutres = $etatSanColletChampignonsAutres;

        return $this;
    }

    public function getEtatSanTroncChampignonsAutres(): ?string
    {
        return $this->etatSanTroncChampignonsAutres;
    }

    public function setEtatSanTroncChampignonsAutres(?string $etatSanTroncChampignonsAutres): self
    {
        $this->etatSanTroncChampignonsAutres = $etatSanTroncChampignonsAutres;

        return $this;
    }

    public function getEtatSanHouppierChampignonsAutres(): ?string
    {
        return $this->etatSanHouppierChampignonsAutres;
    }

    public function setEtatSanHouppierChampignonsAutres(?string $etatSanHouppierChampignonsAutres): self
    {
        $this->etatSanHouppierChampignonsAutres = $etatSanHouppierChampignonsAutres;

        return $this;
    }

    public function getEtatSanColletOther(): ?string
    {
        return $this->etatSanColletOther;
    }

    public function setEtatSanColletOther(?string $etatSanColletOther): self
    {
        $this->etatSanColletOther = $etatSanColletOther;

        return $this;
    }

    public function getEtatSanTroncOther(): ?string
    {
        return $this->etatSanTroncOther;
    }

    public function setEtatSanTroncOther(?string $etatSanTroncOther): self
    {
        $this->etatSanTroncOther = $etatSanTroncOther;

        return $this;
    }

    public function getEtatSanHouppierOther(): ?string
    {
        return $this->etatSanHouppierOther;
    }

    public function setEtatSanHouppierOther(?string $etatSanHouppierOther): self
    {
        $this->etatSanHouppierOther = $etatSanHouppierOther;

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
}
