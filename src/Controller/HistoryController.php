<?php

namespace App\Controller;

use App\Entity\Arbre;
use App\Entity\Inventaire;
use App\Service\ArbreService;
use App\Service\HistoryService;
use App\Service\ImageService;
use App\Service\InventaireService;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/history")
 */
class HistoryController extends AbstractController
{
    /**
     * @Route("/works/list", name="get_travaux_list", methods="GET")
     * @param Request $request
     * @param HistoryService $historyService
     * @return JsonResponse
     * @SWG\Response(
     *      response=200,
     *      description="An object message confirmation",
     * )
     * @SWG\Tag(name="WorkHistory")
     */
    public function GetTravauxHistoryList(Request  $request,HistoryService $historyService):JsonResponse
    {
        $data = $historyService->getHistoryList($request);
        return $this->json($data['data'], $data['errorCode']);

    }

    /**
     * @Route("/works/per/inventaire", name="get_history_per_inventaire", methods="GET")
     * @param Request $request
     * @param HistoryService $historyService
     * @return JsonResponse
     * @SWG\Response(
     *   response=200,
     *      description="return the list of a Works per inventiare",
     *      @SWG\Schema(
     *          type="array",
     *          @SWG\Items(ref=@Model(type=History::class))
     *      )
     *  )
     * @SWG\Parameter(
     *      name="inventaireId",
     *      in="query",
     *      type="number",
     *      description="Id of inventiare"
     *  )
     * @SWG\Tag(name="WorkHistory")
     */
    public function GetHistoryPerInventaireList(Request  $request,HistoryService $historyService):JsonResponse
    {
        $data = $historyService->getHistoryPerInventaire($request);
        return $this->json($data['data'], $data['errorCode']);

    }

    /**
     * @Route("/uploadfiles", name="upload_files", methods={"POST"})
     * @SWG\Post(
     *     path="/api/history/uploadfiles",
     *     summary="Upload files for history",
     *     consumes={"multipart/form-data"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="historyid",
     *         in="formData",
     *         type="string",
     *         description="History ID",
     *         required=true,
     *         default=""
     *     ),
     *     @SWG\Parameter(
     *         name="files[]",
     *         in="formData",
     *         type="file",
     *         description="Files to upload",
     *         required=true,
     *         collectionFormat="multi",
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Files uploaded successfully",
     *         @SWG\Schema(
     *             type="object",
     *             properties={
     *                 @SWG\Property(property="status", type="string", description="Status of the upload"),
     *                 @SWG\Property(property="message", type="string", description="Message regarding the upload"),
     *             }
     *         ),
     *     ),
     *     @SWG\Response(response=400, description="Bad request"),
     * )
     */
    public function uploadFiles(Request $request, HistoryService $historyService)
    {
        $data = $historyService->uploadHistoryDocs($request);
        return $this->json($data['data'], $data['errorCode']);
    }



}