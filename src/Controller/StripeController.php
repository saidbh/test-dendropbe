<?php

namespace App\Controller;

use App\Service\StripeService;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

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
     * @Route("/coupons/customers/{id}", methods={"GET"})
     * @SWG\Response(
     *  response=200,
     *     description="return list of cutomers by coupon",
     * )
     * @SWG\Tag(name="Coupon")
     */
    public function cutomsersByCoupon($id)
    {
        return new JsonResponse($this->stripeService->getCustomersByCoupon($id));
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
}
