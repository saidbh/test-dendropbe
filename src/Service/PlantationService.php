<?php

namespace App\Service;

use App\Entity\Espece;
use App\Entity\Inventaire;
use App\Entity\Plantation;
use App\Form\PlantationType;
use App\Repository\EspeceRepository;
use App\Repository\PlantationRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Intervention\Image\Exception\NotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Serializer;

class PlantationService extends AbstractController
{
    private $repository;
    private $especeRepository;
    private $tokenService;
    private $_mapService;
    private $_inventaireService;

    public function __construct(PlantationRepository $repository,
                                EspeceRepository     $especeRepository,
                                TokenService         $tokenService,
                                MapService           $mapService,
                                InventaireService $inventaireService)
    {
        $this->repository = $repository;
        $this->especeRepository = $especeRepository;
        $this->tokenService = $tokenService;
        $this->_mapService = $mapService;
        $this->_inventaireService = $inventaireService;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function addPlantation(Request $request): array
    {
        // AUTHORIZATION
        $data = $this->tokenService->MiddlewareNormalUser($request->headers->get('Authorization'));
        if (!isset($data['user']) || !$data['user']) {
            return $data;
        }

        // add Inventary
        $inventoryResponse = $this->_inventaireService->addTreeOrInventory($request, 'ARBRE');

        // Created not success
        if ($inventoryResponse['statusCode'] !== Response::HTTP_CREATED) {
            return $inventoryResponse;
        }

        $userId = $data['user']->getId();
        // END AUTHORISATION
        $plantation = new Plantation();

        /** @var Serializer $serializer */
        $serializer = $this->get('serializer');
        $data = $serializer->decode($request->getContent(), 'json');

        if (!isset($data['espece'])) {
            return [
                "data" => [
                    "message" => "Informations obligatoire"
                ],
                "statusCode" => Response::HTTP_BAD_REQUEST
            ];
        }

        /** @var Espece $espece */
        $espece = $this->especeRepository->findOneBy(['id' => $data['espece']]);

        if (!$espece instanceof Espece) {
            throw new NotFoundException('Espece not found');
        }

        $data['espece'] = $espece->getId();
        $data['createdAt'] = new \DateTime('now');

        $data['userAdded'] = $userId;

        $form = $this->createForm(PlantationType::class, $plantation);
        $form->submit($data);

        // PERSIST ADDRESS AND VILLE
        $addressFormatted = $this->_mapService->getAddress($data['coord']['lat'], $data['coord']['long']);

        $plantation->setAddress($addressFormatted['address'] ?? '');
        $plantation->setVille($addressFormatted['ville'] ?? '');
        $plantation->setPays($addressFormatted['pays'] ?? '');

        $plantation->setCreatedAt(new \DateTime('now'));
        $plantation->setDateEcheance(new \DateTime("" . $data['dateEcheance'] . ""));

        // Get
        $plantation->setInventory($this->getDoctrine()->getRepository(Inventaire::class)->find($inventoryResponse['data']['id']['id'])); // id Inventory

        if ($form->isSubmitted()) {
            try {
                $em = $this->getDoctrine()->getManager();
                $em->persist($plantation);
                $em->flush();
                return [
                    "data" => $this->generateObjectPlantation($plantation),
                    "statusCode" => Response::HTTP_CREATED
                ];

            } catch (\Exception $e) {
                return [
                    "data" => [
                        "message" => "Impossible de mettre a jour avec succès",
                        "errorCode" => 500
                    ],
                    "statusCode" => Response::HTTP_BAD_REQUEST
                ];
            }
        } else {
            return [
                "data" => [
                    "message" => "Certaines valeurs ne sont pas definies"
                ],
                "statusCode" => Response::HTTP_BAD_REQUEST
            ];
        }
    }

    public function getPlantations(Request $request): array
    {
        // GET HEARDERS
        $data = $this->tokenService->MiddlewareNormalUser($request->headers->get('Authorization'));
        if (!isset($data['user']) || !$data['user']) {
            return $data;
        }

        $user = $data['user'];
        $objects = $this->repository->findBy([], ['id' => 'DESC']);

        $result = [];
        foreach ($objects as $obj) {
            if ($obj->getUserAdded()->getId() === $user->getId()) {
                array_push($result, $obj);
            }
        }
        return [
            "data" => $this->generateArray($result),
            "statusCode" => Response::HTTP_OK
        ];
    }

    public function getPlantation(Request $request, Plantation $plantation)
    {
        $data = $this->tokenService->MiddlewareNormalUser($request->headers->get('Authorization'));
        if (!isset($data['user']) || !$data['user']) {
            return $data;
        }

        return [
            "data" => $this->generateObjectPlantation($plantation),
            "statusCode" => Response::HTTP_OK
        ];

    }

    public function generateArray($objects): array
    {
        return array_map(function (Plantation $champ) {
            return $this->generateObjectPlantation($champ);
        }, $objects);
    }

    /**
     * @param Request $request
     * @param Plantation $plantation
     * @return array
     */
    public function deleteSinglePlantation(Request $request, Plantation $plantation): array
    {
        $data = $this->tokenService->MiddlewareNormalUser($request->headers->get('Authorization'));
        if (!isset($data['user']) || !$data['user']) {
            return $data;
        }

        return PlantationService::deletePlantation($plantation, $this->getDoctrine()->getManager());
    }

    static function deletePlantation(Plantation $plantation, ObjectManager $em): array
    {
        try {
            $em->remove($plantation);
            $em->flush();
            // REMOVE EPAYSAGE OR ARBRE
            return [
                "data" => ["message" => "Plantation supprimé"],
                "statusCode" => Response::HTTP_NO_CONTENT
            ];

        } catch (\Doctrine\DBAL\DBALException $e) {
            return [
                "data" => [
                    "message" => "Operation impossible"
                ],
                "statusCode" => Response::HTTP_BAD_REQUEST
            ];
        }
    }

    public function deleteManyPlantation(Request $request)
    {
        $data = $this->tokenService->MiddlewareNormalUser($request->headers->get('Authorization'));
        if (!isset($data['user']) || !$data['user']) {
            return $data;
        }

        $body = $request->getContent();
        $data = json_decode($body, true);

        foreach ($data['ids'] as $id) {
            // GET INVENTAIRE
            try {
                $plantation = $this->repository->findOneBy(['id' => $id]);
                if ($plantation instanceof Plantation) {
                    PlantationService::deletePlantation($plantation, $this->getDoctrine()->getManager());
                }
            } catch (\Exception $e) {
                return [
                    "data" => [
                        "message" => "Operation impossible"
                    ],
                    "statusCode" => Response::HTTP_BAD_REQUEST
                ];
            }

        }
        return [
            "data" => [
                "message" => "plantations supprimées"
            ],
            "statusCode" => Response::HTTP_NO_CONTENT
        ];
    }

    /**
     * @param Plantation $object
     * @return array
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function generateObjectPlantation(Plantation $object): array
    {
        /** @var Serializer $serializer */
        $serializer = $this->get('serializer');
        $data = $serializer->normalize($object, 'json', ['groups' => ['read']]);

        // serializer Inventory
        $data['inventory'] = $object->getInventory() ? $object->getInventory()->getId() : null;
        return $data;
    }
}
