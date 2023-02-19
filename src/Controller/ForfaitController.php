<?php

namespace App\Controller;

use App\Entity\Forfait;
use App\Service\ForfaitService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;

/**
 * @Route("/api/forfait")
 */
class ForfaitController extends AbstractController
{
    private $forfaitService;

    public function __construct(ForfaitService $forfaitService)
    {
        $this->forfaitService = $forfaitService;
    }

    /**
     * @Route("", name="forfait_index", methods="GET")
     * @return JsonResponse
     * @param Request $request
     * @SWG\Response(
     *  response=200,
     *     description="return the list of a forfait",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Forfait::class, groups={"read"}))
     *     )
     * )
     * @SWG\Tag(name="Forfait")
     */
    public function index(Request $request): JsonResponse
    {
        $data = $this->forfaitService->getForfaits($request);
        return $this->json($data['data'], $data['statusCode']);
    }

    /**
     * @Route("", name="forfait_new", methods="POST")
     * @return JsonResponse
     * @var Request $request
     * @SWG\Response(
     *  response=201,
     *     description="Return the list of a forfait",
     *     @SWG\Schema(
     *         type="Forfait",
     *         @SWG\Items(ref=@Model(type=Forfait::class, groups={"post"}))
     *     )
     * )
     * @SWG\Parameter(
     *     name="forfait",
     *     in="body",
     *     @Model(type=Forfait::class, groups={"post"})
     * )
     * @SWG\Tag(name="Forfait")
     */
    public function new(Request $request): JsonResponse
    {
        $data = $this->forfaitService->add($request);
        return $this->json($data['data'], $data['statusCode']);
    }

    /**
     * @Route("/{id}", name="forfait_show", methods="GET", requirements={"id"="\d+"})
     * @param Request $request
     * @param Forfait $forfait
     * @return JsonResponse
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="slug of forfait"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Returns the list of a droit",
     *     @SWG\Schema(
     *         type="Forfait",
     *         @SWG\Items(ref=@Model(type=Forfait::class, groups={"read"}))
     *     )
     * )
     * @SWG\Tag(name="Forfait")
     */
    public function show(Request $request, Forfait $forfait): JsonResponse
    {
        $data = $this->forfaitService->getOne($request, $forfait);
        return $this->json($data['data'], $data['statusCode']);
    }

    /**
     * @Route("/{id}", name="forfait_edit", methods="PUT", requirements={"id"="\d+"})
     * @return JsonResponse
     * @var Request $request
     * @var Forfait $forfait
     * @SWG\Response(
     *     response=200,
     *     description="Returns a forfait",
     *     @SWG\Schema(
     *         type="Forfait",
     *         @SWG\Items(ref=@Model(type=Forfait::class, groups={"read"}))
     *     )
     * )
     * @SWG\Parameter(
     *     name="forfait",
     *     in="body",
     *     @Model(type=Forfait::class, groups={"post"})
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="slug of forfait"
     * )
     * @SWG\Tag(name="Forfait")
     */
    public function edit(Request $request, Forfait $forfait): JsonResponse
    {
        $data = $this->forfaitService->edit($request, $forfait);
        return $this->json($data['data'], $data['statusCode']);
    }
}
