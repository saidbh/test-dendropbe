<?php

namespace App\Service;

use App\Entity\Essence;
use App\Entity\Inventaire;
use App\Repository\ArbreRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Serializer;

class BevaService extends AbstractController
{
    private $_tokenService;
    private $_arbreRepository;

    public function __construct(TokenService $_tokenService, ArbreRepository $_arbreRepository)
    {
        $this->_tokenService = $_tokenService;
        $this->_arbreRepository = $_arbreRepository;
    }

    private static function getValSituationIndex($diametre): int
    {
        if(is_null($diametre)){
            return 0;
        }
        // DEFINE INDEX DE TAILLE
        $circ = $diametre * pi();
        $circ = round($circ, 0, PHP_ROUND_HALF_UP);
        switch ($circ) {
            case ($circ >= 10 && $circ <= 14) :
                return 0.5;
            case ($circ >= 15 && $circ <= 22) :
                return 0.8;
            case ($circ >= 23 && $circ <= 40) :
                return 1;
            case ($circ >= 41 && $circ <= 60) :
                return 2;
            case ($circ >= 61 && $circ <= 70) :
                return 3;
            case ($circ >= 71 && $circ <= 80) :
                return 5;
            case ($circ >= 81 && $circ <= 90) :
                return 6;
            case ($circ >= 91 && $circ <= 100) :
                return 8;
            case ($circ >= 101 && $circ <= 110) :
                return 9.5;
            case ($circ >= 111 && $circ <= 120) :
                return 11;
            case ($circ >= 121 && $circ <= 130) :
                return 12.5;
            case ($circ >= 131 && $circ <= 140) :
                return 14;
            case ($circ >= 141 && $circ <= 150) :
                return 15;
            case ($circ >= 151 && $circ <= 160) :
                return 16;
            case ($circ >= 161 && $circ <= 170) :
                return 17;
            case ($circ >= 171 && $circ <= 180) :
                return 18;
            case ($circ >= 181 && $circ <= 190) :
                return 19;
            case ($circ >= 191 && $circ <= 200) :
                return 20;
            case ($circ >= 201 && $circ <= 220) :
                return 21;
            case ($circ >= 221 && $circ <= 240) :
                return 22;
            case ($circ >= 241 && $circ <= 260) :
                return 23;
            case ($circ >= 261 && $circ <= 280) :
                return 24;
            case ($circ >= 281 && $circ <= 300) :
                return 25;
            case ($circ >= 301 && $circ <= 320) :
                return 26;
            case ($circ >= 321 && $circ <= 340) :
                return 27;
            case ($circ >= 341 && $circ <= 360) :
                return 28;
            case ($circ >= 361 && $circ <= 380) :
                return 29;
            case ($circ >= 381 && $circ <= 400) :
                return 30;
            case ($circ >= 401 && $circ <= 420) :
                return 31;
            case ($circ >= 421 && $circ <= 440) :
                return 32;
            case ($circ >= 441 && $circ <= 460) :
                return 33;
            case ($circ >= 461 && $circ <= 480) :
                return 34;
            case ($circ >= 481 && $circ <= 500) :
                return 35;
            case ($circ >= 501 && $circ <= 600) :
                return 40;
            case ($circ >= 601) :
                return 45;
            default:
                return 0;
        }
    }

    public static function createBeva(array $data, $object, $countSubject = 1): int
    {
        $tailleLocation = self::getValSituationIndex($object->getDiametre());
        $beva = ($data['aestheticIndex'] + $data['healthIndex']) * ($data['varietyGrade'] * $data['locationIndex']) * $tailleLocation * $countSubject;
        return round($beva, 2);
    }

    /**
     * @param Request $request
     * @param Inventaire $inventaire
     * @return array
     */
    public function calculBeva(Request $request, Inventaire $inventaire): array
    {
        // AUTHORIZATION
        $data = $this->_tokenService->MiddlewareNormalUser($request->headers->get('Authorization'));
        if (!isset($data['user']) || !$data['user']) {
            return $data;
        }

        /*************** END AUTHORIZATION ****************/
        /** @var Serializer $serializer */
        $serializer = $this->get('serializer');
        $data = $serializer->decode($request->getContent(), 'json');

        $response = $this->configDataBeva($data);

        if ($response['statusCode'] == Response::HTTP_BAD_REQUEST) {
            return $response;
        }
        // CONTROL SUR LES CHAMPS
        $arbre = $this->_arbreRepository->findOneBy(["id" => $inventaire->getArbre()->getId()]);
        $beva = self::createBeva($data, $arbre);

        $inventaire->setHealthIndex($data['healthIndex']);
        $inventaire->setVarietyGrade($data['varietyGrade']);
        $inventaire->setAestheticIndex($data['aestheticIndex']);
        $inventaire->setLocationIndex($data['locationIndex']);

        if (isset($data['healthColumn'])) {
            $inventaire->setHealthColumn($data['healthColumn']);
        }
        if (isset($data['aestheticColumn'])) {
            $inventaire->setAestheticColumn($data['aestheticColumn']);
        }

        try {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return [
                "data" => [
                    "message" => "Beva défini avec succès",
                    "id" => $inventaire->getId(),
                    "beva" => $beva,
                    "errorCode" => 200
                ],
                "statusCode" => Response::HTTP_OK
            ];

        } catch (\Exception $e) {
            return [
                "data" => ["message" => "Impossible de calculer Beva", "errorCode" => 500],
                "statusCode" => Response::HTTP_BAD_REQUEST
            ];
        }
    }

    private function configDataBeva($data)
    {
        if (!isset($data['varietyGrade']) || !isset($data['healthIndex']) || !isset($data['aestheticIndex']) || !isset($data['locationIndex'])) {
            return [
                "data" => [
                    "message" => "Informations obligatoires",
                    "errorCode" => 300
                ],
                "statusCode" => Response::HTTP_BAD_REQUEST
            ];
        }

        if (!is_numeric(trim($data['varietyGrade'])) || !ctype_digit(trim($data['healthIndex'])) || !ctype_digit(trim($data['aestheticIndex'])) || !ctype_digit(trim($data['locationIndex']))) {
            return [
                "data" => [
                    "message" => "Certaines valeurs ne sont pas des entiers",
                    "errorCode" => 301
                ],
                "statusCode" => Response::HTTP_BAD_REQUEST
            ];
        }

        // INDICE SANITAIRE DOIT ETRE COMPRIS ENTRE O ET 4
        if ($data['healthIndex'] < 0 || $data['healthIndex'] > 4) {
            return [
                "data" => [
                    "message" => "Indice Sanitaire doit être compris entre 0 et 4",
                    "errorCode" => 302
                ],
                "statusCode" => Response::HTTP_BAD_REQUEST
            ];
        }

        if ($data['aestheticIndex'] < 1 || $data['aestheticIndex'] > 6) {
            return [
                'data' => [
                    "message" => "Indice esthétique doit être compris entre 1 et 6",
                    "errorCode" => 303
                ],
                'statusCode' => Response::HTTP_BAD_REQUEST
            ];
        }

        if (trim($data['locationIndex']) != 6 && trim($data['locationIndex']) != 8 && trim($data['locationIndex']) != 10) {
            return [
                "data" => [
                    "message" => "Indice de location doit prendre 6, 8 et 10",
                    "errorCode" => 304
                ],
                "statusCode" => Response::HTTP_BAD_REQUEST
            ];
        }

        return ["statusCode" => Response::HTTP_OK];
    }

    public function calculBevaEssence(Request $request, Essence $essence): array
    {
        // AUTHORIZATION
        $data = $this->_tokenService->MiddlewareNormalUser($request->headers->get('Authorization'));
        if (!isset($data['user']) || !$data['user']) {
            return $data;
        }

        /*************** END AUTHORIZATION ****************/
        /** @var Serializer $serializer */
        $serializer = $this->get('serializer');
        $data = $serializer->decode($request->getContent(), 'json');

        $response = $this->configDataBeva($data);

        if ($response['statusCode'] == Response::HTTP_BAD_REQUEST) {
            return $response;
        }
        // CONTROL SUR LES CHAMPS
        $beva = $this->createBeva($data, $essence, $essence->getCountSubject());

        $essence->setHealthIndex($data['healthIndex']);
        $essence->setVarietyGrade($data['varietyGrade']);
        $essence->setAestheticIndex($data['aestheticIndex']);
        $essence->setLocationIndex($data['locationIndex']);

        if (isset($data['healthColumn'])) {
            $essence->setHealthColumn($data['healthColumn']);
        }
        if (isset($data['aestheticColumn'])) {
            $essence->setAestheticColumn($data['aestheticColumn']);
        }

        try {
            $statusCode = Response::HTTP_OK;
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return [
                "data" => [
                    "message" => "Beva défini avec succès",
                    "id" => $essence->getId(),
                    "beva" => $beva,
                    "errorCode" => 200
                ],
                "statusCode" => $statusCode
            ];

        } catch (\Exception $e) {
            return [
                "data" => ["message" => "Calcul Beva Impossible", "errorCode" => 500],
                "statusCode" => Response::HTTP_BAD_REQUEST
            ];
        }
    }
}
