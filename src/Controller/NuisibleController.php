<?php

namespace App\Controller;

use App\Entity\Nuisible;

use App\Service\NuisibleService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\JsonResponse;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;
/**
 * @Route("/api/nuisible")
 */
class NuisibleController extends AbstractController
{
    private $_nuisibleService;

    public function __construct(NuisibleService $service) {
        $this->_nuisibleService = $service;
    }
    /**
     * @Route("", name="nuisible_index", methods="GET")
     * @SWG\Response(
     *  response=200,
     *     description="return the list of a Nuisible",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Nuisible::class, groups={"read"}))
     *     )
     * )
     * @SWG\Tag(name="Nuisible")
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $result = $this->_nuisibleService->list();
        return $this->json($result['data'], $result['statusCode']);
    }

    /**
     * @Route("", name="nuisible_new", methods="POST")
     * @param Request $request
     * @SWG\Response(
     *  response=201,
     *     description="return an object of a Nuisible",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Items(ref=@Model(type=Nuisible::class, groups={"read"}))
     *     )
     * )
     * @SWG\Parameter(
     *     name="params",
     *     in="body",
     *     @SWG\Schema(type="object", required={"name"},
     *          @SWG\Property(type="string", property="name", maxLength=255),
     *      )
     *    )
     * )
     * @SWG\Tag(name="Nuisible")
     * @return JsonResponse
     */
    public function new(Request $request): JsonResponse
    {
        $result = $this->_nuisibleService->add($request);
        return $this->json($result['data'], $result['statusCode']);
    }

    /**
     * @Route("/{id}", name="nuisible_show", methods="GET")
     * @param Nuisible $nuisible
     * @SWG\Response(
     *  response=200,
     *     description="return an object of nuisible",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Items(ref=@Model(type=Nuisible::class, groups={"read"}))
     *     )
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="slug of nuisible"
     * )
     * @SWG\Tag(name="Nuisible")
     * @return JsonResponse
     */
    public function show(Nuisible $nuisible): JsonResponse
    {
        $result = $this->_nuisibleService->getOne($nuisible);
        return $this->json($result['data'], $result['statusCode']);
    }

    /**
     * @Route("/{id}", name="nuisible_edit", methods="PUT")
     * @param Request $request
     * @param Nuisible $nuisible
     * @SWG\Response(
     *  response=200,
     *     description="return an object of a Nuisible",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Items(ref=@Model(type=Nuisible::class, groups={"read"}))
     *     )
     * )
     * @SWG\Parameter(
     *     name="params",
     *     in="body",
     *     @SWG\Schema(type="object", required={"name"},
     *          @SWG\Property(type="string", property="name", maxLength=255),
     *      )
     *    )
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="slug of nuisible"
     * )
     * @SWG\Tag(name="Nuisible")
     * @return JsonResponse
     */
    public function edit(Request $request, Nuisible $nuisible): JsonResponse
    {
        $result = $this->_nuisibleService->edit($request, $nuisible);
        return $this->json($result['data'], $result['statusCode']);
    }

    /**
     * @Route("/{id}", name="nuisible_delete", methods="DELETE")
     * @param Nuisible $nuisible
     * @SWG\Response(
     *      response=204,
     *      description="return no content"
     *     )
     * ),
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="slug of nuisible"
     * )
     * @SWG\Tag(name="Nuisible")
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request, Nuisible $nuisible): JsonResponse
    {
        $result = $this->_nuisibleService->delete($request, $nuisible);
        return $this->json($result['data'], $result['statusCode']);
    }
}
