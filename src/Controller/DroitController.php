<?php

namespace App\Controller;

use App\Entity\Droit;
use App\Service\DroitService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;

/**
 * @Route("/api/droit")
 */
class DroitController extends AbstractController
{
    private $_service;

    public function __construct(DroitService $service)
    {
        $this->_service = $service;
    }

    /**
     * @Route("", name="droit_index", methods="GET")
     * @return JsonResponse
     * @param Request $request
     * @SWG\Response(
     *     response=200,
     *     description="Returns the list of a droit",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Droit::class, groups={"read"}))
     *     )
     * )
     * @SWG\Tag(name="Droit")
     */
    public function index(Request $request): JsonResponse
    {
        $data = $this->_service->getAll($request);
        return $this->json($data['data'], $data['statusCode']);
    }

    /**
     * @Route("/{id}", name="droit_show", methods="GET", requirements={"id"="\d+"})
     * @return JsonResponse
     * @param  Request $request
     * @param Droit $droit
     * @SWG\Response(
     *     response=200,
     *     description="Returns the list of a droit",
     *     @SWG\Schema(
     *         type="Droit",
     *         @SWG\Items(ref=@Model(type=Droit::class, groups={"read"}))
     *     )
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="slug of droit"
     * )
     * @SWG\Tag(name="Droit")
     */
    public function show(Droit $droit, Request $request): JsonResponse
    {
        $data = $this->_service->getOne($request, $droit);
        return $this->json($data['data'], $data['statusCode']);
    }

    /**
     * @Route("", name="droit_new", methods="POST")
     * @return JsonResponse
     * @param Request $request
     * @SWG\Response(
     *     response=201,
     *     description="Returns an item of a droit",
     *     @SWG\Schema(
     *         type="Droit",
     *         @SWG\Items(ref=@Model(type=Droit::class, groups={"read"}))
     *     )
     * )
     * @SWG\Parameter(
     *     name="droit",
     *     in="body",
     *     @Model(type=Droit::class, groups={"post"})
     * )
     * @SWG\Tag(name="Droit")
     */
    public function new(Request $request): JsonResponse
    {
        $data = $this->_service->add($request);
        return $this->json($data['data'], $data['statusCode']);
    }
    
    /**
     * @Route("/{id}", name="droit_delete", methods="DELETE", requirements={"id"="\d+"})
     * @return JsonResponse
     * @var Request $request
     * @var Droit $droit
     * @SWG\Response(
     *     response=204,
     *     description="Return no content",
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="slug of droit"
     * )
     * @SWG\Tag(name="Droit")
     */
    public function delete(Request $request, Droit $droit): JsonResponse
    {
        $data = $this->_service->delete($request, $droit);
        return $this->json($data['data'], $data['statusCode']);
    }

}
