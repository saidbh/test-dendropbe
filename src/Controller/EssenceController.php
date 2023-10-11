<?php

namespace App\Controller;

use App\Entity\Essence;
use App\Service\EssenceService;
use App\Service\ImageService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;
/**
 * @Route("/api/essence")
 */
class EssenceController extends AbstractController
{
    private $_service;
    private $_imageService;

    public function __construct(EssenceService $_essenceService, ImageService $_imageService)
    {
        $this->_service = $_essenceService;
        $this->_imageService = $_imageService;
    }

    /**
     * @Route("/{id}", name="get_one_essence", methods="GET", requirements={"id"="\d+"})
     * @param Request $request
     * @param Essence $essence
     * @SWG\Response(
     *      response=200,
     *      description="return an object of essence",
     *      @SWG\Schema(
     *         type="object",
     *         @SWG\Items(ref=@Model(type=Essence::class, groups={"read"}))
     *      )
     * )
     * @SWG\Tag(name="Essence")
     * @return JsonResponse
     */
    public function index(Request $request, Essence $essence):JsonResponse
    {
        $data = $this->_service->getEssence($request, $essence);
        return new JsonResponse(
            $data['data'],
            $data['statusCode']
        );
    }

    /**
     * @Route("/{id}", name="update_essence", methods="PUT", requirements={"id"="\d+"})
     * @param Essence $essence
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request, Essence $essence):JsonResponse
    {
        $data = $this->_service->update($request, $essence);
        return new JsonResponse(
            $data['data'],
            $data['statusCode']
        );
    }

    /**
     * @Route("/{id}", name="delete_essence", methods="DELETE", requirements={"id"="\d+"})
     * @param Request $request
     * @param Essence $essence
     * @SWG\Response(
     *      response=204,
     *      description="return no content",
     * )
     * @SWG\Tag(name="Essence")
     * @return JsonResponse
     */
    public function delete(Request $request, Essence $essence):JsonResponse
    {
        $data = $this->_service->delete($request, $essence);
        return new JsonResponse(
            $data['data'],
            $data['statusCode']
        );
    }

    /**
     * @Route("", name="add_essence", methods="POST")
     * @param Request $request
     * @return JsonResponse
     */
    public function add(Request $request):JsonResponse
    {
        $data = $this->_service->addEssence($request);
        return new JsonResponse(
            $data['data'],
            $data['statusCode']
        );
    }

    /**
     * @Route("/{id}/deleteImg", name="detelete_mage_essence", methods="PUT")
     * @param Request $request
     * @param Essence $essence
     * @SWG\Response(
     *    response=204,
     *    description="return no content"
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="slug of inventaire"
     * )
     * @SWG\Tag(name="Essence")
     * @return JsonResponse
     */
    public function deleteImages(Request $request, Essence $essence):JsonResponse
    {
        $data = $this->_service->deleteImg($request, $essence);
        return new JsonResponse($data['data'], $data['statusCode']);
    }

    /**
     * @Route("/{id}/rotate", name="essence_image_rotate", methods="POST")
     * @param Essence $essence
     * @param Request $request
     * @SWG\Response(
     *      response=200,
     *      description="An object message confirmation",
     * )
     * @SWG\Parameter(
     *     name="params",
     *     in="body",
     *     @SWG\Schema(type="object", required={"degre", "position"},
     *          @SWG\Property(type="integer", property="position"),
     *          @SWG\Property(type="integer", property="degre")
     *      )
     *    )
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="slug of essence"
     * )
     * @SWG\Tag(name="Essence")
     * @return JsonResponse
     */
    function turnImage(Request $request, Essence $essence): JsonResponse
    {
        $data = $this->_imageService->imagerotate($essence, $request);
        return new JsonResponse($data['data'], $data['statusCode']);
    }
}
