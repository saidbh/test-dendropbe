<?php

namespace App\Controller;

use App\Service\StripeService;
use App\Validator\Stripe\CreateCodePromosValidator;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class StripeController
 * @Route("/api/stripe")
 */
class StripeController extends AbstractController
{
    private $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    /**
     * @Route("/coupons", methods={"GET"})
     * @SWG\Response(
     *  response=200,
     *     description="return the list of coupons",
     * )
     * @SWG\Tag(name="Coupon")
     */
    public function getCoupons(Request $request): JsonResponse
    {
        $response = $this->stripeService->formattedCoupons($request);
        return new JsonResponse($response['data'], $response['statusCode']);
    }

    /**
     * Get promo code
     * @Route("/codepromos", methods={"POST"})
     * @param Request $request
     * @SWG\Parameter(
     *     name="params",
     *     in="body",
     *     @SWG\Schema(type="object", required={"discount", "codeForfait"},
     *          @SWG\Property(type="string", property="discount", description="code de reduction"),
     *          @SWG\Property(type="string", property="codeForfait", description="Code forfait")
     *      )
     *    )
     * )
     * @SWG\Response(
     *  response=200,
     *     description="return promo code object",
     * )
     * @SWG\Tag(name="Coupon")
     * @return JsonResponse
     */
    public function getCodePromos(Request $request): JsonResponse
    {
        $response = $this->stripeService->codePromoFormatted($request);
	    return new JsonResponse($response['data'], $response['statusCode']);
    }

    /**
     * Delete promo code
     * @Route("/delete/codepromos", methods={"POST"})
     * @param Request $request
     * @SWG\Parameter(
     *     name="params",
     *     in="body",
     *     @SWG\Schema(type="object", required={"promotionCodeId"},
     *          @SWG\Property(type="string", property="promotionCodeId", description="Id code de reduction"),
     *      )
     *    )
     * )
     * @SWG\Response(
     *  response=200,
     *     description="return promo code object",
     * )
     * @SWG\Tag(name="Coupon")
     * @return JsonResponse
     */
    public function deleteCodePromos(Request $request): JsonResponse
    {
        $response = $this->stripeService->deletePmotionCode($request);
        return new JsonResponse($response);
    }

    /**
     * Create promo code
     * @Route("/create/codepromos", methods={"POST"})
     * @param Request $request
     * @param ValidatorInterface $validator
     * @return JsonResponse
     * @SWG\Parameter(
     *     name="params",
     *     in="body",
     *     @SWG\Schema(type="object", required={"promotionCode", "quantity", "start_date","end_date", "type", "value"},
     *          @SWG\Property(type="string", property="promotionCode", description="code de reduction"),
     *          @SWG\Property(type="string", property="quantity", description="Quantity de reduction"),
     *          @SWG\Property(type="string", property="start_date", description="date debut de reduction"),
     *          @SWG\Property(type="string", property="end_date", description="date fin de reduction"),
     *          @SWG\Property(type="string", property="type", description="type de reduction"),
     *          @SWG\Property(type="string", property="value", description="valeur de reduction"),
     *      )
     *    )
     * )
     * @SWG\Response(
     *  response=200,
     *     description="return promo object",
     * )
     * @SWG\Tag(name="Coupon")
     */
    public function createCodePromos(Request $request, ValidatorInterface $validator): JsonResponse
    {
        if (count($validator->validate(new CreateCodePromosValidator(json_decode($request->getContent(),true))))) {
            return new JsonResponse([
                'data' => [
                    'message' => 'error_params',
                ],
                'statusCode' => 400
            ]);
        }
        $response = $this->stripeService->createPromotionCode($request);
        return new JsonResponse($response);
    }

    /**
     * @Route("/coupons/customer/{id}", methods={"GET"})
     * @SWG\Response(
     *  response=200,
     *     description="return list of cutomers by coupon",
     * )
     * @SWG\Tag(name="Coupon")
     */
    public function cutomsersByCoupon($id)
    {
        $response = $this->stripeService->getCustomersByCoupon($id);
        return new JsonResponse($response);
    }
}
