<?php

namespace App\Controller;


use App\Service\DataZoneService;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/dataInventory")
 */
class DataConstZoneController extends AbstractController
{
    private $_service;

    public function __construct(DataZoneService $service)
    {
        $this->_service = $service;
    }

    /**
     * @Route("/zone/caractere", name="get_caractere_zone", methods="GET")
     * @SWG\Response(
     *  response=200,
     *     description="return liste des caracteres zone",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=App\Entity\DataConstInventory::Class ))
     *     )
     * )
     * @SWG\Tag(name="ConstantesInventaireZone")
     * @return JsonResponse
     */
    public function getPlantations(): JsonResponse
    {
        $data = $this->_service->caractereZone();
        return $this->json($data['data'], $data['statusCode']);
    }

    /**
     * @Route("/zone/houppier", name="get_houppier_zone", methods="GET")
     * @SWG\Response(
     *  response=200,
     *     description="return type houppier zone",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=App\Entity\DataConstInventory::Class ))
     *     )
     * )
     * @SWG\Tag(name="ConstantesInventaireZone")
     * @return JsonResponse
     */
    public function houppierZone(): JsonResponse
    {
        $data = $this->_service->houppierZone();
        return $this->json($data['data'], $data['statusCode']);
    }

    /**
     * @Route("/zone/etatGenerale", name="get_etat_zone", methods="GET")
     * @SWG\Response(
     *  response=200,
     *     description="return etat generale zone",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=App\Entity\DataConstInventory::Class ))
     *     )
     * )
     * @SWG\Tag(name="ConstantesInventaireZone")
     * @return JsonResponse
     */
    public function etatGenerale(): JsonResponse
    {
        $data = $this->_service->etatGeneralZone();
        return $this->json($data['data'], $data['statusCode']);
    }

    /**
     * @Route("/zone/travaux", name="get_travaux_zone", methods="GET")
     * @SWG\Response(
     *  response=200,
     *     description="return liste des travaux zone",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=App\Entity\DataConstInventory::Class ))
     *     )
     * )
     * @SWG\Tag(name="ConstantesInventaireZone")
     * @return JsonResponse
     */
    public function travauxZone(): JsonResponse
    {
        $data = $this->_service->travauxZone();
        return $this->json($data['data'], $data['statusCode']);
    }

}