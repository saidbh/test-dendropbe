<?php

namespace App\Controller;

use App\Entity\Champignons;
use App\Service\ChampignonService;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/champignons")
 */
class ChampignonsController extends AbstractController
{
    private $_champignonService;

    public function __construct(ChampignonService $service)
    {
        $this->_champignonService = $service;
    }

    /**
     * @Route("", name="champignon_index", methods="GET")
     * @param Request $request
     * @SWG\Response(
     *  response=200,
     *     description="return the list of a Champignons",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Champignons::class, groups={"read"}))
     *     )
     * )
     * @SWG\Parameter(
     *     name="page",
     *     in="query",
     *     type="number",
     *     description="set of pages"
     * )
     * @SWG\Tag(name="Champignons")
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $result = $this->_champignonService->getAll($request);
        return $this->json($result['data'], $result['statusCode']);
    }

    /**
     * @Route("", name="champignon_new", methods="POST")
     * @param Request $request
     * @return JsonResponse
     * @SWG\Response(
     *  response=200,
     *     description="return an object a Champignons",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Items(ref=@Model(type=Champignons::class, groups={"read"}))
     *     )
     * )
     * @SWG\Parameter(
     *     name="img1",
     *     in="formData",
     *     description="champignons image 1",
     *     type="file"
     * )
     * * @SWG\Parameter(
     *     name="img2",
     *     in="formData",
     *     description="champignons image 2",
     *     type="file"
     * )
     * @SWG\Parameter(
     *     name="name",
     *     in="formData",
     *     description="champignon name",
     *     type="string"
     * )
     * @SWG\Parameter(
     *     name="attaqueF",
     *     in="formData",
     *     description="attaque sur Feuillux",
     *     type="string"
     * )
     * @SWG\Parameter(
     *     name="attaqueR",
     *     in="formData",
     *     description="Attaque sur résineux",
     *     type="string"
     * )
     *
     * @SWG\Parameter(
     *     name="category",
     *     in="formData",
     *     description="Catégorie soit R ou F",
     *     type="string"
     * )
     * @SWG\Tag(name="Champignons")
     */
    public function new(Request $request): JsonResponse
    {
        $result = $this->_champignonService->add($request);
        return $this->json($result['data'], $result['statusCode']);
    }

    /**
     * @Route("/{id}", name="champignon_show", methods="GET", requirements={"id"="\d+"})
     * @param Request $request
     * @param Champignons $champignon
     * @return JsonResponse
     * @SWG\Response(
     *  response=200,
     *     description="return an object a Champignons",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Items(ref=@Model(type=Champignons::class, groups={"read"}))
     *     )
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="slug of nuisible"
     * )
     * @SWG\Tag(name="Champignons")
     */
    public function show(Request $request, Champignons $champignon): JsonResponse
    {
        $result = $this->_champignonService->getOne($request, $champignon);
        return $this->json($result['data'], $result['statusCode']);
    }

    /**
     * @Route("/{id}/upload", name="champignon_upload", methods="POST")
     * @param Request $request
     * @SWG\Response(
     *  response=200,
     *     description="return an object a Champignons",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Items(ref=@Model(type=Champignons::class, groups={"read"}))
     *     )
     * )
     * @SWG\Parameter(
     *     name="img1",
     *     in="formData",
     *     description="champignons image 1",
     *     type="file"
     * )
     * * @SWG\Parameter(
     *     name="img2",
     *     in="formData",
     *     description="champignons image 2",
     *     type="file"
     * )
     * @param Champignons $champignon
     * @SWG\Tag(name="Champignons")
     * @return JsonResponse
     */
    public function uploadFile(Request $request, Champignons $champignon): JsonResponse
    {
        $result = $this->_champignonService->uploadFileChampignons($request, $champignon);
        return $this->json($result['data'], $result['statusCode']);
    }

    /**
     * @Route("/{id}", name="champignon_edit", methods="PUT")
     * @param Champignons $champignon
     * @param Request $request
     * @SWG\Response(
     *  response=200,
     *     description="return an object a Champignons",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Items(ref=@Model(type=Champignons::class, groups={"read"}))
     *     )
     * )
     * @SWG\Parameter(
     *     name="img1",
     *     in="formData",
     *     description="champignons image 1",
     *     type="file"
     * )
     * @SWG\Parameter(
     *     name="img2",
     *     in="formData",
     *     description="champignons image 2",
     *     type="file"
     * )
     * @SWG\Parameter(
     *     name="name",
     *     in="formData",
     *     description="champignon name",
     *     type="string"
     * )
     * @SWG\Parameter(
     *     name="attaqueF",
     *     in="formData",
     *     description="attaque sur Feuillux",
     *     type="string"
     * )
     * @SWG\Parameter(
     *     name="attaqueR",
     *     in="formData",
     *     description="Attaque sur résineux",
     *     type="string"
     * )
     * @SWG\Parameter(
     *     name="category",
     *     in="formData",
     *     description="Catégorie soit R ou F",
     *     type="string"
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="slug of champignon"
     * )
     * @SWG\Tag(name="Champignons")
     * @return JsonResponse
     */
    public function edit(Request $request, Champignons $champignon): JsonResponse
    {
        $result = $this->_champignonService->edit($request, $champignon);
        return $this->json($result['data'], $result['statusCode']);
    }

    /**
     * @Route("/{id}", name="champignons_delete", methods="DELETE")
     * @param Champignons $champignons
     * @param Request $request
     * @SWG\Response(
     *  response=204,
     *  description="return no content"
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="slug of champignon"
     * )
     * @SWG\Tag(name="Champignons")
     * @return JsonResponse
     */
    public function delete(Request $request, Champignons $champignons): Response
    {
        $result = $this->_champignonService->delete($request, $champignons);
        return $this->json($result['data'], $result['statusCode']);
    }

    /**
     * @Route("/feuilluOrResineu", name="get_feuillu_or_resineu", methods="GET")
     * @param Request $request
     * @SWG\Parameter(
     *     name="params",
     *     in="body",
     *     @SWG\Schema(type="object", required={"categorie"},
     *          @SWG\Property(type="text", property="categorie")
     *      )
     *    )
     * )
     * @SWG\Response(
     *      response=200,
     *      description="return the list of a Chmapignons",
     *      @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Champignons::class, groups={"read"}))
     *      )
     * )
     * @SWG\Parameter(
     *     name="page",
     *     in="query",
     *     type="number",
     *     description="set of pages"
     * )
     * @SWG\Tag(name="Champignons")
     * @return JsonResponse
     */
    public function getListResineuOrF(Request $request): JsonResponse
    {
        $result = $this->_champignonService->getListResineuOrF($request);
        return $this->json($result['data'], $result['statusCode']);
    }

    /**
     * @Route("/search", name="search_champignons", methods="POST")
     * @param Request $request
     * @SWG\Parameter(
     *     name="params",
     *     in="body",
     *     @SWG\Schema(type="object", required={"infos"},
     *          @SWG\Property(type="text", property="categorie")
     *      )
     *    )
     * )
     * @SWG\Response(
     *      response=200,
     *      description="return the list of a Chmapignons",
     *      @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Champignons::class, groups={"read"}))
     *      )
     * )
     * @SWG\Parameter(
     *     name="page",
     *     in="query",
     *     type="number",
     *     description="set of pages"
     * )
     * @SWG\Parameter(
     *     name="params",
     *     in="body",
     *     @SWG\Schema(type="object", required={"infos"},
     *          @SWG\Property(type="text", property="infos")
     *      )
     *    )
     * )
     * @SWG\Tag(name="Champignons")
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $result = $this->_champignonService->search($request);
        return $this->json($result['data'], $result['statusCode']);
    }

    /**
     * @Route("/{id}/delete-image", name="champignon_delete_image", methods="POST")
     * @SWG\Response(
     *  response=204,
     *     description="return an object a Champignons",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Items(ref=@Model(type=Champignons::class, groups={"read"}))
     *     )
     * )
     * * @SWG\Parameter(
     *     name="img",
     *     in="body",
     *     @SWG\Schema(type="object", required={"img"},
     *          @SWG\Property(type="text", property="img")
     *      )
     *    )
     * )
     * @SWG\Tag(name="Champignons")
     */
    public function deleteImage(Champignons $champignon, Request $request): JsonResponse
    {
        $result = $this->_champignonService->deleteImage($champignon, $request);
        return $this->json($result['data'], $result['statusCode']);
    }
}
