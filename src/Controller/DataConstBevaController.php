<?php

namespace App\Controller;

use App\Service\DataBevaService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;


/**
 * @Route("/api/dataInventory")
 */
class DataConstBevaController extends AbstractController
{
    private $_service;

    public function __construct(DataBevaService $service)
    {
        $this->_service = $service;
    }

    /**
     * @Route("/beva/locationIndex", name="get_locationIndex_beva", methods="GET")
     * @SWG\Response(
     *  response=200,
     *     description="return the beva location index",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=App\Entity\DataConstInventory::Class ))
     *     )
     * )
     * @SWG\Tag(name="ConstantesInventaireBeva")
     * @return JsonResponse
     */
    public function getLocationIndex(): JsonResponse
    {
        $data = $this->_service->Location();
        return $this->json($data['data'], $data['statusCode']);
    }

    /**
     * @Route("/beva/healthIndex", name="get_healthIndex_beva", methods="GET")
     * @SWG\Response(
     *  response=200,
     *     description="return the beva health index",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=App\Entity\DataConstInventory::Class ))
     *     )
     * )
     * @SWG\Tag(name="ConstantesInventaireBeva")
     * @return JsonResponse
     */
    public function getHealthIndex(): JsonResponse
    {
        $data = $this->_service->HealthIndex();
        return $this->json($data['data'], $data['statusCode']);
    }

    /**
     * @Route("/beva/aestheticIndex", name="get_aestheticIndex_beva", methods="GET")
     * @SWG\Response(
     *  response=200,
     *     description="return the beva aesthetic index",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=App\Entity\DataConstInventory::Class ))
     *     )
     * )
     * @SWG\Tag(name="ConstantesInventaireBeva")
     * @return JsonResponse
     */
    public function getaestheticIndex(): JsonResponse
    {
        $data = $this->_service->aestheticIndex();
        return $this->json($data['data'], $data['statusCode']);
    }

    /**
     * @Route("/beva/aestheticIndexValue", name="get_aestheticIndexValue_beva", methods="GET")
     * @SWG\Response(
     *  response=200,
     *     description="return the beva aesthetic index value",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=App\Entity\DataBevaColumn::Class ))
     *     )
     * )
     * @SWG\Tag(name="ConstantesInventaireBeva")
     * @return JsonResponse
     */
    public function getaestheticIndexValue(): JsonResponse
    {
        $data = $this->_service->aestheticIndexValue();
        return $this->json($data['data'], $data['statusCode']);
    }

    /**
     * @Route("/beva/healthIndexValue", name="get_healthIndexValue_beva", methods="GET")
     * @SWG\Response(
     *  response=200,
     *     description="return the beva health index value",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=App\Entity\DataBevaColumn::Class ))
     *     )
     * )
     * @SWG\Tag(name="ConstantesInventaireBeva")
     * @return JsonResponse
     */
    public function gethealthIndexValue(): JsonResponse
    {
        $data = $this->_service->healthIndexValue();
        return $this->json($data['data'], $data['statusCode']);
    }
}