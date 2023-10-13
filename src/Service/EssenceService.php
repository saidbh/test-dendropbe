<?php

namespace App\Service;

use App\Entity\Epaysage;
use App\Entity\Essence;
use App\Entity\Inventaire;
use App\Form\EssenceType;
use App\Repository\ChampignonsRepository;
use App\Repository\EssenceRepository;
use App\Repository\NuisibleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Serializer;

class EssenceService extends AbstractController
{
    private $_repository;
    private $_tokenService;
    private $_nuisibleRepository;
    private $_champignonsRepository;
    private $_bevaService;
    private $_champignonService;
    private $_nuisibleService;

    public function __construct(
        EssenceRepository     $_repository,
        TokenService          $_tokenService,
        NuisibleRepository    $_nuisibleRepository,
        ChampignonsRepository $_champignonsRepository,
        BevaService           $bevaService,
        ChampignonService     $champignonService,
        NuisibleService       $nuisibleService
    )
    {
        $this->_repository = $_repository;
        $this->_tokenService = $_tokenService;
        $this->_nuisibleRepository = $_nuisibleRepository;
        $this->_champignonsRepository = $_champignonsRepository;
        $this->_bevaService = $bevaService;
        $this->_champignonService = $champignonService;
        $this->_nuisibleService = $nuisibleService;
    }

    /**
     * @param Request $request
     * @param Essence $essence
     * @return array
     */
    public function delete(Request $request, Essence $essence): array
    {
        // DELETE ONE ESSENCE
        $data = $this->_tokenService->MiddlewareNormalUser($request->headers->get('Authorization'));
        if (!isset($data['user']) || !$data['user']) {
            return $data;
        }
        // DELETE DATA
        try {
            $em = $this->getDoctrine()->getManager();
            $em->remove($essence);
            $em->flush();
            return [
                "data" => [
                    "message" => "essence supprimé"
                ],
                "statusCode" => Response::HTTP_NO_CONTENT
            ];
        } catch (\Doctrine\DBAL\Exception $e) {
            return [
                "data" => [
                    "message" => "Inventaire supprimé avec succès"
                ],
                "statusCode" => Response::HTTP_BAD_REQUEST
            ];
        }
    }

    /**
     * @param Request $request
     * @param Essence $essence
     * @return array
     */
    public function update(Request $request, Essence $essence): array
    {
        // UPDATE ONE ESSENCE
        $data = $this->_tokenService->MiddlewareNormalUser($request->headers->get('Authorization'));
        if (!isset($data['user']) || !$data['user']) {
            return $data;
        }

        /** @var Serializer $serializer */
        $serializer = $this->get('serializer');
        $data = $serializer->decode($request->getContent(), 'json');

        $data['epaysage'] = $essence->getEpaysage()->getId();

        /** @var Inventaire $inventaire */
        $inventaire = $this->getDoctrine()->getManager()->getRepository(Inventaire::class)->findOneBy(['epaysage' => $data['epaysage']]);

        if (!$inventaire instanceof Inventaire) {
            return [
                "data" => [
                    "message" => 'inventaire is not define'
                ],
                "statusCode" => Response::HTTP_BAD_REQUEST
            ];
        }

        $inventaire->setUpdatedAt(new \DateTime('now'));

        if (!isset($data['diametre']) || !isset($data['hauteur']) || !isset($data['espece'])) {
            return [
                "data" => [
                    "message" => "Informations obligatoires"
                ],
                "statusCode" => Response::HTTP_BAD_REQUEST
            ];
        }

        if (!is_numeric($data['diametre']) || !is_numeric($data['hauteur']) || !is_numeric($data['espece'])) {
            return [
                "data" => [
                    "message" => "Informations obligatoires"
                ],
                "statusCode" => Response::HTTP_BAD_REQUEST
            ];
        }

        $data['imageUrl']['img1'] = $essence->getImg1();
        $data['imageUrl']['img2'] = $essence->getImg2();
        $data['imageUrl']['img3'] = $essence->getImg3();

        $form = $this->createForm(EssenceType::class, $essence);
        $form->submit($data);

        $essence->setImg1($data['imageUrl']['img1']);
        $essence->setImg2($data['imageUrl']['img2']);
        $essence->setImg3($data['imageUrl']['img3']);

        $essence->setStatusTravaux($essence->getStatusTravaux());
        $essence->setCritere(isset($data['critere']) && $data['critere'] ? $data['critere'] : []);
        $essence->setEtatGeneral(isset($data['etatGeneral']) ? $data['etatGeneral'] : []);
        $essence->setEtatSanGeneralChampignons(isset($data['etatSanGeneralChampignons']) ? array_unique($data['etatSanGeneralChampignons']) : []);
        $essence->setEtatSanGeneralParasite(isset($data['etatSanGeneralParasite']) ? array_unique($data['etatSanGeneralParasite']) : []);
        $essence->setNuisance(isset($data['nuisance']) ? $data['nuisance'] : []);
        $essence->setProximite(isset($data['proximite']) ? $data['proximite'] : []);
        $essence->setProximiteWithDict(isset($data['proximiteWithDict']) ? $data['proximiteWithDict'] : []);
        $essence->setTypePassage(isset($data['typePassage']) ? $data['typePassage'] : []);
        $essence->setTravaux(isset($data['travaux']) ? $data['travaux'] : []);

        if ($this->setProchaineViste($essence->getTravaux()) || in_array("exam-comple", $essence->getEtatGeneral())) {
            $essence->setDateTravaux(null);
            $essence->setDateProVisite(isset($data['dateTravaux']) ? $data['dateTravaux'] : '');
        } else {
            $essence->setDateProVisite(null);
            $essence->setDateTravaux(isset($data['dateTravaux']) ? $data['dateTravaux'] : '');
        }

        if (isset($data['userEditedDateTravaux']) && $data['userEditedDateTravaux']) {
            $essence->setUserEditedDateTravaux(new \DateTime('' . $data['userEditedDateTravaux'] . ''));
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($essence);
        $em->flush();

        return [
            "data" => [
                "message" => "operation success"
            ],
            "statusCode" => Response::HTTP_ACCEPTED
        ];
    }

    /**
     * @param array $travaux
     * @return bool
     */
    public function setProchaineViste(array $travaux): bool
    {
        $data = array_filter($travaux, function ($e) {
            return $e === 'aucun-travaux';
        });
        return count($data) >= 1 || count($travaux) == 0;
    }

    public function addEssence(Request $request)
    {
        // ADD ESSENCE WHEN YOU CREATE AN INVENTAIRE
        $data = $this->_tokenService->MiddlewareNormalUser($request->headers->get('Authorization'));
        if (!isset($data['user']) || !$data['user']) {
            return $data;
        }

        /** @var Serializer $serializer */
        $serializer = $this->get('serializer');
        $data = $serializer->decode($request->getContent(), 'json');

        if (!isset($data['diametre']) || !isset($data['hauteur']) || !isset($data['espece']) || !isset($data['epaysage'])) {
            return [
                "data" => [
                    "message" => "Informations obligatoires"
                ],
                "statusCode" => Response::HTTP_BAD_REQUEST
            ];
        }

        if (!is_numeric($data['diametre']) || !is_numeric($data['hauteur']) || !is_numeric($data['espece'])) {
            return [
                "data" => [
                    "message" => "Informations obligatoires"
                ],
                "statusCode" => Response::HTTP_BAD_REQUEST
            ];
        }

        // GET EPAYSAGE
        $epaysage = $this->getDoctrine()->getRepository(Epaysage::class)->findOneBy(['id' => $data['epaysage']]);

        if (!$epaysage instanceof Epaysage) {
            return [
                'data' => [
                    'message' => 'Impossible de upload cet inventaire'
                ],
                'statusCode' => Response::HTTP_BAD_REQUEST
            ];
        }
        // VERIFY IF ALREADY 5 ESSENCES
        if (count($epaysage->getEssence()) >= 5) {
            return [
                'data' => [
                    'message' => 'Cet inventaire comporte déjà 5 essences dominantes\'',
                    'errorCode' => 400
                ],
                'statusCode' => Response::HTTP_BAD_REQUEST
            ];
        }
        $essence = new Essence();

        $form = $this->createForm(EssenceType::class, $essence);
        $form->submit($data);

        $essence->setStatusTravaux($essence->getStatusTravaux());
        $essence->setCritere(isset($data['critere']) ? $data['critere'] : []);
        $essence->setEtatGeneral(isset($data['etatGeneral']) ? $data['etatGeneral'] : []);
        $essence->setEtatSanGeneralChampignons(isset($data['etatSanGeneralChampignons']) ? array_unique($data['etatSanGeneralChampignons']) : []);
        $essence->setEtatSanGeneralParasite(isset($data['etatSanGeneralParasite']) ? array_unique($data['etatSanGeneralParasite']) : []);
        $essence->setNuisance(isset($data['nuisance']) ? $data['nuisance'] : []);
        $essence->setProximite(isset($data['proximite']) ? $data['proximite'] : []);
        $essence->setProximiteWithDict(isset($data['proximiteWithDict']) ? $data['proximiteWithDict'] : []);
        $essence->setTypePassage(isset($data['typePassage']) ? $data['typePassage'] : []);
        $essence->setTravaux(isset($data['travaux']) ? $data['travaux'] : []);

        if ($this->setProchaineViste($essence->getTravaux()) || in_array("exam-comple", $essence->getEtatGeneral())) {
            $essence->setDateTravaux(null);
            $essence->setDateProVisite(isset($data['dateTravaux']) ? $data['dateTravaux'] : '');
        } else {
            $essence->setDateProVisite(null);
            $essence->setDateTravaux(isset($data['dateTravaux']) ? $data['dateTravaux'] : '');
        }

        if (isset($data['userEditedDateTravaux']) && $data['userEditedDateTravaux']) {
            $essence->setUserEditedDateTravaux(new \DateTime('' . $data['userEditedDateTravaux'] . ''));
        }
        try {
            $em = $this->getDoctrine()->getManager();
            $em->persist($essence);
            $em->flush();
            return [
                "data" => [
                    "message" => "operation success",
                    "id" => $essence->getId()
                ],
                "statusCode" => Response::HTTP_ACCEPTED
            ];
        } catch (\Doctrine\DBAL\Exception $e) {
            return [
                'data' => [
                    "message" => "Impossible d'enregistrer l'essence",
                    "errorCode" => $e->getMessage()
                ], 'statusCode' => Response::HTTP_BAD_REQUEST
            ];
        }


    }


    public function getEssence(Request $request, Essence $essence)
    {
        // AUTHORIZATION
        $data = $this->_tokenService->MiddlewareNormalUser($request->headers->get('Authorization'));
        if (!isset($data['user']) || !$data['user']) {
            return $data;
        }

        $_essence = $this->serializerEssence($essence);
        // RETURN DATA
        return [
            "data" => $_essence,
            "statusCode" => Response::HTTP_ACCEPTED
        ];
    }

    /**
     * @param Request $request
     * @param Essence $essence
     * @return array
     */
    public function deleteImg(Request $request, Essence $essence): array
    {
        $headers = $request->headers->get('Authorization');

        $data = $this->_tokenService->MiddlewareNormalUser($headers);
        if (!isset($data['user']) || !$data['user']) {
            return $data;
        }

        /** @var Serializer $serializer */
        $serializer = $this->get('serializer');
        $data = $serializer->decode($request->getContent(), 'json');

        if (isset($data['numImg']) && !is_numeric($data['numImg'])) {
            return [
                "data" => ["message" => "information obligatoire"],
                "statusCode" => Response::HTTP_BAD_REQUEST
            ];
        }
        if ($data['numImg'] == 1) {
            ImageService::deletephiscallyFile($essence->getImg1(), 'ESSENCE');
            $essence->setImg1(null);
        } elseif ($data['numImg'] == 2) {
            ImageService::deletephiscallyFile($essence->getImg2(), 'ESSENCE');
            $essence->setImg2(null);
        } elseif ($data['numImg'] == 3) {
            ImageService::deletephiscallyFile($essence->getImg3(), 'ESSENCE');
            $essence->setImg3(null);
        }
        try {
            $em = $this->getDoctrine()->getManager();
            $em->persist($essence);
            $em->flush();
            return [
                "data" => ["message" => "information obligatoire"],
                "statusCode" => Response::HTTP_OK
            ];
        } catch (\Exception $e) {
            return [
                "data" => ["message" => "information obligatoire"],
                "statusCode" => Response::HTTP_BAD_REQUEST
            ];
        }
    }

    public function serializerEssence(Essence $essence, $invSerializer = false)
    {
        $serializer = $this->get('serializer');
        $data = $serializer->normalize($essence, 'json', ['groups' => ['read']]);

        $data['imageUrl'] = [
            'img1' => $data['img1'],
            'img2' => $data['img2'],
            'img3' => $data['img3']
        ];

        unset($data['img1']);
        unset($data['img2']);
        unset($data['img3']);
        // set Parasite && Champignon
        $data['etatSanGeneralChampignons'] = $this->_champignonService->setChampignons($essence->getEtatSanGeneralChampignons());
        $data['etatSanGeneralParasite'] = $this->_nuisibleService->setNuisible($essence->getEtatSanGeneralParasite());
        // set Beva
        $data['beva'] = InventaireService::setBevaInventaire($essence);
        $data['epaysage'] = $essence->getEpaysage()->getId();
        $data['area'] = $essence->getEpaysage()->getArea();

        // Get Inventory
        /** @var Inventaire $inventaire */
        $inventaire = $this->getDoctrine()->getRepository(Inventaire::class)->findOneBy(['epaysage' => $essence->getEpaysage()->getId()]);

        // INventaire object
        $_inventaire['id'] = $inventaire->getId();
        $_inventaire['createdAt'] = $inventaire->getCreatedAt() ? $inventaire->getCreatedAt()->format('Y-m-d\TH:i:sO') :  '';
        $_inventaire['updatedAt'] = $inventaire->getUpdatedAt() ? $inventaire->getUpdatedAt()->format('Y-m-d\TH:i:sO') : '';;
        $data['inventaire'] = $_inventaire;

        if ($invSerializer) {
            unset($data['epaysage']);
            unset($data['inventaire']);
        }
        return $data;
    }
}
