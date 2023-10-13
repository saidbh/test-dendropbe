<?php

namespace App\Service;

use App\Entity\Arbre;
use App\Entity\Epaysage;
use App\Entity\Essence;
use App\Entity\Inventaire;
use App\Entity\User;
use App\Form\InventaireType;
use App\Repository\InventaireRepository;
use App\Repository\UserRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class InventaireService extends AbstractController
{
    private $tokenService;
    private $validator;
    private $_arbreService;
    private $_bevaService;
    private $_champignonService;
    private $_epaysageService;
    private $_essenceService;
    private $_travauxService;
    private $_userRepository;

    const TYPE_TREE = ['ARBRE', 'ALIGNEMENT'];

    public function __construct(
        TokenService       $tokenService,
        ValidatorInterface $validator,
        ArbreService       $arbreService,
        BevaService        $bevaService,
        ChampignonService  $champignonService,
        EpaysageService    $epaysageService,
        EssenceService     $essenceService,
        TravauxService     $travauxService,
        UserRepository     $userRepository
    )
    {
        $this->tokenService = $tokenService;
        $this->validator = $validator;
        $this->_arbreService = $arbreService;
        $this->_bevaService = $bevaService;
        $this->_champignonService = $champignonService;
        $this->_epaysageService = $epaysageService;
        $this->_essenceService = $essenceService;
        $this->_travauxService = $travauxService;
        $this->_userRepository = $userRepository;
    }

    /**
     * @param $request
     * @param $object
     * @return array
     */
    public function getOne($request, $object): array
    {
        // GET ONE INVENTAIRE
        $headers = $request->headers->get('Authorization');

        $data = $this->tokenService->MiddlewareNormalUser($headers);
        if (!isset($data['user']) || !$data['user']) {
            return $data;
        }
        $data = $this->generateObjectInventaire($object);
        // RETURN TABLEAU
        return [
            "data" => $data,
            "statusCode" => Response::HTTP_OK
        ];
    }

    /**
     * @param $request
     * @return array
     */
    public function getAllWithPagination($inventaireRepository,$request): array{

        $page = $request->get('page');
        $limit = $request->get('limit');
        $data = $this->tokenService->MiddlewareNormalUser($request->headers->get('Authorization'));
        if (!isset($data['user']) || !$data['user']) {
            return $data;
        }
        $user = $data['user'];
        $data = $this->generateArrayInventaire($inventaireRepository->queryInventoryByPaginators($user,$page,$limit));
        return['data'=> $data['data'],'count'=>$data['count'], "statusCode" => Response::HTTP_OK];
    }
    

    /**
     * @param Request $request
     * @return array
     */
    public function getAll(Request $request): array
    {
        $data = $this->tokenService->MiddlewareNormalUser($request->headers->get('Authorization'));
        if (!isset($data['user']) || !$data['user']) {
            return $data;
        }
        $user = $data['user'];
        // Get Data Params
        $page = $request->query->get('page');
        $data = $this->generateArrayInventaire($this->getDoctrine()->getRepository(Inventaire::class)->queryInventoryByGroupe($user));

        if (!isset($page)) {
            return [
                "data" => $data['data'],
                "statusCode" => Response::HTTP_OK
            ];
        }

        return PaginatedService::paginateList($data, $page, 20);
    }



    /**
     * @param Request $request
     * @param bool $finished 
     * @return array
     */
    public function getAllFinishedWithPagination($inventaireRepository,$request, bool $finished)
    {
        // VERIFICATION TOKEN
        $data = $this->tokenService->MiddlewareNormalUser($request->headers->get('Authorization'));
        if (!isset($data['user']) || !$data['user']) {
            return $data;
        }
        $user = $data['user'];
        // Get Data Params
        $page = $request->get('page');
        $limit = $request->get('limit');
        /** @var Serializer $serializer */
        $serializer = $this->get('serializer');
        $data = $serializer->decode($request->getContent(), 'json');
        /** @var Inventaire[] $inventory */
        $inventory = $this->generateArrayInventaire($inventaireRepository->queryInventoryByGroupeIsFinishedPagination($page, $limit,$user, $finished ? 1 : 0));
            return [
                "data" => $inventory["data"],
                "count" => $inventory["count"],
                "statusCode" => Response::HTTP_OK
            ];
    }

    /**
     * @param Request $request
     * @param bool $finished
     * @return array
     */
    public function getAllFinished(Request $request, bool $finished): array
    {
        $data = $this->tokenService->MiddlewareNormalUser($request->headers->get('Authorization'));
        if (!isset($data['user']) || !$data['user']) {
            return $data;
        }
        $user = $data['user'];
        //Get Data Params
        $page = $request->query->get('page');
        /** @var Serializer $serializer */
        $serializer = $this->get('serializer');

        $data = $serializer->decode($request->getContent(), 'json');


        /** @var Inventaire[] $inventory */
        $inventory = $this->generateArrayInventaire($this->getDoctrine()->getRepository(Inventaire::class)->queryInventoryByGroupeIsFinished($user, $finished ? 1 : 0));

        if (!isset($data['page'])) {
            return [
                "data" => $inventory["data"],
                "statusCode" => Response::HTTP_OK
            ];
        }
        return PaginatedService::paginateList($inventory, $page, 20);
    }

    /**
     * @param Request $request
     * @return array
     */
    public function getAllByPosition(Request $request): array
    {
        $data = $this->tokenService->MiddlewareNormalUser($request->headers->get('Authorization'));
        if (!isset($data['user']) || !$data['user']) {
            return $data;
        }
        /** @var  User $user */
        $user = $data['user'];

        /** @var Serializer $serializer */
        $serializer = $this->get('serializer');
        $data = $serializer->decode($request->getContent(), 'json');
        if (!isset($data['lat']) || !isset($data['lng'])) {
            return [
                'data' => [
                    'message' => 'Les coordonnées géographiques sont obligatoire'
                ],
                'statusCode' => Response::HTTP_BAD_REQUEST
            ];
        }
        // Get Inventory by Groupe
        /** @var Inventaire[] $inventaires */
        $inventaires = $this->getDoctrine()->getRepository(Inventaire::class)->queryInventoryByGroupe($user);
        // Get Array by Position
        $data = $data['position'] == 'RADIUS' ?
            $this->getInventoryByRadius($data, $inventaires, (!$data['espece'] && !$data['codeSite'] && !$data['critere'] && !$data['isFinished']))
         : $this->getAllInventoryWithoutRadius($data, $inventaires, (!$data['espece'] && !$data['codeSite'] && !$data['critere'] && !$data['isFinished']));
        return [
            'data' => $data,
            'statusCode' => Response::HTTP_OK
        ];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function searchInventaire(Request $request): array
    {
        $data = $this->tokenService->MiddlewareNormalUser($request->headers->get('Authorization'));
        if (!isset($data['user']) || !$data['user']) {
            return $data;
        }
        $user = $data['user'];

        /** @var Serializer $serializer */
        $serializer = $this->get('serializer');
        $data = $serializer->decode($request->getContent(), 'json');

        if (!isset($data['infos'])) {
            return [
                "data" => [
                    'message' => 'Informations obligatoires'
                ],
                "statusCode" => Response::HTTP_BAD_REQUEST
            ];
        }

        /** @var InventaireRepository $repo */
        $repo = $this->getDoctrine()->getRepository(Inventaire::class);
        $list = $repo->findInventoryTreeByAdrOrTown($data['infos'], $user);

        // get page query
        /** @var string $page */
        $page = $request->query->get('page');

        if (!isset($page) || !is_numeric($page)) {
            return [
                "data" => 'Informations obligatoires',
                "statusCode" => Response::HTTP_BAD_REQUEST
            ];
        }

        return PaginatedService::paginateList($this->generateArrayInventaire($list), $page, 20);
    }

    /**
     * @param $objects
     * @return array
     */
    private function generateArrayInventaire($objects): array
    {
        $_objects = [];
        
            if(isset($objects["data"])){
            $_objects["count"] = $objects["count"];
            $objects = $objects["data"];
            }
            foreach ($objects as $object) {
            $_objects["data"][] = $this->generateObjectInventaire($object);
            }
           
        return $_objects;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function validMany(Request $request): array
    {
        $data = $this->tokenService->MiddlewareNormalUser($request->headers->get('Authorization'));
        if (!isset($data['user']) || !$data['user']) {
            return $data;
        }

        /** @var Serializer $serializer */
        $serializer = $this->get('serializer');
        $data = $serializer->decode($request->getContent(), 'json');


        foreach ($data['ids'] as $inventaireId) {
            /** @var Inventaire $inventaire */
            $inventaire = $this->getDoctrine()->getRepository(Inventaire::class)->findOneBy(['id' => $inventaireId]);

            if ($inventaire instanceof Inventaire) {
                $inventaire->setIsFinished(true);
                $inventaire->setUpdatedAt(new \DateTime('now'));

                try {
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($inventaire);
                    $em->flush();

                    $data = [
                        "message" => "Inventaire mis a jour avec succès",
                        "errorCode" => 200
                    ];

                } catch (\Exception $e) {
                    $data = [
                        "message" => "Impossible de mettre a jour avec succès",
                        "errorCode" => 300
                    ];
                    return [
                        "data" => $data,
                        "statusCode" => Response::HTTP_BAD_REQUEST
                    ];
                }
            }

        }

        return [
            "data" => $data,
            "statusCode" => Response::HTTP_ACCEPTED
        ];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function validTravaux(Request $request): array
    {

        $data = $this->tokenService->MiddlewareNormalUser($request->headers->get('Authorization'));
        if (!isset($data['user']) || !$data['user']) {
            return $data;
        }

        // get data
        /** @var Serializer $serializer */
        $serializer = $this->get('serializer');
        $data = $serializer->decode($request->getContent(), 'json');

        foreach ($data['ids'] as $inventaireId) {
            /** @var Inventaire $inventory */
            $inventory = $this->getDoctrine()->getRepository(Inventaire::class)->findOneBy(['id' => $inventaireId]);

            if(!$inventory instanceof Inventaire) {
                return [
                    "data" => [
                        "message" => "Impossible de changer le status"
                    ],
                    "statusCode" => Response::HTTP_BAD_REQUEST
                ];
            }

            $this->_travauxService->validTravauxInventory($inventory);
        }
        return [
            "data" => [
                "message" => "Travaux mis à jour avec succès"
            ],
            "statusCode" => Response::HTTP_OK
        ];

    }

    /**
     * @param Request $request
     * @param Inventaire $inventaire
     * @return array
     */
    public function deleteSingleInventaire(Request $request, Inventaire $inventaire): array
    {
        $data = $this->tokenService->MiddlewareNormalUser($request->headers->get('Authorization'));
        if (!isset($data['user']) || !$data['user']) {
            return $data;
        }

        return InventaireService::deleteInventaire($inventaire, $this->getDoctrine()->getManager());
    }

    /**
     * @param array $etatSanGnl
     * @return bool
     */
    public static function isExamenComplementaire(array $etatSanGnl): bool
    {
        return in_array("exam-comple", $etatSanGnl);
    }

    /**
     * @param Inventaire $inventaire
     * @param ObjectManager $em
     * @return array
     */
    static function deleteInventaire(Inventaire $inventaire, ObjectManager $em): array
    {
        try {
            $em->remove($inventaire);
            $em->flush();
            // REMOVE EPAYSAGE OR ARBRE
            return [
                "data" => ["message" => "Inventaire supprimé avec succès"],
                "statusCode" => Response::HTTP_NO_CONTENT
            ];

        } catch (\Doctrine\DBAL\Exception $e) {
            return [
                "data" => [
                    "message" => "Impossible de supprimer Inventaire"
                ],
                "statusCode" => Response::HTTP_BAD_REQUEST
            ];
        }
    }

    /**
     * @param Request $request
     * @return array
     */
    public function deleteManyInventaire(Request $request): array
    {
        $data = $this->tokenService->MiddlewareNormalUser($request->headers->get('Authorization'));
        if (!isset($data['user']) || !$data['user']) {
            return $data;
        }

        /** @var Serializer $serializer */
        $serializer = $this->get('serializer');
        $data = $serializer->decode($request->getContent(), 'json');

        foreach ($data['ids'] as $id) {
            // GET INVENTAIRE
            try {
                $inventaire = $this->getDoctrine()->getRepository(Inventaire::class)->findOneBy(['id' => $id]);
                if ($inventaire instanceof Inventaire) {
                    InventaireService::deleteInventaire($inventaire, $this->getDoctrine()->getManager());
                }
            } catch (\Exception $e) {
                return [
                    "data" => [
                        "message" => "Impossible de supprimer l'inventaire"
                    ],
                    "statusCode" => Response::HTTP_BAD_REQUEST
                ];
            }

        }

        return [
            "data" => [
                "message" => "Inventaire supprimé avec succès"
            ],
            "statusCode" => Response::HTTP_NO_CONTENT
        ];
    }

    /**
     * @param Request $request
     * @param Inventaire $inventaire
     * @return array
     */
    public function updateArbre(Request $request, Inventaire $inventaire): array
    {
        // UPDATE INVENTAIRE
        $data = $this->tokenService->MiddlewareNormalUser($request->headers->get('Authorization'));
        if (!isset($data['user']) || !$data['user']) {
            return $data;
        }
        // Get data
        /** @var Serializer $serializer */
        $serializer = $this->get('serializer');
        $data = $serializer->decode($request->getContent(), 'json');

        // INVENTAIRE DATA TO HYDRATE
        $dataInventaire['type'] = isset($data['type']) ? $data['type'] : $inventaire->getType();
        $dataInventaire['user'] = $inventaire->getUser();
        $dataInventaire['isFinished'] = $data['isFinished'];

        $dataInventaire['createdAt'] = $inventaire->getCreatedAt();

        // Beva calcul
        $dataInventaire['varietyGrade'] = isset($data['varietyGrade']) ? $data['varietyGrade'] : $inventaire->getVarietyGrade();
        $dataInventaire['healthIndex'] = isset($data['healthIndex']) ? $data['healthIndex'] : $inventaire->getHealthIndex();
        $dataInventaire['aestheticIndex'] = isset($data['aestheticIndex']) ? $data['aestheticIndex'] : $inventaire->getAestheticIndex();
        $dataInventaire['locationIndex'] = isset($data['locationIndex']) ? $data['locationIndex'] : $inventaire->getLocationIndex();
        $dataInventaire['aestheticColumn'] = isset($data['aestheticColumn']) ? $data['aestheticColumn'] : $inventaire->getAestheticColumn();
        $dataInventaire['healthColumn'] = isset($data['healthColumn']) ? $data['healthColumn'] : $inventaire->getHealthColumn();

        $dataInventaire['createdAt'] = $inventaire->getCreatedAt();

        // Get inventaire arbre or epaysage
        $dataAnswer = $this->editInventaireArbre($data, $inventaire);

        if (!$dataAnswer['isDone']) {
            return [
                'data' => [
                    'message' => 'Impossible de modifier inventaire',
                    'error' => 303
                ], 'statusCode' => Response::HTTP_BAD_REQUEST
            ];
        }
        $dataInventaire['arbre'] = $dataAnswer['id'];

        $form = $this->createForm(InventaireType::class, $inventaire);
        $form->submit($dataInventaire);

        if (isset($data['healthIndex'])) {
            $inventaire->setHealthIndex($data['healthIndex']);
        }
        if (isset($data['varietyGrade'])) {
            $inventaire->setVarietyGrade($data['varietyGrade']);
        }
        if (isset($data['aestheticIndex'])) {
            $inventaire->setAestheticIndex($data['aestheticIndex']);
        }
        if (isset($data['locationIndex'])) {
            $inventaire->setLocationIndex($data['locationIndex']);
        }

        if (isset($data['healthColumn'])) {
            $inventaire->setHealthColumn($data['healthColumn']);
        }
        if (isset($data['aestheticColumn'])) {
            $inventaire->setAestheticColumn($data['aestheticColumn']);
        }
        $inventaire->setUpdatedAt(new \DateTime('now'));
        try {
            $em = $this->getDoctrine()->getManager();
            $em->persist($inventaire);
            $em->flush();
            return [
                'data' => [
                    "message" => "Inventaire mis à jour avec succès",
                    "data" => $this->generateObjectInventaire($inventaire)
                ], 'statusCode' => Response::HTTP_OK
            ];
        } catch (\Doctrine\DBAL\DBALException $e) {
            return [
                'data' => [
                    "message" => "Impossible de mettre à jour",
                    "errorCode" => 500,
                    "error" => $e->getMessage()
                ], 'statusCode' => Response::HTTP_INTERNAL_SERVER_ERROR
            ];
        }
    }

    /**
     * @param array $data
     * @param Inventaire $inventaire
     * @return array
     */
    public function editInventaireArbre(array $data, Inventaire $inventaire): array
    {
        /** @var Arbre $arbre */
        $arbre = $this->getDoctrine()->getRepository(Arbre::class)->findOneBy(['id' => $inventaire->getArbre()->getId()]);
        if (!$arbre instanceof Arbre) {
            return [
                'data' => [
                    'message' => 'Information obligatoire'
                ],
                'statusCode' => Response::HTTP_NOT_FOUND
            ];
        }
        try {
            $this->_arbreService->updateArbre($data, $arbre);
            return [
                'isDone' => true,
                'id' => $arbre->getId()
            ];
        } catch (\Exception $e) {
            return ['isDone' => false, "message" => $e->getMessage()];
        }
    }

    /**
     * @param Inventaire $object
     * @return array
     */
    public function generateObjectInventaire(Inventaire $object): array
    {
        // OBJECT
        $_object['id'] = $object->getId();
        $_object['type'] = $object->getType();

        if ($object->getArbre()) {
            $_object['arbre'] = $this->_arbreService->generateObjectArbreJson($object);
        }
        // EPAYSAGE
        $_object['epaysage'] = $object->getEpaysage() ? $this->serializerEpaysage($object->getEpaysage()) : null;
        //
        $user = $object->getUser();

        $_user ['id'] = $user->getId();
        $_user ['email'] = $user->getEmail();
        $_user ['username'] = $user->getUsername();
        $_user ['img'] = $user->getImg();
        $_user ['profil'] = $user->getProfil()->getName();
        $_object['user'] = $_user;
        // FIN USER
        $_object['isFinished'] = $object->getIsFinished();
        $_object['createdAt'] = $object->getCreatedAt()->format('Y-m-d\TH:i:sO');
        $_object['updatedAt'] = $object->getUpdatedAt() ? $object->getUpdatedAt()->format('Y-m-d\TH:i:sO') : '';
        
        return $_object;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function getStatInventory(Request $request, $user=null): array
    {

        $data = $this->tokenService->MiddlewareNormalUser($request->headers->get('Authorization'));
        if (!isset($data['user']) || !$data['user']) {
            return $data;
        }

        if (isset($user)) {
            $user = $this->_userRepository->findOneBy(['id'=>$user]);
            $data['user']=$user;

        }

        // Get infos inventaires
        /** @var Inventaire[] $result */
        $result = $this->getListInventorybyUser($data['user']);

        $statArray = [
            'ebc' => 0,
            'epp' => 0,
            'arbre' => 0
        ];
        foreach ($result as $inv) {
            if ($inv->getType() === 'ARBRE') ++$statArray['arbre'];
            if ($inv->getType() === 'EBC') ++$statArray['ebc'];
            if ($inv->getType() === 'EPP') ++$statArray['epp'];
        }
        return [
            'data' => $statArray,
            'statusCode' => Response::HTTP_OK
        ];
    }

    /**
     * @param User $user
     * @return array
     */
    private function getListInventorybyUser(User $user): array
    {

        $objects = $this->getDoctrine()->getRepository(Inventaire::class)->findBy(['user'=>$user], ['id' => 'DESC']);
        $data = [];
        foreach ($objects as $object) {
            if ($object->getUser()->getId() === $user->getId()) {
                array_push($data, $object);
            }
        }
        return $data;
    }

    /**
     * @param Inventaire|Essence $object
     * @return int
     */
    public static function setBevaInventaire($object): int
    {
        $data = [];
        if ($object instanceof Inventaire) { // arbre Inventory
            $data = [
                "varietyGrade" => $object->getVarietyGrade(),
                "healthIndex" => $object->getHealthIndex(),
                "aestheticIndex" => $object->getAestheticIndex(),
                "locationIndex" => $object->getLocationIndex()
            ];
        } else if ($object instanceof Essence) {
            $data = [
                "varietyGrade" => $object->getVarietyGrade(),
                "healthIndex" => $object->getHealthIndex(),
                "aestheticIndex" => $object->getAestheticIndex(),
                "locationIndex" => $object->getLocationIndex()
            ];
        }
        return BevaService::createBeva($data,
            ($object instanceof Inventaire) ? $object->getArbre() : $object,
            ($object instanceof Inventaire) ? 1 : $object->getCountSubject()
        );
    }

    /**
     * @param Epaysage $epaysage
     * @return array
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function serializerEpaysage(Epaysage $epaysage): array
    {
        /** @var Serializer $serializer */
        $serializer = $this->get('serializer');
        $data = $serializer->normalize($epaysage, 'json', ['groups' => ['read']]);

        // format Essence
        $data['essence'] = [];
        foreach ($epaysage->getEssence() as $essence) {
            array_push($data['essence'], $this->_essenceService->serializerEssence($essence, true));
        }
        // Format coord
        $data['coord'] = MapService::serializeCoord($epaysage);
        return $data;
    }

    /**
     * @param Request $request
     * @param Epaysage $epaysage
     * @return array
     */
    public function getOneInventaireByEpaysage(Request $request, Epaysage $epaysage): array
    {
        $data = $this->tokenService->MiddlewareNormalUser($request->headers->get('Authorization'));

        if (!isset($data['user']) || !$data['user']) {
            return $data;
        }
        /** @var Inventaire $inventaire */
        $inventaire = $this->getDoctrine()->getManager()->getRepository(Inventaire::class)->findOneBy(['epaysage' => $epaysage->getId()]);

        if (!$inventaire instanceof Inventaire) {
            return [
                "data" => [
                    "message" => 'inventaire is not define'
                ],
                "statusCode" => Response::HTTP_BAD_REQUEST
            ];
        }

        return [
            "data" => $this->generateObjectInventaire($inventaire),
            "statusCode" => Response::HTTP_OK
        ];
    }

    /**
     * Filter inventaire dans un rayon d'1 km
     * @param array $coord
     * @param array $inventaires
     * @return array
     */
    public function findInventoryByFilter(array $coord, array $inventaires): array
    {
        return array_filter($inventaires, function (Inventaire $inventaire) use ($coord) {
            if (in_array(strtoupper($inventaire->getType()), self::TYPE_TREE)) { // Cas d'un arbre or Alignement
                return MapService::isRadiusAround($coord, MapService::serializeCoord($inventaire->getArbre())['lat'], MapService::serializeCoord($inventaire->getArbre())['long']);
            } else { // Cas d'un EBC ou EPP
                $arrayCoord = MapService::serializeCoord($inventaire->getEpaysage());
                $response = array_filter($arrayCoord, function ($coordinate) use ($coord) {
                    return MapService::isRadiusAround($coord, $coordinate['lat'], $coord['long']);
                });
                return count($response) == 1;
            }
        });
    }

    /**
     * @param Request $request
     * @param string|null $type
     * @return array
     */
    public function addTreeOrInventory(Request $request, string $type = null): array
    {
        $data = $this->tokenService->MiddlewareNormalUser($request->headers->get('Authorization'));
        if (!isset($data['user']) || !$data['user']) {
            return $data;
        }
        $user = $data['user'];

        /** @var Serializer $serializer */
        $serializer = $this->get('serializer');
        $inventoryResponse = strtoupper($type) == 'ARBRE' ? $this->_arbreService->add($serializer->decode($request->getContent(), 'json')) :
            $this->_epaysageService->add($serializer->decode($request->getContent(), 'json'));

        if ($inventoryResponse['errorCode'] != 200) {
            return [
                'data' => $inventoryResponse,
                'statusCode' => Response::HTTP_BAD_REQUEST
            ];
        }

        $inventoryResponse['data']['user'] = $user;
        $response = $this->addInventory($inventoryResponse['data']);

        return [
            'data' => $response,
            'statusCode' => (isset($response['errorCode']) && $response['errorCode'] != 200) ? Response::HTTP_BAD_REQUEST : Response::HTTP_CREATED
        ];

    }

    public function addInventory(array $addInventaire): array
    {
        // verify if Epaysage or Arbre
        $inventaire = new Inventaire();

        if (!isset($addInventaire['type']) || !isset($addInventaire['user'])) {
            return [
                'message' => 'Saisir information obligatoires',
                'errorCode' => 300
            ];
        }
        $inventaire->setType($addInventaire['type']);
        $inventaire->setUser($addInventaire['user']);

        $inventaire->setIsFinished($addInventaire['isFinished'] ?? '');
        // ARBRE ID OU ESPACE ID
        if (isset($addInventaire['arbre'])) {
            /** @var Arbre $arbre */
            $arbre = $this->getDoctrine()->getRepository(Arbre::class)->findOneBy(['id' => $addInventaire['arbre']]);
            $inventaire->setArbre($arbre);

            $inventaire->setHealthColumn($addInventaire['healthColumn'] ?? null);
            $inventaire->setHealthIndex($addInventaire['healthIndex'] ?? null);
            $inventaire->setVarietyGrade($addInventaire['varietyGrade'] ?? null);
            $inventaire->setLocationIndex($addInventaire['locationIndex'] ?? null);
            $inventaire->setAestheticIndex($addInventaire['aestheticIndex'] ?? null);
            $inventaire->setAestheticColumn($addInventaire['aestheticColumn'] ?? null);

        } else if ($addInventaire['epaysage']) {
            /** @var Epaysage $epaysage */
            $epaysage = $this->getDoctrine()->getRepository(Epaysage::class)->findOneBy(['id' => $addInventaire['epaysage']]);
            $inventaire->setEpaysage($epaysage);
            $inventaire->setType($addInventaire['type']);
        }
        // DATE DE CREATION
        $inventaire->setCreatedAt(new \DateTime('now'));

        try {
            $this->getDoctrine()->getManager()->persist($inventaire);
            $this->getDoctrine()->getManager()->flush();
            return ($inventaire->getType() == 'ARBRE') ? [
                "message" => "Enregistrement effectué avec succès",
                "id" => self::generateObjectInventaire($inventaire),
                "errorCode" => 200
            ] : EpaysageService::formatAddInventoryObject($inventaire);

        } catch (\Doctrine\DBAL\Exception $e) {
            return [
                "message" => "Impossible d'enrégistrer user",
                "errorCode" => 500
            ];
        }
    }

    /**
     * @param array $data // Request data
     * @param array $inventaires
     * @param bool $isSearching
     * @return array
     */
    public function getInventoryByRadius(array $data, array $inventaires, bool $isSearching):array {
        $result = [];
        
        foreach ($inventaires as $inventory) {
            if (MapService::isInventoryInZone($inventory, $data)) {
                if(!$isSearching) {
                    self::filterInventoryMap($inventory, $data) && array_push($result,  $this->generateObjectInventaire($inventory));  
                } else {
                    array_push($result,  $this->generateObjectInventaire($inventory));
                };
            }
        }
        return $result;
    }

    /**
     * get all invetory map without radius
     * @param array $data
     * @param array $inventaires
     * @param bool $isSearching
     * @return array
     */
    public function getAllInventoryWithoutRadius(array $data, array $inventaires, bool $isSearching): array {
        $result = [];
        foreach ($inventaires as $inventory) {
            if(!$isSearching) {
                self::filterInventoryMap($inventory, $data) && array_push($result,  $this->generateObjectInventaire($inventory));
            } else {
                array_push($result,  $this->generateObjectInventaire($inventory));
            }
        }
        return $result;
    }

    /**
     * @param Inventaire $inventory
     * @param array $data
     * @return bool
     */
    public static function filterInventoryMap(Inventaire $inventory, array $data):bool {
        if($data['espece'] && !$data['codeSite'] && !$data['critere'] && !$data['isFinished']) {
            return FilterMapService::filterEspece($data['espece'], $inventory);
        } else if(!$data['espece'] && $data['codeSite'] && !$data['critere'] && !$data['isFinished']) {
            return FilterMapService::filterCodeSiteOrNumSujet($data['codeSite'], $inventory);
        } else if(!$data['espece'] && !$data['codeSite'] && $data['critere'] && !$data['isFinished']) {
            return FilterMapService::filtertreeRemarquable($inventory);
        } else if(!$data['espece'] && !$data['codeSite'] && !$data['critere'] && $data['isFinished']) {
            return FilterMapService::filterBrouillon($inventory);
        } else if($data['espece'] && $data['codeSite'] && !$data['critere'] && !$data['isFinished']) {
            return FilterMapService::filterEspece($data['espece'], $inventory) && FilterMapService::filterCodeSiteOrNumSujet($data['codeSite'], $inventory);
        }  else if($data['espece'] && !$data['codeSite'] && $data['critere'] && !$data['isFinished']) {
            return FilterMapService::filterEspece($data['espece'], $inventory) && FilterMapService::filtertreeRemarquable($inventory);
        } else if($data['espece'] && !$data['codeSite'] && !$data['critere'] && $data['isFinished']) {
            return FilterMapService::filterEspece($data['espece'], $inventory) && FilterMapService::filterBrouillon($inventory);
        } else if (!$data['espece'] && $data['codeSite'] && $data['critere'] && !$data['isFinished']) {
            return FilterMapService::filterCodeSiteOrNumSujet($data['codeSite'], $inventory) && FilterMapService::filtertreeRemarquable($inventory);
        } else if(!$data['espece'] && !$data['codeSite'] && $data['critere'] && $data['isFinished']) {
            return FilterMapService::filtertreeRemarquable($inventory) && FilterMapService::filterBrouillon($inventory);
        } else if($data['espece'] && $data['codeSite'] && $data['critere'] && !$data['isFinished']) {
            return FilterMapService::filterEspece($data['espece'], $inventory) && FilterMapService::filterCodeSiteOrNumSujet($data['codeSite'], $inventory) && FilterMapService::filtertreeRemarquable($inventory);
        } else if(!$data['espece'] && $data['codeSite'] && $data['critere'] && $data['isFinished']) {
            return FilterMapService::filtertreeRemarquable($inventory) && FilterMapService::filterCodeSiteOrNumSujet($data['codeSite'], $inventory) && FilterMapService::filterBrouillon($inventory);
        }
    }
}