<?php

namespace App\Controller;

use App\Entity\Espece;
use App\Service\EspeceService;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/especes")
 */
class EspeceController extends AbstractController
{
    private $_service;

    public function __construct(EspeceService $service)
    {
        $this->_service = $service;
    }

    /**
     * @Route("", name="espece_index", methods="GET")
     * @param Request $request
     * @SWG\Response(
     *  response=200,
     *     description="return the list of a Espece",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Espece::class, groups={"read"}))
     *     )
     * )
     * @SWG\Parameter(
     *     name="page",
     *     in="query",
     *     type="number",
     *     description="set of pages"
     * )
     * @SWG\Tag(name="Espece")
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $data = $this->_service->getEspeces($request);
        return $this->json($data['data'], $data['statusCode']);
    }

    /**
     * @Route("", name="espece_new", methods="POST")
     * @param Request $request
     * @SWG\Response(
     *  response=201,
     *     description="return an object a Espece",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Items(ref=@Model(type=Espece::class, groups={"read"}))
     *     )
     * )
     * @SWG\Parameter(
     *     name="params",
     *     in="body",
     *     @SWG\Schema(type="object", required={"name", "genre"},
     *          @SWG\Property(type="string", property="name", maxLength=255),
     *          @SWG\Property(type="string", property="categorie", maxLength=255, ),
     *          @SWG\Property(type="string", property="cultivar", maxLength=255, ),
     *          @SWG\Property(type="string", property="genre"),
     *          @SWG\Property(type="string", property="nomFr"),
     *          @SWG\Property(type="integer", property="tarif"),
     *      )
     *    )
     * )
     * @SWG\Tag(name="Espece")
     * @return JsonResponse
     */
    public function new(Request $request): JsonResponse
    {
        $data = $this->_service->addEspece($request);
        return $this->json($data['data'], $data['statusCode']);
    }

    /**
     * @Route("/{id}", name="espece_show", methods="GET")
     * @param Request $request
     * @param Espece $espece
     * @SWG\Response(
     *  response=200,
     *     description="return an object of a Espece",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Items(ref=@Model(type=Espece::class, groups={"read"}))
     *     )
     * )
     * @SWG\Tag(name="Espece")
     * @return JsonResponse
     */
    public function show(Request $request, Espece $espece): JsonResponse
    {
        $data = $this->_service->getEspece($request, $espece);
        return $this->json($data['data'], $data['statusCode']);
    }

    /**
     * @Route("/{id}", name="espece_edit", methods="PUT")
     * @param Espece $espece
     * @param Request $request
     * @SWG\Response(
     *  response=202,
     *     description="return an object a Espece",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Items(ref=@Model(type=Espece::class, groups={"read"}))
     *     )
     * )
     * @SWG\Parameter(
     *     name="params",
     *     in="body",
     *     @SWG\Schema(type="object", required={"name", "genre"},
     *          @SWG\Property(type="string", property="name", maxLength=255),
     *          @SWG\Property(type="string", property="categorie", maxLength=255, ),
     *          @SWG\Property(type="string", property="cultivar", maxLength=255, ),
     *          @SWG\Property(type="string", property="genre"),
     *          @SWG\Property(type="string", property="nomFr"),
     *          @SWG\Property(type="integer", property="tarif"),
     *      )
     *    )
     * )
     * @SWG\Tag(name="Espece")
     * @return JsonResponse
     */
    public function edit(Request $request, Espece $espece): JsonResponse
    {
        $data = $this->_service->update($request, $espece);
        return $this->json($data['data'], $data['statusCode']);
    }

    /**
     * @Route("/search", name="espece_search", methods="POST")
     * @param Request $request
     * @SWG\Response(
     *  response=200,
     *     description="return an object of a Espece",
     *     @SWG\Schema(
     *         type="Espece",
     *         @SWG\Items(ref=@Model(type=Espece::class, groups={"read"}))
     *     )
     * )
     * @SWG\Parameter(
     *     name="params",
     *     in="body",
     *     @SWG\Schema(type="object", required={"infos"},
     *          @SWG\Property(type="text", property="infos")
     *      )
     *    )
     * )
     * @SWG\Parameter(
     *     name="page",
     *     in="query",
     *     type="number",
     *     description="set of pages"
     * )
     * @SWG\Tag(name="Espece")
     * @return JsonResponse
     */
    public function searchEspece(Request $request): JsonResponse
    {
        $data = $this->_service->searchEspece($request);
        return $this->json($data['data'], $data['statusCode']);
    }

    /**
     * delete espece
     * @Route("/{id}", name="espece_one_delete", methods="DELETE")
     * @param Request $request
     * @param Espece $espece
     * @SWG\Response(
     *  response=204,
     *  description="return no content",
     * )
     * @SWG\Tag(name="Espece")
     * @return JsonResponse
     */
    public function delete(Request $request, Espece $espece): JsonResponse
    {
        $data = $this->_service->delete($request, $espece);
        return $this->json($data['data'], $data['statusCode']);
    }

    /**
     * Get all genre espece
     * @Route("/genre", name="espece_delete", methods="POST")
     * @param Request $request
     * @SWG\Response(
     *  response=Response::HTTP_OK,
     *  description="return array of string",
     * )
     * @SWG\Tag(name="Espece")
     * @return JsonResponse
     */
    public function get_genre(Request $request): JsonResponse
    {
        $data = $this->_service->getGenres($request);
        return $this->json($data['data'], $data['statusCode']);
    }
}
