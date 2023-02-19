<?php

namespace App\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Stripe\Coupon;
use Stripe\Customer;
use Stripe\Stripe;
use Stripe\Subscription;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request as RequestService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Serializer;

class StripeService extends AbstractController
{
    private $_tokenService;
    private $_forfaitService;

    public function __construct(TokenService $tokenService, ForfaitService $forfaitService)
    {
        $this->_tokenService = $tokenService;
        $this->_forfaitService = $forfaitService;
    }
    // FOR A SUBSCRIPTION
    /*
     * We need customer, product,
     * plan : how much you will charge for a product
     */

    /*
     * First create a payment information (card_id)
     */
    public function createCard(array $dataUser)
    {
        // CREATE CARD ID
        // $this->createToken
        Stripe::setApiKey(getenv('STRIPE_PUBLIC_KEY'));

        $ccExp = explode('/', $dataUser['ccExp']);

        $dataUser['monthExp'] = $ccExp[0];
        $dataUser['yearExp'] = $ccExp[1];

        try {
            $token = \Stripe\Token::create([
                "card" => [
                    'number' => $dataUser['ccNumber'],
                    'exp_month' => $dataUser['monthExp'],
                    'exp_year' => $dataUser['yearExp'],
                    'cvc' => $dataUser['ccCvc'],
                    'name' => $dataUser['nameCard']
                ],
            ]);
            return ['token' => $token];
        } catch (\Stripe\Error\Card $e) {
            return ['error' => $e];
        }
    }

    public function setCustomer(array $data)
    {
        // Just need an email and sourceId :
        Stripe::setApiKey(getenv('STRIPE_SECRET_KEY'));
        $customer = new Customer();
        $address = [
            "line1" => isset($data['address2']) ? $data['address2'] : null, // name of society
            "line2" => $data['address'],
            "city" => $data['city'],
            "country" => $data['country'],
            "postal_code" => $data['zipCode'],
        ];
        return $customer::create([
            'email' => $data['email'],
            'source' => $data['token']->id,
            "address" => $address,
            "shipping" => [
                "address" => $address,
                "name" => $data['name'],
                "phone" => isset($data['phoneNumber']) ? $data['phoneNumber'] : null
            ],
            "phone" => $data['phoneNumber'],
            "name" => $data['name']
        ]);
    }

    public function setSubscription(array $data, $subId = null)
    {
        Stripe::setApiKey(getenv('STRIPE_SECRET_KEY'));

        $customerID = (!isset($data['changingMode']) || !$data['changingMode'])
            ? $data['customerID'] : $this->setCustomer($data)->id;

        foreach ($this->_forfaitService->getDaysPeriodForfaits() as $item) {
            if ($item['codeForfait'] == $data['forfait']) {
                $plan = $item;
            }
        }
        $sub = new Subscription();
        $subId ? self::endSubsription($subId) : null;
        try {
            $subscription = $sub::create([
                "customer" => $customerID,
                'default_tax_rates' => [self::taxRatesBilling()],
                'coupon' => isset($data['discount']) ?
                    self::getCouponIdByPromoCode(strtoupper($data['discount']), $plan['plan']) : null,
                "items" => [
                    [
                        "plan" => $plan['plan'] == 'Agile_1M' ? 'Agile_1M_FREE' : $plan['plan']
                    ]
                ],
                "trial_end" => ($plan['plan'] == 'Agile_1M' || $plan['plan'] == 'Agile_1M_FREE') ? self::trialPeriod()->getTimestamp() : null
            ]);
            return ['sub' => $subscription];
        } catch (\Exception $e) {
            return ['error' => $e];
        }
    }

    public function updateSubscription(array $data, $subId = null)
    {
        Stripe::setApiKey(getenv('STRIPE_SECRET_KEY'));

        $customerID = (!isset($data['changingMode']) || !$data['changingMode'])
            ? $data['customerID'] : $this->setCustomer($data)->id;

        foreach ($this->_forfaitService->getDaysPeriodForfaits() as $item) {
            if ($item['codeForfait'] == $data['forfait']) {
                $plan = $item;
            }
        }
        $sub = new Subscription();
        $subId ? self::endSubsription($subId) : null;

        try {
            $subscription = $sub::create([
                "customer" => $customerID,
                'default_tax_rates' => [self::taxRatesBilling()],
                "items" => [
                    [
                        "plan" => $plan['plan']
                    ]
                ]
            ]);
            return ['sub' => $subscription];
        } catch (\Exception $e) {
            return ['error' => $e];
        }
    }

    public static function trialPeriod()
    {
        $now = new \DateTime('now');
        $intervalDays = new \DateInterval('P30D');
        return $now->add($intervalDays);
    }

    public function isSubscriptionValid($subId): bool
    {
        if (!$subId) return false;
        $url = getenv('BASE_URL_STRIPE') . '/v1/subscriptions/' . $subId;
        $subscription = self::configureHttpClientguzzle($url);
        return $subscription->status === 'active' || 'trialing';
    }

    public function setDateEcheance(string $forfait): ?\DateTime
    {
        // FIXED DATE ECHEANCE FOR
        $periode = null;
        $days = null;
        foreach ($this->_forfaitService->getDaysPeriodForfaits() as $item) {

            if ($item['codeForfait'] == $forfait) {
                $periode = $item['periode'];
                $days = $item['days'];
            }
        }
        if ($periode !== 0) {
            $startDate = new \DateTime('now');
            if ($periode == 12) {
                $interval = new \DateInterval('P1Y');
            } else if ($days == 30) {
                $interval = new \DateInterval('P' . $days . 'D');
            } else {
                $interval = new \DateInterval('P' . $periode . 'M');
            }
//            $interval = ($periode == 12) ? new \DateInterval('P1Y') :
//                ($days == 30) ? new \DateInterval('P' . $days . 'D') : new \DateInterval('P' . $periode . 'M');
            return $startDate->add($interval);
        }
        return null;
    }

    public static function endSubsription(string $subId)
    {
        Stripe::setApiKey(getenv('STRIPE_SECRET_KEY'));
        $sub = new Subscription();
        $sub::update($subId, [
                'cancel_at_period_end' => true,
            ]
        );
    }

    public static function taxRatesBilling()
    {
        // GET TAX RATES
        $url = getenv('BASE_URL_STRIPE') . '/v1/tax_rates';
        $taxes = self::configureHttpClientguzzle($url);
        foreach ($taxes->data as $tax) {
            if ($tax->active && $tax->display_name == 'TVA' && $tax->percentage == 20) {
                return $tax->id;
            }
        }
        return null;
    }

    public function getCoupons()
    {
        $url = getenv('BASE_URL_STRIPE') . '/v1/coupons';
        $coupons = self::configureHttpClientguzzle($url);
        return $coupons->data;
    }

    public function getCustomersByCoupon($id)
    {
        $url = getenv('BASE_URL_STRIPE') . '/v1/customers?coupon' . $id;
        $customers = self::configureHttpClientguzzle($url);
        $response = [];
        foreach ($customers->data as $customer) {
            $cus = new \stdClass();
            $cus->name = $customer->name;
            $cus->email = $customer->email;
            $cus->addDate = date('d-m-Y H:i', $customer->created);
            $response[] = $cus;
        }

        return $response;
    }

    /**
     * @param RequestService $request
     * @return array
     */
    public function formattedCoupons(RequestService $request): array
    {
        // AVANT ET DEBUT DE LA RECONDUCTION DU COMPTE
        $data = $this->_tokenService->MiddlewareDendroUser($request->headers->get('Authorization'));

        if (!isset($data['user']) || !$data['user']) {
            return $data;
        }

        /** @var Coupon[] $coupons */
        $coupons = $this->getCoupons();
        $response = [];

        foreach ($coupons as $coupon) {
            $coup = new \stdClass();
            $coup->id = $coupon->id;
            $coup->code = $coupon->name;
            $coup->utilisation = $coupon->times_redeemed;
            $coup->dateActivation = date('d/m/Y', $coupon->created);
            $coup->statut = $coupon->valid ? 'Actif' : 'Désactivé';

            $response[] = $coup;
        }

        return [
            'data' => $response,
            'statusCode' => Response::HTTP_OK
        ];
    }

    private static function configureHttpClientguzzle($url)
    {
        $client = new Client(['headers' =>
            [
                'content-type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . getenv('STRIPE_SECRET_KEY')
            ]
        ]);
        $request = new Request('GET', $url);
        $response = $client->send($request, ['timeout' => 7.0]);
        return json_decode($response->getBody());
    }

    /**
     * @return array
     */
    public function promotionsCode(): array {
        $url = getenv('BASE_URL_STRIPE') . '/v1/promotion_codes';
        $promotionCodes = self::configureHttpClientguzzle($url);
        // format datas
        $response = [];
        foreach ($promotionCodes->data as $promo) {
            $prom = new \stdClass();
            $prom->id = $promo->id;
            $prom->code = $promo->code; // code de reduction
            $prom->reduction = $promo->coupon->percent_off; // pourcentage
            $prom->coupon = $promo->coupon->id; // code coupons
            $prom->valid = $promo->active;
            $response[] = $prom;
        }
        return $response;
    }

    /**
     * Coupon id by PromoCode string given
     * @param string $discount
     * @param string $codeForfait
     * @return mixed|null
     */
    public function getCouponIdByPromoCode(string $discount, string $codeForfait) {
        // verify codeForfait with discount
        if(!ForfaitService::isDiscountSubscription($discount, $codeForfait)) {
            return null;
        }
        // Get the right coupon id
        $promoCodes = self::promotionsCode();
        foreach ($promoCodes as $code) {
            if((strtoupper($code->code) === $discount) && $code->valid) {
                return $code->coupon;
            }
        }
        return null;
    }

    /**
     * @param RequestService $request
     * @return array
     */
    public function codePromoFormatted(RequestService $request):array {
        // Get code forfait
        /** @var Serializer $serializer */
        $serializer = $this->get('serializer');
        $data = $serializer->decode($request->getContent(), 'json');
        // check existing variables
        if(!isset($data['discount']) || !isset($data['codeForfait'])) {
            return [
                'data' => [
                    'message' => 'Information obligatoire'
                ],
                'statusCode' => Response::HTTP_BAD_REQUEST
            ];
        }
        // Select promoCode with discount && codeForfait
	$codePromos = $this->promotionsCode();
        foreach($codePromos as $promo) {
            if((strtoupper($promo->code) === strtoupper($data['discount'])) && ForfaitService::isDiscountSubscription(strtoupper($data['discount']), $data['codeForfait'])) {
		        $promo->codeForfait = ForfaitService::getCodeForfaitByPromo($promo->code);
                    return [
                      'data' => $promo,
                      'statusCode' => Response::HTTP_OK
                    ];
            }
        }

        return [
            'data' => null,
            'statusCode' => Response::HTTP_OK
        ];
    }
}
