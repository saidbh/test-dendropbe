<?php

namespace App\Controller;

use App\Service\DataInventoryService;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/dataInventory")
 */
class DataConstInventoryController extends AbstractController
{
    private $_service;

    public function __construct(DataInventoryService $service)
    {
        $this->_service = $service;
    }

    /**
     * @Route("/arbre/plantations", name="get_plantations_tree", methods="GET")
     * @SWG\Response(
     *  response=200,
     *     description="return the list of a Data",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=App\Entity\DataConstInventory::Class ))
     *     )
     * )
     * @SWG\Tag(name="ConstantesInventaire")
     * @return JsonResponse
     */
    public function getPlantations(): JsonResponse
    {
        $data = $this->_service->plantations();
        return new JsonResponse($data['data'], $data['statusCode']);
    }

    /**
     * @Route("/arbre/criteres", name="get_criteres_tree", methods="GET")
     * @SWG\Response(
     *  response=200,
     *     description="return the list of a criterias",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=App\Entity\DataConstInventory::Class ))
     *     )
     * )
     * @SWG\Tag(name="ConstantesInventaire")
     * @return JsonResponse
     */
    public function getCriteres(): JsonResponse
    {
        $data = $this->_service->criteres();
        return new JsonResponse($data['data'], $data['statusCode']);
    }

    /**
     * @Route("/proximites", name="get_proximites_tree_zone", methods="GET")
     * @SWG\Response(
     *  response=200,
     *     description="return the list of a proximities",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=App\Entity\DataConstInventory::Class ))
     *     )
     * )
     * @SWG\Tag(name="ConstantesInventaire")
     * @return JsonResponse
     */
    public function getProximites() :JsonResponse
    {
        $data = $this->_service->proximites();
        return$this->json($data['data'],$data['statusCode']);
    }

    /**
     * @Route("/proximitesDict", name="get_proximitesDict_tree_zone", methods="GET")
     * @SWG\Response(
     *  response=200,
     *     description="return the list of a Proximities Dict",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=App\Entity\DataConstInventory::Class ))
     *     )
     * )
     * @SWG\Tag(name="ConstantesInventaire")
     * @return JsonResponse
     */
    public function getProximitesDict() :JsonResponse
    {
        $data = $this->_service->proximitesDict();
        return$this->json($data['data'],$data['statusCode']);
    }

    /**
     * @Route("/arbre/caracteresPieds", name="get_caracteresPieds_tree", methods="GET")
     * @SWG\Response(
     *  response=200,
     *     description="return liste des caracteres pieds",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=App\Entity\DataConstInventory::Class ))
     *     )
     * )
     * @SWG\Tag(name="ConstantesInventaire")
     * @return JsonResponse
     */
    public function getCaracteresPieds() :JsonResponse
    {
        $data = $this->_service->caracteresPied();
        return$this->json($data['data'],$data['statusCode']);
    }

    /**
     * @Route("/arbre/caracteresTronc", name="get_caracteresTronc_tree", methods="GET")
     * @SWG\Response(
     *  response=200,
     *     description="return liste des caracteres Tronc",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=App\Entity\DataConstInventory::Class ))
     *     )
     * )
     * @SWG\Tag(name="ConstantesInventaire")
     * @return JsonResponse
     */
    public function getCaracteresTronc() :JsonResponse
    {
        $data = $this->_service->caracteresTronc();
        return$this->json($data['data'],$data['statusCode']);
    }

    /**
     * @Route("/arbre/portArbre", name="get_port_tree", methods="GET")
     * @SWG\Response(
     *  response=200,
     *     description="return liste des ports arbre",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=App\Entity\DataConstInventory::Class ))
     *     )
     * )
     * @SWG\Tag(name="ConstantesInventaire")
     * @return JsonResponse
     */
    public function getPortArbre() :JsonResponse
    {
        $data = $this->_service->portArbre();
        return$this->json($data['data'],$data['statusCode']);
    }

    /**
     * @Route("/stadeDeveloppement", name="get_developmentState_tree", methods="GET")
     * @SWG\Response(
     *  response=200,
     *     description="return liste des stades de devloppement",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=App\Entity\DataConstInventory::Class ))
     *     )
     * )
     * @SWG\Tag(name="ConstantesInventaire")
     * @return JsonResponse
     */
    public function getDevlopmentState() :JsonResponse
    {
        $data = $this->_service->stadeDeveloppement();
        return$this->json($data['data'],$data['statusCode']);
    }

    /**
     * @Route("/arbre/santeTronc", name="get_santeTronc_tree", methods="GET")
     * @SWG\Response(
     *  response=200,
     *     description="return liste des etats de santé tronc",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=App\Entity\DataConstInventory::Class ))
     *     )
     * )
     * @SWG\Tag(name="ConstantesInventaire")
     * @return JsonResponse
     */
    public function getEtatSanteTronc() :JsonResponse
    {
        $data = $this->_service->etatSanteTronc();
        return$this->json($data['data'],$data['statusCode']);
    }

    /**
     * @Route("/arbre/santeHouppier", name="get_santeHouppier_tree", methods="GET")
     * @SWG\Response(
     *  response=200,
     *     description="return liste des etats de santé houppier",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=App\Entity\DataConstInventory::Class ))
     *     )
     * )
     * @SWG\Tag(name="ConstantesInventaire")
     * @return JsonResponse
     */
    public function getEtatSanteHouppier() :JsonResponse
    {
        $data = $this->_service->etatSanteHouppier();
        return$this->json($data['data'],$data['statusCode']);
    }

    /**
     * @Route("/arbre/santeGenerale", name="get_santeGenerale_tree", methods="GET")
     * @SWG\Response(
     *  response=200,
     *     description="return liste des etats de santé générale",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=App\Entity\DataConstInventory::Class ))
     *     )
     * )
     * @SWG\Tag(name="ConstantesInventaire")
     * @return JsonResponse
     */
    public function getEtatSanteGenerale() :JsonResponse
    {
        $data = $this->_service->etatSanteGenerale();
        return$this->json($data['data'],$data['statusCode']);
    }

    /**
     * @Route("/risque", name="get_risque_tree_zone", methods="GET")
     * @SWG\Response(
     *  response=200,
     *     description="return liste des riques",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=App\Entity\DataConstInventory::Class ))
     *     )
     * )
     * @SWG\Tag(name="ConstantesInventaire")
     * @return JsonResponse
     */
    public function getRisque() :JsonResponse
    {
        $data = $this->_service->risque();
        return$this->json($data['data'],$data['statusCode']);
    }

    /**
     * @Route("/arbre/risqueGeneral", name="get_risqueGeneral_tree", methods="GET")
     * @SWG\Response(
     *  response=200,
     *     description="return liste des riques générales",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=App\Entity\DataConstInventory::Class ))
     *     )
     * )
     * @SWG\Tag(name="ConstantesInventaire")
     * @return JsonResponse
     */
    public function getRisqueGenerale() :JsonResponse
    {
        $data = $this->_service->risqueGeneral();
        return$this->json($data['data'],$data['statusCode']);
    }

    /**
     * @Route("/nuisance", name="get_nuisance_tree_zone", methods="GET")
     * @SWG\Response(
     *  response=200,
     *     description="return liste des nuisances",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=App\Entity\DataConstInventory::Class ))
     *     )
     * )
     * @SWG\Tag(name="ConstantesInventaire")
     * @return JsonResponse
     */
    public function getNuisance() :JsonResponse
    {
        $data = $this->_service->nuisance();
        return$this->json($data['data'],$data['statusCode']);
    }

    /**
     * @Route("/tauxFrequent", name="get_tauxFrequent_tree_zone", methods="GET")
     * @SWG\Response(
     *  response=200,
     *     description="return liste des taux fréquent",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=App\Entity\DataConstInventory::Class ))
     *     )
     * )
     * @SWG\Tag(name="ConstantesInventaire")
     * @return JsonResponse
     */
    public function gettauxFrequent() :JsonResponse
    {
        $data = $this->_service->tauxFrequent();
        return$this->json($data['data'],$data['statusCode']);
    }

    /**
     * @Route("/typePassage", name="get_typePassage_tree_zone", methods="GET")
     * @SWG\Response(
     *  response=200,
     *     description="return les types de passage",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=App\Entity\DataConstInventory::Class))
     *     )
     * )
     * @SWG\Tag(name="ConstantesInventaire")
     * @return JsonResponse
     */
    public function getTypePassage() :JsonResponse
    {
        $data = $this->_service->typePassage();
        return$this->json($data['data'],$data['statusCode']);
    }

    /**
     * @Route("/accessibilite", name="get_accessibilite_tree_zone", methods="GET")
     * @SWG\Response(
     *  response=200,
     *     description="return l'accessibilité des arbres",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=App\Entity\DataConstInventory::Class))
     *     )
     * )
     * @SWG\Tag(name="ConstantesInventaire")
     * @return JsonResponse
     */
    public function getaccessibility() :JsonResponse
    {
        $data = $this->_service->accessibilite();
        return$this->json($data['data'],$data['statusCode']);
    }

    /**
     * @Route("/arbre/typeAbattage", name="get_typeAbattage_tree", methods="GET")
     * @SWG\Response(
     *  response=200,
     *     description="return les types d'abattage des arbres",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=App\Entity\DataConstInventory::Class))
     *     )
     * )
     * @SWG\Tag(name="ConstantesInventaire")
     * @return JsonResponse
     */
    public function getabattage() :JsonResponse
    {
        $data = $this->_service->abattage();
        return$this->json($data['data'],$data['statusCode']);
    }

    /**
     * @Route("/arbre/travauxCollet", name="get_typeTravauxCollet_tree", methods="GET")
     * @SWG\Response(
     *  response=200,
     *     description="return les types de travaux pour le collet",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=App\Entity\DataConstInventory::Class))
     *     )
     * )
     * @SWG\Tag(name="ConstantesInventaire")
     * @return JsonResponse
     */
    public function getColletTravaux() :JsonResponse
    {
        $data = $this->_service->travauxCollet();
        return$this->json($data['data'],$data['statusCode']);
    }

    /**
     * @Route("/arbre/travauxTronc", name="get_typeTravauxTronc_tree", methods="GET")
     * @SWG\Response(
     *  response=200,
     *     description="return les types de travaux pour le tronc",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=App\Entity\DataConstInventory::Class))
     *     )
     * )
     * @SWG\Tag(name="ConstantesInventaire")
     * @return JsonResponse
     */
    public function getTravauxTronc() :JsonResponse
    {
        $data = $this->_service->travauxTronc();
        return$this->json($data['data'],$data['statusCode']);
    }

    /**
     * @Route("/arbre/travauxHouppier", name="get_typeTravauxHouppier_tree", methods="GET")
     * @SWG\Response(
     *  response=200,
     *     description="return les types de travaux pour le houppier",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=App\Entity\DataConstInventory::Class))
     *     )
     * )
     * @SWG\Tag(name="ConstantesInventaire")
     * @return JsonResponse
     */
    public function getTravauxHouppier() :JsonResponse
    {
        $data = $this->_service->travauxHouppier();
        return$this->json($data['data'],$data['statusCode']);
    }

    /**
     * @Route("/travauxHouppier", name="get_dateTravaux_tree_zone", methods="GET")
     * @SWG\Response(
     *  response=200,
     *     description="return la date des travaux",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=App\Entity\DataConstInventory::Class))
     *     )
     * )
     * @SWG\Tag(name="ConstantesInventaire")
     * @return JsonResponse
     */
    public function getdateTravaux() :JsonResponse
    {
        $data = $this->_service->dateTravaux();
        return$this->json($data['data'],$data['statusCode']);
    }

    /**
     * @Route("/intervention", name="get_typeIntervention_tree_zone", methods="GET")
     * @SWG\Response(
     *  response=200,
     *     description="return les types d'intervention",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=App\Entity\DataConstInventory::Class))
     *     )
     * )
     * @SWG\Tag(name="ConstantesInventaire")
     * @return JsonResponse
     */
    public function getTypeIntervention() :JsonResponse
    {
        $data = $this->_service->typeIntervention();
        return$this->json($data['data'],$data['statusCode']);
    }
}
