<?php

namespace App\Controller;

use App\Entity\Groupe;
use App\Service\GroupeService;
use App\Service\StripeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;


/**
 * @Route("/api/groupe")
 */
class GroupeController extends AbstractController
{
    private $_service;
    private $_stripeService;

    public function __construct(GroupeService $service, StripeService $stripeService)
    {
        $this->_service = $service;
        $this->_stripeService = $stripeService;
    }

    /**
     * @Route("", name="groupe_index", methods="GET")
     * @param Request $request
     * @return JsonResponse
     * @SWG\Response(
     *  response=200,
     *     description="return the list of a forfait",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Groupe::class, groups={"read"}))
     *     )
     * )
     * @SWG\Tag(name="Groupe")
     */
    public function list(Request $request): JsonResponse
    {
        $data = $this->_service->groupes($request);
        return $this->json($data['data'], $data['statusCode']);
    }

    /**
     * @Route("", name="groupe_new", methods="POST")
     * @param Request $request
     * @return JsonResponse
     * @SWG\Response(
     *  response=201,
     *     description="return an objectif of Groupe",
     *     @SWG\Schema(
     *         type="Groupe",
     *         @SWG\Items(ref=@Model(type=Groupe::class, groups={"post"}))
     *     )
     * )
     * @SWG\Parameter(
     *     name="forfait",
     *     in="body",
     *     @Model(type=Groupe::class, groups={"post"})
     * )
     * @SWG\Tag(name="Groupe")
     */
    public function new(Request $request): JsonResponse
    {
        return $this->_service->add($request);
    }

    /**
     * @Route("/{id}", name="groupe_show", methods="GET", requirements={"id"="\d+"})
     * @param Request $request
     * @param Groupe $groupe
     * @return JsonResponse
     * @SWG\Response(
     *  response=200,
     *     description="return an object of Groupe",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Items(ref=@Model(type=Groupe::class, groups={"post"}))
     *     )
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="slug of forfait"
     * )
     * @SWG\Tag(name="Groupe")
     */
    public function show(Groupe $groupe, Request $request): JsonResponse
    {
        $data = $this->_service->groupe($request, $groupe);
        return $this->json($data['data'], $data['statusCode']);
    }

    /**
     * @Route("/{id}", name="groupe_edit", methods="PUT", requirements={"id"="\d+"})
     * @param Request $request
     * @param Groupe $groupe
     * @return JsonResponse
     * @SWG\Response(
     *  response=200,
     *     description="return an object of Groupe",
     *     @SWG\Schema(
     *         type="Groupe",
     *         @SWG\Items(ref=@Model(type=Groupe::class, groups={"read"}))
     *     )
     * )
     * @SWG\Parameter(
     *     name="forfait",
     *     in="body",
     *     @Model(type=Groupe::class, groups={"post"})
     * )
     * @SWG\Tag(name="Groupe")
     */
    public function edit(Request $request, Groupe $groupe): JsonResponse
    {
        $data = $this->_service->update($request, $groupe);
        return $this->json($data['data'], $data['statusCode']);
    }

    /**
     * @Route("/{id}", name="groupe_delete", methods="DELETE", requirements={"id"="\d+"})
     * @param Request $request
     * @param Groupe $groupe
     * @return JsonResponse
     * @SWG\Response(
     *  response=204,
     *  description="Return no content",
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="slug of groupe"
     * )
     * @SWG\Tag(name="Groupe")
     */
    public function delete(Request $request, Groupe $groupe): JsonResponse
    {
        $data = $this->_service->delete($request, $groupe);
        return $this->json($data['data'], $data['statusCode']);
    }

    /**
     * @Route("/{id}/upload", name="groupe_upload", methods="POST")
     * @param Request $request
     * @param Groupe $groupe
     * @return JsonResponse
     * @SWG\Response(
     *      response=200,
     *     description="return the string"
     * )
     * @SWG\Parameter(
     *     name="img",
     *     in="formData",
     *     description="Logo of society",
     *     type="file"
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="slug of groupe"
     * )
     * @SWG\Tag(name="Groupe")
     */
    public function uploadLogo(Groupe $groupe, Request $request): JsonResponse
    {
        $data = $this->_service->uploadImg($groupe, $request);
        return $this->json($data['data'], $data['statusCode']);
    }
    /**
     * @Route("/getNoStripped", name="get_group_not_stripped", methods="GET")
     * @param Request $request
     * @return JsonResponse
     * @SWG\Response(
     *  response=200,
     *     description="return the list of a forfait",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Groupe::class, groups={"read"}))
     *     )
     * )
     * @SWG\Tag(name="Groupe")
     */
    public function getGroupNoStripped(Request $request): JsonResponse {
        $data = $this->_service->getNoStripped($request);
        return $this->json($data['data'], $data['statusCode']);
    }

    /**
     * @Route("/changeModeStripped", name="change_group_stripped", methods="PATCH")
     * @param Request $request
     * @return JsonResponse
     * @SWG\Response(
     *  response=200,
     *     description="return an object of Groupe",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Items(ref=@Model(type=Groupe::class, groups={"read"}))
     *     )
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
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="slug of groupe"
     * )
     * @SWG\Tag(name="Groupe")
     */
    public function changeModeStripped(Request $request): JsonResponse {
        $data = $this->_service->changeModeGroupeStripped($request);
        return $this->json($data['data'], $data['statusCode']);
    }
}
