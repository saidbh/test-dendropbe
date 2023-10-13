<?php

namespace App\Controller;

use App\Entity\Epaysage;
use App\Entity\Essence;
use App\Service\EpaysageService;
use App\Service\ImageService;
use App\Service\InventaireService;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/epaysage")
 */
class EpaysageController extends AbstractController
{

    private $service;
    private $_imageService;
    private $_inventaireService;

    public function __construct(EpaysageService $service, ImageService $imageService, InventaireService $inventaireService)
    {
        $this->service = $service;
        $this->_imageService = $imageService;
        $this->_inventaireService = $inventaireService;
    }

    /**
     * @Route("", name="epaysage_new", methods="POST")
     * @param Request $request
     * @return JsonResponse
     */
    public function new(Request $request): JsonResponse
    {
        $data = $this->_inventaireService->addTreeOrInventory($request);
        return new JsonResponse($data['data'], $data['statusCode']);
    }

    /**
     * @Route("/{id}/upload", name="essence_upload", methods="POST")
     * @param Request $request
     * @param Essence $essence
     * @SWG\Response(
     *     response=200,
     *     description="return an object message confirmation"
     * )
     * @SWG\Parameter(
     *     name="img1",
     *     in="formData",
     *     description="Image arbre 1",
     *     type="file"
     * )
     * @SWG\Parameter(
     *     name="img2",
     *     in="formData",
     *     description="Image arbre 2",
     *     type="file"
     * )
     * @SWG\Parameter(
     *     name="img3",
     *     in="formData",
     *     description="Image arbre 3",
     *     type="file"
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="slug of Essence"
     * )
     * @SWG\Tag(name="Epaysage")
     * @return JsonResponse
     */
    public function upload(Essence $essence, Request $request): JsonResponse
    {
        // UPLOAD IMAGE ESSENCES
        $data = $this->_imageService->uploadImageInv($request, $essence);
        return new JsonResponse($data['data'], $data['statusCode']);
    }

    /**
     * @Route("/{id}", name="get_inventaire_by_epaysage_id", methods="GET")
     * @param Request $request
     * @param Epaysage $epaysage
     * @SWG\Response(
     *  response=200,
     *     description="return an object of a User",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Items(ref=@Model(type=Epaysage::class, groups={"read"}))
     *     )
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="slug of epaysage"
     * )
     * @SWG\Tag(name="Epaysage")
     * @return JsonResponse
     */
    public function getOne(Request $request, Epaysage $epaysage): JsonResponse
    {
        $data = $this->_inventaireService->getOneInventaireByEpaysage($request, $epaysage);
        return new JsonResponse($data['data'], $data['statusCode']);
    }

}
