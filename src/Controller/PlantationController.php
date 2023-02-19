<?php

namespace App\Controller;

use App\Entity\Plantation;
use App\Service\PlantationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;

/**
 * @Route("/api/plantations")
 */
class PlantationController extends AbstractController
{
    private $service;

    public function __construct(PlantationService $service)
    {
        $this->service = $service;
    }

    /**
     * @Route("", name="get_plantation", methods="GET")
     * @param Request $request
     * @SWG\Response(
     *  response=200,
     *     description="return the list of a Plantation",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Plantation::class, groups={"read"}))
     *     )
     * )
     * @SWG\Tag(name="plantations")
     * @return JsonResponse
     */
    public function index(Request $request):JsonResponse
    {
        $data = $this->service->getPlantations($request);
        return $this->json($data['data'], $data['statusCode']);
    }

    /**
     * @Route("", name="new_plantation", methods="POST")
     * @param Request $request
     * @SWG\Response(
     *      response=200,
     *      description="return a plantation",
     *      @SWG\Schema(
     *         type="Object",
     *         @SWG\Items(ref=@Model(type=Plantation::class, groups={"read"}))
     *      )
     * )
     * @SWG\Parameter(
     *     name="params",
     *     in="body",
     *     @SWG\Schema(type="object", required={"hauteur", "diametre", "coord", "countSubject", "espece"},
     *          @SWG\Property(type="integer", property="hauteur"),
     *          @SWG\Property(type="integer", property="diametre"),
     *          @SWG\Property(type="integer", property="espece"),
     *          @SWG\Property(type="object", property="coord",
     *              @SWG\Items(type="object",
     *                    @SWG\Property(type="string", property="lat"),
     *                    @SWG\Property(type="string", property="long"),
     *              )
     *          ),
     *          @SWG\Property(type="datetime", property="dateEcheance"),
     *          @SWG\Property(type="integer", property="countSubject", default=1)
     *      )
     *    )
     * )
     * @SWG\Tag(name="plantations")
     * @return JsonResponse
     */
    public function new(Request $request): JsonResponse
    {
        $data = $this->service->addPlantation($request);
        return $this->json($data['data'], $data['statusCode']);
    }

    /**
     * @Route("/{id}", name="delete_plantation", methods="DELETE")
     * @param Request $request
     * @param Plantation $plantation
     * @SWG\Response(
     *  response=204,
     *  description="return no content"
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="slug of nuisible"
     * )
     * @SWG\Tag(name="plantations")
     * @return JsonResponse
     */
    public function delete(Plantation $plantation, Request $request): JsonResponse
    {
        $data = $this->service->deleteSinglePlantation($request, $plantation);
        return $this->json($data['data'], $data['statusCode']);
    }

    /**
     * @Route("/deleteMany", name="delete_many_plantation", methods="POST")
     * @param Request $request
     * @SWG\Response(
     *  response=204,
     *  description="return no content"
     * )
     * @SWG\Parameter(
     *     parameter="ids",
     *     name="ids",
     *     in="body",
     *     required=true,
     *     description="ObjectArray",
     * @SWG\Schema(type="object",
     *         @SWG\Property(property="ids", type="array",
     *          @SWG\Items(type="integer")
     *      )
     *   )
     * )
     * @SWG\Tag(name="plantations")
     * @return JsonResponse
     */
    public function deleteManyPlant(Request $request): JsonResponse
    {
        $data = $this->service->deleteManyPlantation($request);
        return $this->json($data['data'], $data['statusCode']);
    }

    /**
     * @Route("/{id}", name="get_one_plantation", methods="GET")
     * @SWG\Response(
     *  response=200,
     *     description="return an object a Plantation",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Items(ref=@Model(type=Plantation::class, groups={"read"}))
     *     )
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer"
     * )
     * @SWG\Tag(name="plantations")
     */
    public function getPlantation(Request $request, Plantation $plantation)
    {
        $data = $this->service->getPlantation($request, $plantation);
        return $this->json($data['data'], $data['statusCode']);
    }

}
