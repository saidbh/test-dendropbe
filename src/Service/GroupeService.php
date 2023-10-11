<?php

namespace App\Service;

use App\Entity\Forfait;
use App\Entity\Groupe;
use App\Entity\Inventaire;
use App\Entity\User;
use App\Repository\GroupeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class GroupeService extends AbstractController
{
    private $_tokenService;
    private $_repository;
    private $_stripeService;
    private $_imageService;
    private $_validator;

    const GROUP_TYPE = ['DENDROMAP', 'FORMULE PREMUIM'];

    public function __construct(TokenService       $_tokenService,
                                GroupeRepository   $repository,
                                StripeService      $stripeService,
                                ImageService       $imageService,
                                ValidatorInterface $validator
    )
    {
        $this->_tokenService = $_tokenService;
        $this->_repository = $repository;
        $this->_stripeService = $stripeService;
        $this->_imageService = $imageService;
        $this->_validator = $validator;
    }

    public function add(Request $request)
    {
        $data = $this->_tokenService->MiddlewareAdminDedroUser($request->headers->get('Authorization'));
        if (!isset($data['user']) || !$data['user']) {
            return new JsonResponse($data['data'], $data['statusCode']);
        }

        /** @var Serializer $serializer */
        $serializer = $this->get('serializer');
        $data = $serializer->decode($request->getContent(), 'json');

        if (!isset($data['groupeType'])) {
            return new JsonResponse([
                'message' => 'Informations obligatoires',
                'errorCode' => 300
            ], Response::HTTP_BAD_REQUEST);
        }

        if (strtoupper($data['groupeType']) != 'DENDROMAP') {
            // FORFAIT
            if (!isset($data['forfait'])) {
                return new JsonResponse([
                    'message' => 'Informations obligatoires',
                    'errorCode' => 300
                ], Response::HTTP_BAD_REQUEST);
            }
        }

        $data['dateEcheance'] = $this->_stripeService->setDateEcheance($data['forfait']);
        return self::newGroupe($data);
    }

    /**
     * @param Request $request
     * @return array
     */
    public function groupes(Request $request): array
    {
        $headers = $request->headers->get('Authorization');
        $data = $this->_tokenService->MiddlewareNormalUser($headers);

        if (!isset($data['user']) || !$data['user']) {
            return $data;
        }
        return [
            "data" => self::generateArrayGroupe($this->_repository->findAll()),
            "statusCode" => Response::HTTP_OK
        ];
    }

    public function groupe(Request $request, Groupe $groupe)
    {
        $data = $this->_tokenService->MiddlewareNormalUser($request->headers->get('Authorization'));
        if (!isset($data['user']) || !$data['user']) {
            return $data;
        }

        return [
            "data" => $this->factoryGroupe($groupe),
            "statusCode" => Response::HTTP_OK
        ];
    }

    /**
     * @param Request $request
     * @param Groupe $groupe
     * @return array
     */
    public function delete(Request $request, Groupe $groupe): array
    {
        $data = $this->_tokenService->MiddlewareAdminDedroUser($request->headers->get('Authorization'));
        if (!isset($data['user']) || !$data['user']) {
            return $data;
        }

        // check if user is mapping inventaire
        /** @var Inventaire[] $inventaires */
        $inventaires = $this->getDoctrine()->getManager()->getRepository(Inventaire::class)->findAll();
        // CHECK IF Group has subscribed to a forfait

        if ($groupe->getIsStripped() && !GroupeService::isDateExpired($groupe) && $groupe->getForfait()->getCodeForfait() != 'GRATUIT') {
            return [
                "data" => ["message" => "Impossible de supprimer ce groupe. Un abonnement est en cours sur ce compte"],
                "statusCode" => Response::HTTP_BAD_REQUEST
            ];
        }
        if (!$groupe->getIsStripped()) { // LE CAS D'UN USER NO STRIPPED but with

            if (!GroupeService::isDateExpired($groupe) && $groupe->getForfait()->getCodeForfait() != 'GRATUIT') {
                return [
                    "data" => ["message" => "Impossible de supprimer ce groupe. Un abonnement est en cours sur ce compte"],
                    "statusCode" => Response::HTTP_BAD_REQUEST
                ];
            }
        }
        $em = $this->getDoctrine()->getManager();

        if (GroupeService::isGroupMappingInventory($inventaires, $groupe)) {
            return [
                "data" => [
                    "message" => "Impossible de supprimer ce compte car il existe des inventaires dessus"
                ],
                "statusCode" => Response::HTTP_BAD_REQUEST
            ];
        }

        try {
            $em->remove($groupe);
            $em->flush();
            return [
                "data" => ["message" => "Groupe supprimé avec succès"],
                "statusCode" => Response::HTTP_NO_CONTENT
            ];

        } catch (\Doctrine\DBAL\Exception $e) {
            return [
                "data" => [
                    "message" => "Impossible de supprimer ce groupe",
                    "error" => $e->getMessage()
                ],
                "statusCode" => Response::HTTP_BAD_REQUEST
            ];
        }

    }

    public function update(Request $request, Groupe $groupe)
    {
        $data = $this->_tokenService->MiddlewareDendroUser($request->headers->get('Authorization'));
        if (!isset($data['user']) || !$data['user']) {
            return $data;
        }

        /** @var Serializer $serializer */
        $serializer = $this->get('serializer');
        $data = $serializer->decode($request->getContent(), 'json');

        if (!isset($data['forfait'])) {
            return [
                'data' => ['message' => 'Information obligatoire'],
                'statusCode' => Response::HTTP_BAD_REQUEST
            ];
        }
        if ($groupe->getIsStripped()) {
            // IMPOSSIBLE TO MODIFY FORFAIT GROUPE
            // Possible to make it if to save the same card users
            if ($data['forfait'] !== $groupe->getForfait()->getCodeForfait()) {
                return [
                    "data" => [
                        "message" => "Le forfait de ce compte ne peut être modifié.",
                        "errorCode" => 309
                    ],
                    "statusCode" => Response::HTTP_BAD_REQUEST
                ];
            }
        }
        // FORFAIT
        if ($data['forfait']) {
            if (is_numeric($data['forfait'])) {
                /** @var Forfait $data ['forfait'] */
                $data['forfait'] = $this->_repository->findOneBy(['id' => $data['forfait']])->getId();
                // LICENCE
            }
            /** @var Forfait $forfait */
            $forfait = $this->getDoctrine()->getRepository(Forfait::class)->findOneBy(['codeForfait' => $data['forfait']]);
            if (!$forfait instanceof Forfait) {
                return [
                    "data" => [
                        "message" => "Ce forfait n'est pas défini",
                        "errorCode" => 300
                    ],
                    "statusCode" => Response::HTTP_CONFLICT
                ];
            }
            $data['forfait'] = $forfait;
        }
        // HYDRATATION DE DONNEES
        $data['createdAt'] = $groupe->getCreatedAt();
        $data['updatedAt'] = new \DateTime('now');

        // Change Date Subscription no stripe Groupe
        if (!$groupe->getIsStripped()) {
            if (($data['forfait'] !== 'GRATUIT' && $groupe->getForfait()->getCodeForfait() == 'GRATUIT') || ($data['forfait'] == 'GRATUIT' && $groupe->getForfait()->getCodeForfait() != 'GRATUIT')) {
                // free to payant && payant to free
                $data['dateEcheance'] = $this->_stripeService->setDateEcheance($data['forfait']->getCodeForfait());
                $data['dateSubscribed'] = new \DateTime('now');
            } else {
                // PAY_TO_PAY DATE to define another time
                /*
                ** Eventually to see if user only finish his period of resiliation period
                */
                $data['dateEcheance'] = $this->_stripeService->setDateEcheance($data['forfait']->getCodeForfait());
                $data['dateSubscribed'] = new \DateTime('now');
            }
        } else {
            // In case of stripped users. Only User have capacity to modify her own forfait
            $data['dateSubscribed'] = $groupe->getDateSubscribed();
            $data['dateEcheance'] = $groupe->getDateEcheance();
        }

        $serializer = new Serializer([CustomSerializationObject::denormalizeDateTime()], [new JsonEncoder()]);

        /** @var Groupe $forfaitSerial */
        $groupeSerial = $serializer->denormalize($data, Groupe::class, 'json', ['object_to_populate' => $groupe]);

        // UPDATE IN A ROW ALREADY EXIST
        if ($this->_repository->findOneByUpdate($groupeSerial, $groupeSerial->getName())) {
            return [
                "data" => [
                    "message" => "Groupe deja defini",
                    "errorCode" => 303
                ],
                "statusCode" => Response::HTTP_CONFLICT];
        }

        try {
            $em = $this->getDoctrine()->getManager();
            $em->persist($groupeSerial);
            $em->flush();
            return [
                "data" => [
                    "message" => "Modification effectuée avec succès",
                    "id" => $groupe->getId()
                ], "statusCode" => Response::HTTP_OK];
        } catch (\Doctrine\DBAL\Exception $e) {
            return [
                "data" => [
                    "message" => "Impossible de modifier ce groupe",
                    "error" => $e->getMessage()
                ],
                "statusCode" => Response::HTTP_BAD_REQUEST
            ];
        }
    }

    public static function isDateExpired(Groupe $groupe): bool
    {
        // RETURN TRUE IF DATA EXPIRED
        if (($groupe->getIsStripped() && $groupe->getDateEcheance()) || (!$groupe->getIsStripped() && $groupe->getDateEcheance())) {
            // IN THE CASE OF USER STRIPE
            $now = new \DateTime('now');
            $interval = $groupe->getDateEcheance()->diff($now);
            return $interval->days < 0;
        }
        return false;
    }

    public static function isRightTimeChangingForfaitBis(Groupe $groupe): bool
    {
        if ($groupe->getDateEcheance()) {
            $now = new \DateTime('now');
            $result = $now->diff($groupe->getDateEcheance());

            // To fix échéance date 48h before available.
            return $result->days <= 2 || ($groupe->getDateEcheance() < $now);
        }
        return false;
    }

    /**
     * @param User $user
     * @param ForfaitService|null $forfaitService
     * @return array|array[]
     */
    public static function endSubscription(User $user, ?ForfaitService $forfaitService): array
    {
        try {
            StripeService::endSubsription($user->getGroupe()->getSubId());
            // TURN TO DEFAULT ACCOUNT GRATUIT
            if ($forfaitService) {
                $forfaitService->setDefautlForfait(['id' => $user->getGroupe()->getId()]);
            }
            return [
                "data" => [
                    "message" => "Votre résiliation a été pris en compte avec succès"
                ],
                "statusCode" => Response::HTTP_OK
            ];
        } catch (\Exception $e) {
            return [
                "data" => [
                    "message" => "Impossible to résilier votre compte",
                    "error" => $e,
                    "statusCode" => Response::HTTP_BAD_REQUEST
                ]
            ];
        }

    }

    private function generateArrayGroupe($objects)
    {
        return array_map(function (Groupe $g) {
            return self::factoryGroupe($g);
        }, $objects);
    }

    public function factoryGroupe(Groupe $object)
    {
        return $this->get('serializer')->normalize($object, 'json', ['groups' => ['read']]);
    }

    /**
     * @param Groupe $groupe
     * @param Request $request
     * @return array
     */
    public function uploadImg(Groupe $groupe, Request $request): array
    {
        $data['img'] = $request->files->get('img');

        if (isset($data['img']) && $data['img']) {
            $imgFilename = $this->_imageService->addImage($data['img'], ImageService::LOGO_TREE, false);
            if (is_array($imgFilename)) {
                return $imgFilename;
            }
            $groupe->setImgLogo($imgFilename);
        }

        try {
            $em = $this->getDoctrine()->getManager();
            $em->persist($groupe);
            $em->flush();
            return [
                "data" => ["message" => "Logo upload avec success"],
                "statusCode" => Response::HTTP_OK
            ];

        } catch (\Doctrine\DBAL\Exception $e) {
            return [
                "data" => [
                    "message" => "Impossible de uploader ce logo",
                    "error" => $e->getMessage()
                ],
                "statusCode" => Response::HTTP_BAD_REQUEST
            ];
        }

    }

    static function getGroupType(): array
    {
        return self::GROUP_TYPE;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function getNoStripped(Request $request): array
    {
        $data = $this->_tokenService->MiddlewareAdminDedroUser($request->headers->get('Authorization'));
        if (!isset($data['user']) || !$data['user']) {
            return $data;
        }

        $result = $this->_repository->findAll();
        $_groups = [];
        foreach ($result as $group) {
            if (!$group->getIsStripped() && $group->getGroupeType() !== self::GROUP_TYPE[0]) {
                array_push($_groups, $this->factoryGroupe($group));
            }
        }
        return [
            'data' => $_groups,
            'statusCode' => Response::HTTP_OK
        ];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function changeModeGroupeStripped(Request $request): array
    {
        $data = $this->_tokenService->MiddlewareAdminDedroUser($request->headers->get('Authorization'));
        if (!isset($data['user']) || !$data['user']) {
            return $data;
        }

        /** @var Serializer $serializer */
        $serializer = $this->get('serializer');
        $data = $serializer->decode($request->getContent(), 'json');

        if (!isset($data['ids']) || !is_array($data['ids'])) {
            return [
                'data' => [
                    'message' => 'Information required'
                ],
                'statusCode' => Response::HTTP_CONFLICT
            ];
        }

        $groupRepository = $this->getDoctrine()->getRepository(Groupe::class);
        foreach ($data['ids'] as $id) {
            $group = $groupRepository->findOneBy(['id' => $id]);
            if ((!$group instanceof Groupe)) {
                return [
                    'data' => [
                        'message' => 'Group not exist'
                    ],
                    'statusCode' => Response::HTTP_NOT_FOUND
                ];
            }

            if (($group->getGroupeType() == self::GROUP_TYPE[0]) || $group->getIsStripped()) {
                return [
                    'data' => [
                        'message' => 'Impossible de mettre à jour ce groupe',
                    ],
                    'statusCode' => Response::HTTP_BAD_REQUEST
                ];
            }
            if (is_array(self::isUpdateStrippedModeGroup($group))) {
                return [
                    'data' => [
                        'message' => 'Message not filed'
                    ],
                    'statusCode' => Response::HTTP_BAD_REQUEST
                ];
            }
        }
        return [
            'data' => [
                'message' => 'Groupe(s) modifié(s) avec succès',
            ],
            'statusCode' => Response::HTTP_OK
        ];
    }

    /**
     * @param Groupe $group
     * @return array
     */
    public function isUpdateStrippedModeGroup(Groupe $group): ?array
    {
        /** @var Forfait $forfait */
        $forfait = $this->getDoctrine()->getRepository(Forfait::class)->findOneBy(['codeForfait' => 'GRATUIT']);
        $group->setIsStripped(true);
        $group->setForfait($forfait);

        // Set Date Echéance
        $group->setDateEcheance($this->_stripeService->setDateEcheance($forfait->getCodeForfait()));
        $group->setDateSubscribed(new \DateTime('now'));

        try {
            $this->getDoctrine()->getManager()->persist($forfait);
            $this->getDoctrine()->getManager()->flush();
            return null;
        } catch (\Exception $e) {
            return [
                'data' => [
                    'message' => 'Impossible de mettre a jour',
                    'statusCode' => Response::HTTP_BAD_REQUEST
                ]
            ];
        }
    }

    public function isGroupMappingInventory(array $invs, Groupe $group): bool
    {
        $data = array_filter($invs, function (Inventaire $inv) use ($group) {
            return $inv->getUser()->getGroupe()->getId() === $group->getId();
        });
        return count($data) >= 1;
    }

    /**
     * @param array $data
     * @return JsonResponse
     */
    public function newGroupe(array $data): JsonResponse
    {
        // GET INFORMATION FROM DATA
        $groupe = new Groupe();

        // DEMATERIALISATION EN MODE JSON
        $groupe->setName(strtoupper($data['name']));
        $groupe->setGroupeType(strtoupper($data['groupeType']));
        $groupe->setLicence($data['licence']);
        $groupe->setSubId($data['subId'] ?? null);
        $groupe->setIsStripped($data['isStripped'] ?? false);
        $groupe->setCustomerId($data['customerId'] ?? null);
        if ($data['dateEcheance']) {
            $groupe->setDateEcheance($data['dateEcheance']);
        }

        // SET SUBSCRIPTION
        $groupe->setDateSubscribed(new \DateTime('now'));

        // FORFAIT REPOSITORY
        if (strtoupper($data['groupeType']) != 'DENDROMAP') {

            if ($data['forfait']) {
                // GET FORFAIT
                if (is_numeric($data['forfait'])) {
                    $forfait = $this->getDoctrine()->getRepository(Forfait::class)->findOneBy(['id' => $data['forfait']]);
                    $groupe->setForfait($forfait->getId());
                } else {
                    // FORFAIT REPOSITORY
                    /** @var Forfait $forfait */
                    $forfait = $this->getDoctrine()->getRepository(Forfait::class)->findOneBy(['codeForfait' => $data['forfait']]);
                    $groupe->setForfait($forfait);
                }
            }
        }

        // HYDRATATION DE DONNEES
        $groupe->setCreatedAt(new \DateTime('now'));
        $groupe->setIsInit($data['isInit'] ?? '');

        if (!$groupe->getName()) {
            return new JsonResponse(
                [
                    "message" => "Informations obligatoire",
                    "errorCode" => 300
                ], Response::HTTP_BAD_REQUEST);
        }

        if ($this->getDoctrine()->getRepository(Groupe::class)->findBy(['name' => $groupe->getName()])) {
            return $this->json(
                [
                    "message" => "Ce groupe est deja défini",
                    "errorCode" => 301
                ], Response::HTTP_CONFLICT);
        }

        $errors = $this->_validator->validate($groupe);

        if (count($errors) > 0) {
            return $this->json(
                [
                    "message" => "Certaines valeurs ne sont pas conformes",
                    "errorCode" => 300
                ], Response::HTTP_BAD_REQUEST);
        }
        try {
            $em = $this->getDoctrine()->getManager();
            $em->persist($groupe);
            $em->flush();
            return $this->json([
                'errorCode' => 200,
                'data' => $this->get('serializer')->normalize($groupe, 'json', ['groups' => ['read']]
                )], Response::HTTP_CREATED);
        } catch (\Doctrine\DBAL\Exception $e) {
            return $this->json(["message" => "Impossible d'enrégistrer groupe"], Response::HTTP_BAD_REQUEST);
        }
    }
}
