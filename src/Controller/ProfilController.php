<?php

namespace App\Controller;

use App\Entity\Profil;
use App\Service\ProfilService;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/profil")
 */
class ProfilController extends AbstractController
{
    private $_service;

    public function __construct(ProfilService $service)
    {
        $this->_service = $service;
    }

    /**
     * @Route("", name="profil_index", methods="GET")
     * @return JsonResponse
     * @var Request $request
     * @SWG\Response(
     *  response=200,
     *     description="return the list of a Profil",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Profil::class, groups={"read"}))
     *     )
     * )
     * @SWG\Tag(name="Profil")
     */
    public function index(Request $request): JsonResponse
    {
        $data = $this->_service->getAll($request);
        return $this->json($data['data'], $data['statusCode']);
    }

    /**
     * @Route("", name="profil_new", methods="POST")
     * @return JsonResponse
     * @var Request $request
     * @SWG\Response(
     *  response=201,
     *     description="return an object of a Profil",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Items(ref=@Model(type=Profil::class, groups={"read"}))
     *     )
     * )
     * @SWG\Parameter(
     *     name="params",
     *     in="body",
     *     @SWG\Schema(type="object",
     *          @SWG\Property(type="string", property="name"),
     *          @SWG\Property(type="integer", property="droit"),
     *          @SWG\Property(type="string", property="groupeType")
     *      )
     *    )
     * )
     * @SWG\Tag(name="Profil")
     */
    public function new(Request $request): JsonResponse
    {
        $data = $this->_service->add($request);
        return $this->json($data['data'], $data['statusCode']);
    }

    /**
     * @Route("/{id}", name="profil_show", methods="GET", requirements={"id"="\d+"})
     * @return JsonResponse
     * @var Request $request
     * @var Profil $profil
     * @SWG\Response(
     *  response=200,
     *     description="return an object of a Profil",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Items(ref=@Model(type=Profil::class, groups={"read"}))
     *     )
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="slug of profil"
     * )
     * @SWG\Tag(name="Profil")
     */
    public function show(Request $request, Profil $profil): JsonResponse
    {
        $data = $this->_service->getOne($request, $profil);
        return $this->json($data['data'], $data['statusCode']);
    }

    /**
     * @Route("/{id}", name="profil_edit", methods="PUT", requirements={"id"="\d+"})
     * @return JsonResponse
     * @var Request $request
     * @var Profil $profil
     * @SWG\Response(
     *  response=200,
     *     description="return an object of a Profil",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Items(ref=@Model(type=Profil::class, groups={"read"}))
     *     )
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="slug of profil"
     * )
     * @SWG\Parameter(
     *     name="params",
     *     in="body",
     *     @SWG\Schema(type="object",
     *          @SWG\Property(type="string", property="name"),
     *          @SWG\Property(type="integer", property="droit"),
     *          @SWG\Property(type="string", property="groupeType")
     *      )
     *    )
     * )
     * @SWG\Tag(name="Profil")
     */
    public function edit(Request $request, Profil $profil): JsonResponse
    {
        $data = $this->_service->edit($request, $profil);
        return $this->json($data['data'], $data['statusCode']);
    }

    /**
     * @Route("/{id}", name="profil_delete", methods="DELETE", requirements={"id"="\d+"})
     * @param Request $request
     * @param Profil $profil
     * @SWG\Response(
     *     response=204,
     *     description="Return no content",
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="slug of profil"
     * )
     * @SWG\Tag(name="Profil")
     * @return JsonResponse
     */
    public function delete(Request $request, Profil $profil): JsonResponse
    {
        $data = $this->_service->delete($request, $profil);
        return $this->json($data['data'], $data['statusCode']);
    }

    /**
     * @Route("/profilGroupe", name="get_profil_groupe", methods="POST")
     * @return JsonResponse
     * @var Request $request
     * @SWG\Response(
     *  response=200,
     *     description="return the list of a Profil",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Profil::class, groups={"read"}))
     *     )
     * )
     * @SWG\Parameter(
     *     name="params",
     *     in="body",
     *     @SWG\Schema(type="object",
     *          @SWG\Property(type="string", property="id"),
     *      )
     *    )
     * )
     * @SWG\Tag(name="Profil")
     */
    public function getProfilGroupe(Request $request): JsonResponse
    {
        $data = $this->_service->getProfilGroupe($request);
        return $this->json($data['data'], $data['statusCode']);
    }
}
