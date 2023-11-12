<?php

namespace App\Controller;

use App\Entity\Essence;
use App\Entity\Inventaire;
use App\Service\BevaService;
use Swagger\Annotations as SWG;
use App\Service\EpaysageService;
use App\Service\InventaireService;
use App\Repository\InventaireRepository;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/api/inventaire")
 */
class InventaireController extends AbstractController
{
    private $service;
    private $epaysageService;
    private $_bevaService;

    public function __construct(InventaireService $service,
                                EpaysageService   $epaysageService,
                                BevaService       $bevaService)
    {
        $this->service = $service;
        $this->epaysageService = $epaysageService;
        $this->_bevaService = $bevaService;
    }

    /**
     * @Route("", name="get_inventaire", methods="GET")
     * @param Request $request
     * @SWG\Response(
     *  response=200,
     *     description="return the list of a Inventaire",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Inventaire::class, groups={"read"}))
     *     )
     * )
     * @SWG\Parameter(
     *     name="page",
     *     in="query",
     *     type="number",
     *     description="set of pages"
     * )
     * @SWG\Parameter(
     *    name="limit",
     *   in="query",
     *  type="number",
     * description="set of items per page"
     *)
     * @SWG\Tag(name="Inventaire")
     * @return JsonResponse
     */
    public function list(InventaireRepository $inventaireRepository, Request $request): JsonResponse
    {
        if ($request->get('page') && $request->get('limit')) {
            $data = $this->service->getAllWithPagination($inventaireRepository, $request);
            return new JsonResponse(["count" => $data['count'], "data" => $data['data']], $data['statusCode']);
        } else {
        $data = $this->service->getAll($request);
            return new JsonResponse($data["data"], $data['statusCode']);
        }
    }

    /**
     * @Route("/finished", name="get_inventaire_finished", requirements={"finished"=".+"}, methods="POST")
     * @param Request $request
     * @SWG\Response(
     *  response=200,
     *     description="return the list of a Inventaire",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Inventaire::class, groups={"read"}))
     *     )
     * )
     * @SWG\Parameter(
     *     name="page",
     *     in="query",
     *     type="number",
     *     description="set of pages"
     * )
     * @SWG\Parameter(
     *    name="limit",
     *   in="query",
     *  type="number",
     * description="set of items per page"
     *)
     * @SWG\Tag(name="Inventaire")
     * @return JsonResponse
     */
    public function listInventoryFinished(InventaireRepository $inventaireRepository, Request $request): JsonResponse
    {
        if ($request->get('page') && $request->get('limit')) {
            $data = $this->service->getAllFinishedWithPagination($inventaireRepository, $request, true);
            return new JsonResponse(['count' => isset($data['count'])?$data['count']: 0, 'data' => $data['data']], $data['statusCode']);
        } else {
        $data = $this->service->getAllFinished($request, true);
            return new JsonResponse($data["data"], $data['statusCode']);
        }
    }

    /**
     * @Route("/notfinished", name="get_inventaire_not_finished", requirements={"finished"=".+"}, methods="POST")
     * @param Request $request
     * @SWG\Response(
     *  response=200,
     *     description="return the list of a Inventaire",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Inventaire::class, groups={"read"}))
     *     )
     * )
     * @SWG\Parameter(
     *     name="page",
     *     in="query",
     *     type="number",
     *     description="set of pages"
     * )
     * @SWG\Tag(name="Inventaire")
     * @return JsonResponse
     */
    public function listInventorynotFinished(InventaireRepository $inventaireRepository,Request $request): JsonResponse
    {
        if ($request->get('page') && $request->get('limit')) {
            $data = $this->service->getAllFinishedWithPagination($inventaireRepository, $request, false);
            return new JsonResponse(['count' => $data['count'], 'data' => $data['data']], $data['statusCode']);
        } else {
        $data = $this->service->getAllFinished($request, false);
        return new JsonResponse($data['data'], $data['statusCode']);
        }
    }

    /**
     * @Route("/{id}", name="show_inventaire", methods="GET", requirements={"id"="\d+"})
     * @param Request $request
     * @param Inventaire $inventaire
     * @SWG\Response(
     *      response=200,
     *      description="return an object of a inventaire",
     *      @SWG\Schema(
     *         type="object",
     *         @SWG\Items(ref=@Model(type=Inventaire::class, groups={"read"}))
     *      )
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="slug of inventaire"
     * )
     * @SWG\Tag(name="Inventaire")
     * @return JsonResponse
     */
    public function show(Inventaire $inventaire, Request $request): JsonResponse
    {
        $data = $this->service->getOne($request, $inventaire);
        return new JsonResponse($data['data'], $data['statusCode']);
    }

    /**
     * @Route("/{id}", name="delete_inventaire", methods="DELETE")
     * @param Request $request
     * @param Inventaire $inventaire
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
     * @SWG\Tag(name="Inventaire")
     * @return JsonResponse
     */
    public function delete(Request $request, Inventaire $inventaire): JsonResponse
    {
        // GET REPOSITORY ARBRE OR EPAYSAGE
        $data = $this->service->deleteSingleInventaire($request, $inventaire);
        return new JsonResponse($data['data'], $data['statusCode']);
    }

    /**
     * @Route("/{id}/arbre", name="inventaire_edit_arbre", methods="PUT")
     * @return JsonResponse
     * @var Request $request
     * @var Inventaire $inventaire
     */
    public function edit(Request $request, Inventaire $inventaire): JsonResponse
    {
        $data = $this->service->updateArbre($request, $inventaire);
        return new JsonResponse($data['data'], $data['statusCode']);
    }

    /**
     * @Route("/{id}/epaysage", name="inventaire_edit_epaysage", methods="PUT")
     * @param Inventaire $inventaire
     * @param Request $request
     * @return JsonResponse
     */
    public function updateEpaysage(Inventaire $inventaire, Request $request): JsonResponse
    {
        $data = $this->epaysageService->update($request, $inventaire);
        return new JsonResponse($data['data'], $data['statusCode']);
    }

    /**
     * Set beva inventory
     * @Route("/{id}/beva", name="calcul_beva", methods="POST")
     * @param Request $request
     * @SWG\Response(
     *    response=200,
     *    description="Return an object message confirmation"
     * )
     * @SWG\Parameter(
     *     name="params",
     *     in="body",
     *     @SWG\Schema(type="object", required={"healthIndex", "varietyGrade", "aestheticIndex", "locationIndex"},
     *          @SWG\Property(type="integer", property="healthIndex"),
     *          @SWG\Property(type="integer", property="varietyGrade" ),
     *          @SWG\Property(type="integer", property="aestheticIndex" ),
     *          @SWG\Property(type="integer", property="locationIndex"),
     *          @SWG\Property(type="integer", property="healthColumn"),
     *          @SWG\Property(type="integer", property="aestheticColumn")
     *      )
     *    )
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="slug of inventaire"
     * )
     * @SWG\Tag(name="Inventaire")
     * @param Inventaire $inventaire
     * @return JsonResponse
     */
    public function calculBeva(Request $request, Inventaire $inventaire): JsonResponse
    {
        // BEVA ARBRE
        $datas = $this->_bevaService->calculBeva($request, $inventaire);
        return new JsonResponse($datas['data'], $datas['statusCode']);
    }

    /**
     * set beva essence
     * @Route("/{id}/bevaEssence", name="calcul_beva_essence", methods="POST")
     * @param Request $request
     * @param Essence $essence
     * @SWG\Response(
     *    response=200,
     *    description="Return an object message confirmation"
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="slug of essence"
     * )
     * @SWG\Parameter(
     *     name="params",
     *     in="body",
     *     @SWG\Schema(type="object", required={"healthIndex", "varietyGrade", "aestheticIndex", "locationIndex"},
     *          @SWG\Property(type="integer", property="healthIndex"),
     *          @SWG\Property(type="integer", property="varietyGrade" ),
     *          @SWG\Property(type="integer", property="aestheticIndex" ),
     *          @SWG\Property(type="integer", property="locationIndex"),
     *          @SWG\Property(type="integer", property="healthColumn"),
     *          @SWG\Property(type="integer", property="aestheticColumn")
     *      )
     *    )
     * )
     * @SWG\Tag(name="Inventaire")
     * @return JsonResponse
     */
    public function calculBevaEssence(Request $request, Essence $essence): JsonResponse
    {
        $datas = $this->_bevaService->calculBevaEssence($request, $essence);
        return new JsonResponse($datas['data'], $datas['statusCode']);
    }

    /**
     * @Route("/search", name="search_inventaire", methods="POST")
     * @param Request $request
     * @SWG\Response(
     *  response=200,
     *     description="return the list of a Inventaire",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Inventaire::class, groups={"read"}))
     *     )
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
     *          @SWG\Property(type="text", property="infos", description="Address or ville inventory")
     *      )
     *    )
     * )
     * @SWG\Tag(name="Inventaire")
     * @return JsonResponse
     */
    public function searchInventaire(Request $request): JsonResponse
    {
        $data = $this->service->searchInventaire($request);
        return new JsonResponse($data['data'], $data['statusCode']);
    }


    /**
     * @Route("/isFinished", name="valid_many_inventaire", methods="POST")
     * @param Request $request
     * @return JsonResponse
     */
    public function validManyInventaire(Request $request): JsonResponse
    {
        $data = $this->service->validMany($request);
        return new JsonResponse(
            $data['data'],
            $data['statusCode']
        );
    }

    /**
     * @Route("/deleteMany", name="delete_many_inventaire", methods="POST")
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteManyInventaire(Request $request): JsonResponse
    {
        $data = $this->service->deleteManyInventaire($request);
        return new JsonResponse(
            $data['data'],
            $data['statusCode']
        );
    }

    /**
     * @Route("/validTravaux", name="valid_travaux", methods="POST")
     * @param Request $request
     * @return JsonResponse
     */
    public function effectuerTravaux(Request $request): JsonResponse
    {
        $data = $this->service->validTravaux($request);
        return new JsonResponse(
            $data['data'],
            $data['statusCode']
        );
    }

    /**
     * @Route("/stat", name="get_stat_inventory_user", methods="GET")
     * @param Request $request
     * @return JsonResponse
     */
    public function statInventaires(Request $request): JsonResponse
    {
        $result = $this->service->getStatInventory($request);
        return $this->json($result['data'], $result['statusCode']);
    }

    /**
     * Get inventory list by position around 5 kilometers
     * @param Request $request
     * @return JsonResponse
     * @Route("/position", name="get_inventaire_position", methods="POST")
     * @SWG\Response(
     *      response=200,
     *      description="return the list of a Inventaire by position",
     *      @SWG\Schema(
     *          type="array",
     *          @SWG\Items(ref=@Model(type=Inventaire::class, groups={"read"}))
     *      )
     *  )
     * @SWG\Parameter(
     *     name="params",
     *     in="body",
     *     @SWG\Schema(type="object", required={"lat", "lng"},
     *          @SWG\Property(type="number", property="lat", description="Latitude"),
     *          @SWG\Property(type="number", property="lng", description="Longitude"),
     *          @SWG\Property(type="text", property="critere", description="Arbre remarquable"),
     *          @SWG\Property(type="number", property="espece"),
     *          @SWG\Property(type="text", property="isFinished"),
     *          @SWG\Property(type="text", property="codeSite"),
     *          @SWG\Property(type="text", property="position")
     *      )
     *    )
     * )
     * @SWG\Tag(name="Inventaire")
     * @return JsonResponse
     */
    public function inventoryByPosition(Request $request): JsonResponse
    {
        $result = $this->service->getAllByPosition($request);

        return $this->json($result['data'], $result['statusCode']);
    }


    /**
     * Upload inventory list from a CSV file
     *
     * @Route("/upload", name="upload_inventaires", methods={"POST"})
     * @SWG\Post(
     *     summary="Upload inventory list from a CSV file",
     *     @SWG\Response(
     *         response=200,
     *         description="Returns an array of Inventaire objects",
     *         @SWG\Schema(
     *             type="array",
     *             @SWG\Items(ref=@Model(type=Inventaire::class, groups={"read"}))
     *         )
     *     ),
     *     @SWG\Parameter(
     *         name="file",
     *         in="formData",
     *         type="file",
     *         description="Inventory CSV file to upload",
     *         required=true
     *     ),
     *     @SWG\Tag(name="Inventaire")
     * )
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadInventory(Request $request): JsonResponse
    {
        $result = $this->service->uploadInventoryFile($request);

        return $this->json($result['message'], $result['errorCode']);
    }
}