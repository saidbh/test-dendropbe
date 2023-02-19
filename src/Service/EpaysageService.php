<?php

namespace App\Service;

use App\Entity\Epaysage;
use App\Entity\Essence;
use App\Entity\Inventaire;
use App\Form\EpaysageType;
use App\Form\EssenceType;
use App\Form\InventaireType;
use App\Repository\EpaysageRepository;
use App\Repository\EspeceRepository;
use App\Repository\EssenceRepository;
use App\Repository\InventaireRepository;
use CrEOF\Spatial\PHP\Types\Geometry\LineString;
use CrEOF\Spatial\PHP\Types\Geometry\Point;
use CrEOF\Spatial\PHP\Types\Geometry\Polygon;
use Location\Coordinate;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\Serializer;

// SERVICE
class EpaysageService extends AbstractController
{

    private $repository;
    private $especeRepository;
    private $essenceRepository;
    private $tokenService;
    private $imageService;
    private $inventaireRepository;
    private $_essenceService;
    private $bus;

    public function __construct(
        EpaysageRepository   $repository,
        EspeceRepository     $especeRepository,
        EssenceRepository    $essenceRepository,
        MessageBusInterface  $bus,
        TokenService         $tokenService,
        ImageService         $imageService,
        InventaireRepository $inventaireRepository,
        EssenceService       $essenceService
    )
    {
        $this->repository = $repository;
        $this->especeRepository = $especeRepository;
        $this->essenceRepository = $essenceRepository;
        $this->bus = $bus;
        $this->tokenService = $tokenService;
        $this->imageService = $imageService;
        $this->inventaireRepository = $inventaireRepository;
        $this->_essenceService = $essenceService;
    }

    /**
     * @param array $data
     * @return array
     */
    public function add(array $data): array
    {
        // ADD INVENTAIRE EPAYSAGE
        $dataEpaysage = $this->newEpaysage($data);

        if ($dataEpaysage['errorCode'] != 200) {
            return [
                'message' => 'Impossible d\' enregistrer cet inventaire',
                'errorCode' => 304
            ];
        }
        // GET ARBRE
        $data['epaysage'] = $this->getDoctrine()->getRepository(Epaysage::class)->findOneBy(["id" => $dataEpaysage['id']]);

        foreach ($data['essence'] as $essence) {
            $addEspece = $this->addEssence($essence, $data['epaysage']);
            if ($addEspece['errorCode'] != Response::HTTP_CREATED) {
                return [
                    'message' => 'error added espece',
                    "errorCode" => 403
                ];
            }
        }

        $data['isFinished'] = isset($data['isFinished']) ? $data['isFinished'] : false;
        return [
            'data' => $data,
            'errorCode' => 200
        ];
    }

    /**
     * @param array $travaux
     * @return bool
     */
    public function setProchaineViste(array $travaux): bool
    {
        $data = array_filter($travaux, function ($e) {
            return $e === 'aucun-travaux' || $e === null;
        });
        return count($data) >= 1;
    }

    public function addEssence($data, $epaysage)
    {
        // ADD ESSENCE ON DATABASE AND RETURN ID ESSENCE
        $essence = new Essence();
        $data['epaysage'] = $epaysage->getId();

        // UPDATE AND
        if (isset($data['id'])) {

            return $this->updateEssenceWhenPut($data, $epaysage);

        }

        if ($data['espece'] && $data['epaysage'] && is_numeric($data['espece']) && is_numeric($data['epaysage'])) {
            $data['espece'] = $this->especeRepository->findOneBy(['id' => $data['espece']]);
            $data['epaysage'] = $this->repository->findOneBy(['id' => $data['epaysage']]);

            if (!$data['espece'] && !$data['epaysage']) {

                return ["errorCode" => Response::HTTP_BAD_REQUEST];
            }

            $essence->setEspece($data["espece"]);
            $essence->setEpaysage($data["epaysage"]);

            if (isset($data['userEditedDateTravaux']) && $data['userEditedDateTravaux']) {
                $essence->setUserEditedDateTravaux(new \DateTime('' . $data['userEditedDateTravaux'] . ''));
            }

            $form = $this->createForm(EssenceType::class, $essence);
            $form->submit($data);

            $essence->setStatusTravaux($essence->getStatusTravaux());
            $essence->setCritere($data['critere'] ? $data['critere'] : []);
            $essence->setEtatGeneral($data['etatGeneral'] ? $data['etatGeneral'] : []);
            $essence->setEtatSanGeneralChampignons($data['etatSanGeneralChampignons'] ? $data['etatSanGeneralChampignons'] : []);
            $essence->setEtatSanGeneralParasite($data['etatSanGeneralParasite'] ? $data['etatSanGeneralParasite'] : []);
            $essence->setNuisance($data['nuisance'] ? $data['nuisance'] : []);
            $essence->setProximite(isset($data['proximite']) ? $data['proximite'] : []);
            $essence->setProximiteWithDict(isset($data['proximiteWithDict']) ? $data['proximiteWithDict'] : []);
            $essence->setTypePassage($data['typePassage'] ? $data['typePassage'] : []);
            $essence->setTravaux($data['travaux'] ? $data['travaux'] : []);

            if ($this->setProchaineViste($essence->getTravaux()) || in_array("exam-comple", $essence->getEtatGeneral())) {
                $essence->setDateTravaux(null);
                $essence->setDateProVisite($data['dateTravaux']);
            } else {
                $essence->setDateProVisite(null);
                $essence->setDateTravaux($data['dateTravaux']);
            }

            if (isset($data['healthIndex'])) {
                $essence->setHealthIndex($data['healthIndex']);
            }
            if (isset($data['varietyGrade'])) {
                $essence->setVarietyGrade($data['varietyGrade']);
            }
            if (isset($data['aestheticIndex'])) {
                $essence->setAestheticIndex($data['aestheticIndex']);
            }
            if (isset($data['locationIndex'])) {
                $essence->setLocationIndex($data['locationIndex']);
            }

            if (isset($data['healthColumn'])) {
                $essence->setHealthColumn($data['healthColumn']);
            }
            if (isset($data['aestheticColumn'])) {
                $essence->setAestheticColumn($data['aestheticColumn']);
            }

            // ADD ALL
            if ($form->isSubmitted()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($essence);
                $em->flush();
                return ["errorCode" => Response::HTTP_CREATED];
            } else {
                return ["errorCode" => Response::HTTP_BAD_REQUEST];
            }
        } else {
            return ["errorCode" => Response::HTTP_BAD_REQUEST];
        }
    }

    /**
     * @param $data
     * @param $epaysageObject
     * @return array
     */
    public function updateEssenceWhenPut($data, $epaysageObject): array
    {
        $essence = $this->essenceRepository->findOneBy(['id' => $data['id']]);
        if (!$essence) {
            return ['errorCode' => Response::HTTP_BAD_REQUEST];
        }

        $data['epaysage'] = $epaysageObject->getId();

        $data['img1'] = $essence->getImg1();
        $data['img2'] = $essence->getImg2();
        $data['img3'] = $essence->getImg3();

        $form = $this->createForm(EssenceType::class, $essence);
        $form->submit($data);

        $essence->setStatusTravaux($essence->getStatusTravaux());
        $essence->setCritere($data['critere'] ? $data['critere'] : []);
        $essence->setEtatGeneral($data['etatGeneral'] ? $data['etatGeneral'] : []);
        $essence->setEtatSanGeneralChampignons($data['etatSanGeneralChampignons'] ? $data['etatSanGeneralChampignons'] : []);
        $essence->setEtatSanGeneralParasite($data['etatSanGeneralParasite'] ? $data['etatSanGeneralParasite'] : []);
        $essence->setNuisance($data['nuisance'] ? $data['nuisance'] : []);
        $essence->setProximite($data['proximite'] ? $data['proximite'] : []);
        $essence->setProximitewithDict(isset($data['proximiteWithDict']) ? $data['proximiteWithDict'] : []);
        $essence->setTypePassage($data['typePassage'] ? $data['typePassage'] : []);
        $essence->setTravaux($data['travaux'] ? $data['travaux'] : []);

        if (isset($data['userEditedDateTravaux']) && $data['userEditedDateTravaux']) {
            $essence->setUserEditedDateTravaux(new \DateTime('' . $data['userEditedDateTravaux'] . ''));
        }

        if ($this->setProchaineViste($essence->getTravaux()) || in_array("exam-comple", $essence->getEtatGeneral())) {
            $essence->setDateTravaux(null);
            $essence->setDateProVisite($data['dateTravaux']);
        } else {
            $essence->setDateProVisite(null);
            $essence->setDateTravaux($data['dateTravaux']);
        }


        if (isset($data['healthIndex'])) {
            $essence->setHealthIndex($data['healthIndex']);
        }
        if (isset($data['varietyGrade'])) {
            $essence->setVarietyGrade($data['varietyGrade']);
        }
        if (isset($data['aestheticIndex'])) {
            $essence->setAestheticIndex($data['aestheticIndex']);
        }
        if (isset($data['locationIndex'])) {
            $essence->setLocationIndex($data['locationIndex']);
        }

        if (isset($data['healthColumn'])) {
            $essence->setHealthColumn($data['healthColumn']);
        }
        if (isset($data['aestheticColumn'])) {
            $essence->setAestheticColumn($data['aestheticColumn']);
        }

        if ($form->isSubmitted()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($essence);
            $em->flush();
            return ["errorCode" => Response::HTTP_CREATED];
        } else {
            return ["errorCode" => Response::HTTP_BAD_REQUEST];
        }

    }

    // UPDATE EPAYSAGE

    /**
     * @param Request $request
     * @param Inventaire $inventaire
     * @return array
     */
    public function update(Request $request, Inventaire $inventaire): array
    {
        /*********************** START AUTHORIZATION ***************************/
        $data = $this->tokenService->MiddlewareNormalUser($request->headers->get('Authorization'));

        if (!isset($data['user']) || !$data['user']) {
            return $data;
        }
        /***************************** END WRIGHT AUTHORIZATION ************************/
        /** @var Serializer $serializer */
        $serializer = $this->get('serializer');
        $data = $serializer->decode($request->getContent(), 'json');

        // TEST VARIABLE IF IS EXIST
        $epaysage = $this->repository->findOneBy(['id' => $inventaire->getEpaysage()->getId()]);

        $data['user'] = $inventaire->getUser()->getId();

        // INVENTAIRE DATA TO HYDRATE
        $dataInventaire['type'] = isset($data['type']) ? $data['type'] : $inventaire->getType();
        $dataInventaire['user'] = $inventaire->getUser();
        $dataInventaire['isFinished'] = isset($data['isFinished']) ? $data['isFinished'] : $inventaire->getIsFinished();
        $dataInventaire['epaysage'] = $epaysage->getId();
        $dataInventaire['createdAt'] = $inventaire->getCreatedAt();
        // DATA EPAYSAGE

        if (isset($data['coord'])) {
            foreach ($data['coord'] as $coord) {
                $point = new Point($coord['lat'], $coord['long']);
                $pointTab [] = $point;
            }
            $dataEpaysage['coord'] = new Polygon(array(new LineString($pointTab)));

        } else {
            $dataEpaysage['coord'] = $epaysage->getCoord();
        }
        // EPAYSAGE
        $dataEpaysage['id'] = $epaysage->getId();
        $dataEpaysage['address'] = $epaysage->getAddress();
        $dataEpaysage['ville'] = $epaysage->getVille();
        $dataEpaysage['pays'] = $epaysage->getPays();
        $dataEpaysage['essence'] = $epaysage->getEssence();

        // INVENTAIRE
        if (isset($data['essence']) && $data['essence']) {
            // UPDATE ESSENCE
            for ($i = 0; $i < count($data['essence']); $i++) {
                $dataBack = $this->addEssence($data['essence'][$i], $epaysage);

                if ($dataBack["errorCode"] != Response::HTTP_CREATED) {
                    return [
                        "data" => $dataBack,
                        "statusCode" => Response::HTTP_BAD_REQUEST
                    ];
                }
            }
        }

        // UPDATE EPAYSAGE
        $backEpaysage = $this->updateEpaysage($dataEpaysage, $epaysage);

        if ($backEpaysage['statusCode'] == Response::HTTP_BAD_REQUEST) {
            return $backEpaysage;
        }

        // UPDATE INVENTAIRE
        $form = $this->createForm(InventaireType::class, $inventaire);
        $form->submit($dataInventaire);

        $inventaire->setUpdatedAt(new \DateTime('now'));
        // UPDATE FORM
        try {
            $em = $this->getDoctrine()->getManager();
            $em->persist($inventaire);
            $em->flush();
            return [
                'data' => [
                    "message" => "Inventaire mis a jour avec succès",
                    "errorCode" => 200
                ],
                'statusCode' => Response::HTTP_OK
            ];
        } catch (\Doctrine\DBAL\Exception $e) {
            return [
                "data" =>
                    [
                        "message" => "Impossible de mettre a jour avec succès",
                        "errorCode" => 500,
                        "error" => $e->getMessage()
                    ],
                "statusCode" => Response::HTTP_BAD_REQUEST
            ];
        }
    }

    /**
     * @param array $arrayToUpdate
     * @param Epaysage $epaysage
     * @return array
     */
    public function updateEpaysage(array $arrayToUpdate, Epaysage $epaysage): array
    {
        $form = $this->createForm(EpaysageType::class, $epaysage);
        $form->submit($arrayToUpdate);

        try {
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            return [
                'data' => [
                    "message" => "Inventaire mis a jour avec succès",
                    "errorCode" => 200
                ],
                'statusCode' => Response::HTTP_ACCEPTED
            ];
        } catch (\Doctrine\DBAL\Exception $e) {
            return [
                "data" =>
                    [
                        "message" => "Impossible de mettre a jour avec succès",
                        "errorCode" => 500,
                        "error" => $e->getMessage()
                    ],
                "statusCode" => Response::HTTP_BAD_REQUEST
            ];
        }
    }

    public function newEpaysage($addObject): array
    {
        $epaysage = new Epaysage();
        if (!isset($addObject['coord'])) {
            return [
                'message' => 'Saisir informations obligatoires',
                'errorCode' => 300
            ];
        }
        // CAS D'UN POLYGON
        $pointTab = [];
        $polygonToCalculateArea = new \Location\Polygon();
        foreach ($addObject['coord'] as $coord) {
            $point = new Point($coord['lat'], $coord['long']);
            array_push($pointTab, $point);
            $polygonToCalculateArea->addPoint(new Coordinate($coord['lat'], $coord["long"]));
        }
        // FIXED POLYGON COORD
        $epaysage->setCoord(new Polygon(array(new LineString($pointTab))));
        $epaysage->setArea($polygonToCalculateArea->getArea());

        $addressFormatted = MapService::getAddress($addObject['coord'][0]['lat'], $addObject['coord'][0]['long']);
        // ADDRESS AND VILLE
        $epaysage->setAddress($addressFormatted['address'] ?? '');
        $epaysage->setVille($addressFormatted['ville'] ?? '');
        $epaysage->setPays($addressFormatted['pays'] ?? '');
        // ADD ESPACE BOISEE
        try {
            $this->getDoctrine()->getManager()->persist($epaysage);
            $this->getDoctrine()->getManager()->flush();
            return [
                "message" => "Enregistrement effectué avec succès",
                "id" => $epaysage->getId(),
                "errorCode" => 200
            ];
        } catch (\Doctrine\DBAL\Exception $e) {
            return [
                "message" => "Impossible d'enrégistrer cet espace boisé",
                "errorCode" => 500
            ];
        }
    }

    /**
     * @param Inventaire $inventaire
     * @return array
     */
    public static function formatAddInventoryObject(Inventaire $inventaire): array
    {
        return [
            'id' => $inventaire->getId(),
            'type' => $inventaire->getType(),
            'epaysage' => $inventaire->getEpaysage()->getId(),
            'essence' => $inventaire->getEpaysage()->getEssence()
        ];
    }
}
