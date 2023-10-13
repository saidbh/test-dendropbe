<?php

namespace App\Service;

use App\Entity\Forfait;
use App\Entity\Groupe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;

class ForfaitService extends AbstractController
{
    private $_tokenService;

    public const FORFAIT_DISCOUNT = [
        ['name' => 'Agile_1MOIS', "discount" => "HEV220915", "price" => 120, "reduction" => 15],
        ['name' => 'Agile_6MOIS', "discount" => "HEV220920", "price" => 99, "reduction" => 20],
        ['name' => 'Agile_12MOIS', "discount" => "HEV220930", "price" => 75, "reduction" => 30]
    ];

    public function __construct(TokenService $tokenService)
    {
        $this->_tokenService = $tokenService;
    }

    function getForfaits(Request $request)
    {
        $data = $this->_tokenService->MiddlewareAdminDedroUser($request->headers->get('Authorization'));
        if (!isset($data['user']) || !$data['user']) {
            return $data;
        }
        $lists = $this->getDoctrine()->getRepository(Forfait::class)->findAll();
        $lists = array_filter($lists, function (Forfait $f) use ($data) {
            return $data['user']->getGroupe()->getGroupeType() !== 'DENDROMAP' ? $f->getCodeForfait() !== '1M_FREE' : $f;
        });
        return [
            "data" => $this->generateDataArray($lists),
            "statusCode" => Response::HTTP_ACCEPTED
        ];
    }

    public function add(Request $request): array
    {
        $data = $this->_tokenService->MiddlewareAdminDedroUser($request->headers->get('Authorization'));
        if (!isset($data['user']) || !$data['user']) {
            return $data;
        }

        /** @var Serializer $serializer */
        $serializer = $this->get('serializer');
        $data = $serializer->decode($request->getContent(), 'json');

        if (!isset($data['name']) || !$data['name']) {
            return [
                'data' => ['message' => 'Informations obligatoire'],
                'statusCode' => Response::HTTP_BAD_REQUEST
            ];
        }

        if (!isset($data['codeForfait'])) {
            return [
                'data' => ['message' => 'Informations obligatoire'],
                'statusCode' => Response::HTTP_BAD_REQUEST
            ];
        }
        /** @var Forfait $forfait */
        $forfait = $serializer->denormalize($data, Forfait::class);
        $forfait->setCreatedAt(new \DateTime('now'));

        if (!$forfait instanceof Forfait) {
            return [
                'data' => ['message' => 'Informations obligatoire'],
                'statusCode' => Response::HTTP_BAD_REQUEST
            ];
        }
        $existForfait = $this->getDoctrine()->getRepository(Forfait::class)->findOneBy(['codeForfait' => $forfait->getCodeForfait()]);

        if ($existForfait instanceof Forfait) {
            return [
                "data" => [
                    "message" => "Ce forfait est deja defini",
                    "errorCode" => 302
                ], 'statusCode' => Response::HTTP_CONFLICT];
        }

        try {
            $em = $this->getDoctrine()->getManager();
            $em->persist($forfait);
            $em->flush();
            return [
                'data' => $this->generateObject($forfait), 'statusCode' => Response::HTTP_CREATED
            ];
        } catch (\Doctrine\DBAL\Exception $e) {
            return [
                'data' => [
                    "message" => "Impossible d'enrégistrer ce forfait",
                    "errorCode" => 500
                ], 'statusCode' => Response::HTTP_BAD_REQUEST
            ];
        }
    }

    public function edit(Request $request, Forfait $forfait): array
    {
        $data = $this->_tokenService->MiddlewareAdminDedroUser($request->headers->get('Authorization'));
        if (!isset($data['user']) || !$data['user']) {
            return $data;
        }

        /** @var Serializer $serializer */
        $serializer = $this->get('serializer');
        $data = $serializer->decode($request->getContent(), 'json');

        if (!isset($data['name']) || !isset($data['codeForfait'])) {
            return [
                'data' => ['message' => 'Informations obligatoire'],
                'statusCode' => Response::HTTP_BAD_REQUEST
            ];
        }
        if (!$data['name'] || !$data['codeForfait']) {
            return [
                'data' => ['message' => 'Informations obligatoire'],
                'statusCode' => Response::HTTP_BAD_REQUEST
            ];
        }
        // Serialization to an object to hydrate
        $serializer = new Serializer([CustomSerializationObject::denormalizeDateTime()], [new JsonEncoder()]);
        /** @var Forfait $forfaitSerial */
        $forfaitSerial = $serializer->denormalize($data, Forfait::class, 'json', ['object_to_populate' => $forfait]);

        $existForfait = $this->getDoctrine()->getRepository(Forfait::class)->findOneByUpdate($forfait);
        if ($existForfait instanceof Forfait) {
            return [
                "data" => ["message" => "Aucune modification sur ce profil"],
                "statusCode" => Response::HTTP_CONFLICT
            ];
        }

        $forfaitSerial->setUpdatedAt(new \DateTime('now'));

        try {
            $em = $this->getDoctrine()->getManager();
            $em->persist($forfaitSerial);
            $em->flush();
            return [
                'data' => $this->generateObject($forfaitSerial),
                'statusCode' => Response::HTTP_OK
            ];
        } catch (\Doctrine\DBAL\Exception $e) {
            return [
                'data' => [
                    "message" => "Impossible de supprimer ce forfait",
                    "errorCode" => 500
                ], Response::HTTP_BAD_REQUEST
            ];
        }
    }

    public function generateDataArray($objects)
    {
        return array_map(function (Forfait $f) {
            return self::generateObject($f);
        }, $objects);
    }

    private function generateObject(Forfait $forfait)
    {
        return $this->get('serializer')->normalize($forfait, 'json', ['groups' => ["read"]]);
    }

    public function getOne(Request $request, Forfait $object): array
    {
        $data = $this->_tokenService->MiddlewareAdminDedroUser($request->headers->get('Authorization'));
        if (!isset($data['user']) || !$data['user']) {
            return $data;
        }
        // RETURN TABLEAU
        return [
            "data" => self::generateObject($object),
            "statusCode" => Response::HTTP_OK
        ];
    }

    public static function getInfoDataForfait(string $codeForfait): array
    {
        // DEFINE ARRAY OF FORFAIT END SUBSCRIPTION DATA
        // duration and Price
        switch ($codeForfait) {
            case '1M' :
                return ['duration' => 1, 'price' => 120];

            case '6M' :
                return ['duration' => 6, 'price' => 99];

            case '12M' :
                return ['duration' => 12, 'price' => 75];

            case 'GRATUIT' :
                return [];
        }
    }

    public function getDaysPeriodForfaits(): array
    {
        $lists = $this->getDoctrine()->getRepository(Forfait::class)->findBy([]);

        $func = function (Forfait $forfait) {
            if ($forfait->getCodeForfait() == "GRATUIT") {
                $plan = "GRATUIT";
                $periode = 6;
                $days = 0;
                $desc = "Forfait flex";
            } else if ($forfait->getCodeForfait() == "1M") {
                $plan = "Agile_1M";
                $periode = 1;
                $days = 0;
                $desc = "Forfait Agile payable par mois pendant 1 mois";
            } else if ($forfait->getCodeForfait() == "6M") {
                $plan = "Agile_6M";
                $days = 0;
                $periode = 6;
                $desc = "Forfait Agile payable par mois pendant 6 mois";
            } else if ($forfait->getCodeForfait() == "12M") {
                $plan = "Agile_12M";
                $periode = 12;
                $days = 0;
                $desc = "Forfait Agile payable par mois soit 12 mois";
            } else if ($forfait->getCodeForfait() == "1M_FREE") {
                $plan = "Agile_1M_FREE";
                $periode = 1;
                $days = 30;
                $desc = "Forfait Agile par mois pendant 1 mois avec une periode d'essai";
            } else if ($forfait->getCodeForfait() == "Agile_1MOIS") {
                $plan = "Agile_1MOIS";
                $periode = 1;
                $days = 0;
                $desc = "Forfait Agile par mois pendant 1 mois avec une periode d'essai";
            } else if ($forfait->getCodeForfait() == "Agile_6MOIS") {
                $plan = "Agile_6MOIS";
                $periode = 6;
                $days = 0;
                $desc = "Forfait Agile par mois pendant 6 mois";
            } else if ($forfait->getCodeForfait() == "Agile_12MOIS") {
                $plan = "Agile_12MOIS";
                $periode = 12;
                $days = 0;
                $desc = "Forfait Agile payable par mois pendant 12 mois";
            }
            return [
                "id" => $forfait->getId(),
                "codeForfait" => $forfait->getCodeForfait(),
                "plan" => $plan,
                "periode" => $periode,
                'days' => $days,
                "description" => $desc
            ];
        };
        return array_map($func, $lists);
    }

    /**
     * @param array $data
     * @return array
     */
    public function setDefautlForfait(array $data): array
    {
        if (!is_numeric($data['id'])) {
            return [
                'data' => [
                    'message' => 'Imposssible to set to default'
                ],
                'statusCode' => Response::HTTP_BAD_REQUEST
            ];
        }
        /** @var Groupe $groupe */
        $groupe = $this->getDoctrine()->getRepository(Groupe::class)->findOneBy(['id' => $data['id']]);
        $forfait = $this->getDoctrine()->getManager()->getRepository(Forfait::class)->findOneBy(['codeForfait' => 'GRATUIT']);

        if ($forfait instanceof Forfait) {
            $groupe->setForfait($forfait);
        }

        // ADD 6 MONTHS TO END DATE FORMAT
        $interval = new \DateInterval('P6M');
        $groupe->setDateEcheance($groupe->getDateEcheance()->add($interval));

        try {
            $em = $this->getDoctrine()->getManager();
            $em->persist($groupe);
            $em->flush();
            return [
                'data' => [
                    'message' => 'Groupe set to default'
                ],
                'statusCode' => Response::HTTP_OK
            ];
        } catch (\Exception $e) {
            return [
                'data' => [
                    'message' => 'Imposssible to set to default'
                ],
                'statusCode' => Response::HTTP_BAD_REQUEST
            ];
        }
    }

    /**
     * @param string $discount
     * @param string $codeForfailt
     * @return bool
     */
    public static function isDiscountSubscription(string $discount, string $codeForfailt): bool {
        $data = array_filter(self::FORFAIT_DISCOUNT, function(array $elt) use ($codeForfailt) {
            return $elt['name'] === $codeForfailt;
        });
        return in_array($discount, array_column($data, 'discount'));
    }

    /**
     * Get codeForfait corresponding by codePromo
     * @param string $codePromo
     * @return string
     */
    public static function getCodeForfaitByPromo(string $codePromo):string {
        $data = array_filter(self::FORFAIT_DISCOUNT, function(array $tab) use ($codePromo){
            return $tab['discount'] === $codePromo;
        });
        $arrayDiscount = array_column($data, 'name');
        return array_shift($arrayDiscount);
    }

    public static function getPriceForfait(string $discount, string $codeForfait) {
        // Get array line on forfaiService
        $data = array_filter(self::FORFAIT_DISCOUNT, function($elt) use($codeForfait) {
           return $elt['name'] === $codeForfait;
        });

        if(count($data) === 0) {
           return null;
        }
        $forfait = array_shift($data);

        if(strtoupper($forfait['discount']) === $discount) {
            $calcReduc = ($forfait['price'] * $forfait['reduction']) / 100;
            return number_format(($forfait['price'] - $calcReduc), 2);
        }
        return $forfait['price'];
    }
}
