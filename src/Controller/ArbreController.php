<?php

namespace App\Controller;

use App\Entity\Arbre;
use App\Entity\Inventaire;
use App\Service\ArbreService;
use App\Service\ImageService;
use App\Service\InventaireService;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/arbre")
 */
class ArbreController extends AbstractController
{
    private $imageService;
    private $_arbreService;
    private $_inventaireService;

    public function __construct(ImageService $imageService, ArbreService $arbreService, InventaireService $inventaireService)
    {
        $this->imageService = $imageService;
        $this->_arbreService = $arbreService;
        $this->_inventaireService = $inventaireService;
    }

    /**
     * @Route("", name="arbre_new", methods="POST")
     * @param Request $request
     * @SWG\Response(
     *  response=200,
     *     description="return an object of a Inventaire",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Items(ref=@Model(type=Inventaire::class, groups={"read"}))
     *     )
     * )
     * @SWG\Parameter(
     *     name="params",
     *     in="body",
     *     @SWG\Schema(type="object", required={"coord", "espece", "diametre", "hauteur"},
     *          @SWG\Property(type="object", property="coord",
     *               @SWG\Items(type="object",
     *                    @SWG\Property(type="string", property="lat"),
     *                    @SWG\Property(type="string", property="long"),
     *              )
     *          ),
     *          @SWG\Property(type="integer", property="espece"),
     *          @SWG\Property(type="integer", property="diametre"),
     *          @SWG\Property(type="string", property="codeSite"),
     *          @SWG\Property(type="string", property="numSujet")
     *      )
     *    )
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="slug of Inventaire"
     * )
     * @SWG\Tag(name="Arbre")
     * @return JsonResponse
     */
    public function new(Request $request): JsonResponse
    {
        $data = $this->_inventaireService->addTreeOrInventory($request, 'ARBRE');
        return $this->json($data['data'], $data['statusCode']);
    }

    /**
     * @Route("/{id}/upload", name="arbre_upload", methods="POST")
     * @param Arbre $arbre
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
     *     description="slug of Arbre"
     * )
     * @SWG\Tag(name="Arbre")
     * @param Request $request
     * @return JsonResponse
     */
    public function upload(Arbre $arbre, Request $request): JsonResponse
    {
        $data = $this->imageService->uploadImageInv($request, $arbre);
        return $this->json($data['data'], $data['statusCode']);
    }

    /**
     * @Route("/{id}/rotate", name="arbre_image_rotate", methods="POST")
     * @param Request $request
     * @param Arbre $arbre
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
     *     description="slug of arbre"
     * )
     * @SWG\Tag(name="Arbre")
     * @return JsonResponse
     */
    function turnImage(Request $request, Arbre $arbre): JsonResponse
    {
        $data = $this->imageService->imagerotate($arbre, $request);
        return $this->json($data['data'], $data['statusCode']);
    }

    /**
     * @Route("/{id}/deleteImg", name="arbre_image_delete", methods="PUT")
     * @param Request $request
     * @param Arbre $arbre
     * @SWG\Response(
     *      response=200,
     *      description="An object of address and ville",
     * )
     * @SWG\Parameter(
     *     name="params",
     *     in="body",
     *     @SWG\Schema(type="object", required={"lat", "long"},
     *          @SWG\Property(type="float", property="lat"),
     *          @SWG\Property(type="float", property="long")
     *      )
     *    )
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="slug of Inventaire"
     * )
     * @SWG\Tag(name="Arbre")
     * @return JsonResponse
     */
    public function supprimerImg(Request $request, Arbre $arbre, ArbreService $service): JsonResponse
    {
        $data = $service->deleteImg($request, $arbre);
        return $this->json($data['data'], $data['statusCode']);
    }

    /**
     * @Route("/{id}/changeCoord", name="change_coord_tree", methods="PATCH")
     * @param Arbre $arbre
     * @param Request $request
     * @SWG\Response(
     *      response=200,
     *      description="An object of address and ville",
     * )
     * @SWG\Parameter(
     *     name="params",
     *     in="body",
     *     @SWG\Schema(type="object", required={"lat", "long", "ville", "address"},
     *          @SWG\Property(type="float", property="lat", maxLength=255),
     *          @SWG\Property(type="float", property="long", maxLength=255),
     *          @SWG\Property(type="string", property="ville"),
     *          @SWG\Property(type="string", property="address", maxLength=255),
     *       )
     *    )
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="slug of arbre"
     * )
     * @SWG\Tag(name="Arbre")
     * @return JsonResponse
     */
    public function updateCoord(Request $request, Arbre $arbre): JsonResponse
    {
        $data = $this->_arbreService->updateCoordinate($request, $arbre);
        return $this->json($data['data'], $data['statusCode']);
    }

    /**
     * @Route("/{id}/addressVille", name="get_address_coord", methods="POST")
     * @param Request $request
     * @SWG\Response(
     *      response=200,
     *      description="An object of address and ville",
     * )
     * @SWG\Parameter(
     *     name="params",
     *     in="body",
     *     @SWG\Schema(type="object", required={"lat", "long"},
     *          @SWG\Property(type="float", property="lat"),
     *          @SWG\Property(type="float", property="long"),
     *      )
     *    )
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="slug of Inventaire"
     * )
     * @SWG\Tag(name="Arbre")
     * @return JsonResponse
     */
    public function getAdressCoord(Request $request): JsonResponse
    {
        $data = $this->_arbreService->getAddressWithCoord($request);
        return $this->json($data['data'], $data['statusCode']);
    }
}
