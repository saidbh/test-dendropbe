<?php

namespace App\Service;

use App\Entity\Arbre;
use App\Entity\Espece;
use App\Entity\Inventaire;
use App\Entity\Nuisible;
use App\Form\ArbreType;
use CrEOF\Spatial\PHP\Types\Geometry\Point;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Serializer;


class ArbreService extends AbstractController
{
    private $_tokenService;
    private $_mapService;
    private $_champignonService;

    public function __construct(
        TokenService      $tokenService,
        MapService        $mapService,
        ChampignonService $champignonService
    )
    {
        $this->_tokenService = $tokenService;
        $this->_mapService = $mapService;
        $this->_champignonService = $champignonService;
    }

    /**
     * @param array $data
     * @return array
     */
    public function add(array $data): array
    {

        if (!isset($data['espece']) || !isset($data['coord'])
            || !isset($data['diametre']) || !isset($data['hauteur'])) {
            return [
                'message' => 'Informations obligatoires',
                'errorCode' => 300
            ];
        }

        if (isset($data['imgUrl']['img1']) && $data['imgUrl']['img1']) {
            $data['img1'] = $data['imgUrl']['img1'];
        }

        if (isset($data['imgUrl']['img2']) && $data['imgUrl']['img2']) {
            $data['img2'] = $data['imgUrl']['img2'];
        }

        if (isset($data['imgUrl']['img3']) && $data['imgUrl']['img3']) {
            $data['img3'] = $data['imgUrl']['img3'];
        }
        $data['arbre'] = $this->newArbre($data);

        if ($data['arbre']['errorCode'] != 200) {
            return [
                'message' => 'Impossible d\' enregistrer le diagnotisque',
                'errorCode' => 304
            ];
        }

        $data['type'] = 'ARBRE';
        $data['arbre'] = $data['arbre']['id'];

        $data['isFinished'] = isset($data['isFinished']) ? $data['isFinished'] : false;

        return [
            'data' => $data,
            'errorCode' => 200
        ];
    }

    public function update(Request $request)
    {
        $data = $this->_tokenService->MiddlewareNormalUser($request->headers->get('Authorization'));
        if (!isset($data['user']) || !$data['user']) {
            return $data;
        }

        $user = $data['user'];

    }

    public function deleteArbre(Request $request, Arbre $arbre)
    {
        // DELETE ARBRE
        $data = $this->_tokenService->MiddlewareNormalUser($request->headers->get('Authorization'));
        if (!isset($data['user']) || !$data['user']) {
            return $data;
        }

        try {
            $em = $this->getDoctrine()->getManager();
            $em->remove($arbre);
            $em->flush();
            // REMOVE EPAYSAGE OR ARBRE
            return [
                "statusCode" => Response::HTTP_NO_CONTENT
            ];

        } catch (\Doctrine\DBAL\DBALException $e) {
            return [
                "data" => [
                    "error" => $e->getMessage()
                ],
                "statusCode" => Response::HTTP_BAD_REQUEST
            ];
        }
    }

    /**
     * @param Request $request
     * @param Arbre $arbre
     * @return array
     */
    public function deleteImg(Request $request, Arbre $arbre): array
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
            ImageService::deletephiscallyFile($arbre->getImg2());
            $arbre->setImg1(null);
            // Delete physically file
        } elseif ($data['numImg'] == 2) {
            ImageService::deletephiscallyFile($arbre->getImg2());
            $arbre->setImg2(null);
        } elseif ($data['numImg'] == 3) {
            ImageService::deletephiscallyFile($arbre->getImg3());
            $arbre->setImg3(null);
        }
        try {
            $em = $this->getDoctrine()->getManager();
            $em->persist($arbre);
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

    /**
     * @param Request $request
     * @return array
     */
    public function getAddressWithCoord(Request $request): array
    {
        // CHANGE COORD EVERY BODY
        $data = $this->_tokenService->MiddlewareNormalUser($request->headers->get('Authorization'));
        if (!isset($data['user']) || !$data['user']) {
            return $data;
        }
        //
        /** @var Serializer $serializer */
        $serializer = $this->get('serializer');
        $data = $serializer->decode($request->getContent(), 'json');
        // GET POINT
        if (!isset($data['lat']) || !isset($data['long'])) {
            return [
                'data' => ['message' => 'informations manquantes'],
                'statusCode' => Response::HTTP_BAD_REQUEST
            ];
        }
        $point = new Point($data['lat'], $data['long']);
        if (!$point instanceof Point) {
            return [
                'data' => ['message' => 'Coordonnées géographiques ne sont pas bonnes'],
                'statusCode' => Response::HTTP_BAD_REQUEST
            ];
        }
        $addressFormatted = $this->_mapService->getAddress($data['lat'], $data['long']);
        // GET ADDRESS
        try {
            return [
                'data' => [
                    'ville' => $addressFormatted['ville'],
                    'address' => $addressFormatted['address']
                ],
                'statusCode' => Response::HTTP_OK
            ];
        } catch (\Exception $e) {
            return [
                'data' => ['message' => 'Impossible to change Coord'],
                'statusCode' => Response::HTTP_BAD_REQUEST
            ];
        }
    }

    /**
     * @param Request $request
     * @param Arbre $arbre
     * @return array
     */
    public function updateCoordinate(Request $request, Arbre $arbre): array
    {
        $data = $this->_tokenService->MiddlewareNormalUser($request->headers->get('Authorization'));
        if (!isset($data['user']) || !$data['user']) {
            return $data;
        }

        /** @var Serializer $serializer */
        $serializer = $this->get('serializer');
        $data = $serializer->decode($request->getContent(), 'json');

        if (!isset($data['address']) || !isset($data['ville']) || !isset($data['lat']) || !isset($data['long'])) {
            return [
                'data' => ['message' => 'information manquante'],
                'statusCode' => Response::HTTP_BAD_REQUEST
            ];
        }

        $point = new Point($data['lat'], $data['long']);
        if (!$point instanceof Point) {
            return [
                'data' => ['message' => 'Coordonnées géographiques ne sont pas bonnes'],
                'statusCode' => Response::HTTP_BAD_REQUEST
            ];
        }

        // GET ADDRESS
        $arbre->setAddress($data['address']);
        $arbre->setVille($data['ville']);
        $arbre->setCoord($point);
        try {
            $em = $this->getDoctrine()->getManager();
            $em->persist($arbre);
            $em->flush();
            return [
                'data' => ['message' => 'Adresse and Ville modify with success'],
                'statusCode' => Response::HTTP_OK
            ];
        } catch (\Exception $e) {
            return [
                'data' => ['message' => 'Impossible to change Address'],
                'statusCode' => Response::HTTP_BAD_REQUEST
            ];
        }
    }

    public function updateArbre(array $ArrayDataArbre, Arbre $arbre)
    {
        $coord = null;
        if (isset($ArrayDataArbre['coord'])) {
            $point = new Point($ArrayDataArbre['coord']['lat'], $ArrayDataArbre['coord']['long']);
            if (($point->getX() != $arbre->getCoord()->getX()) or ($point->getY() != $arbre->getCoord()->getY())) {
                $ArrayDataArbre['coord'] = $point;
            }
            $coord = $ArrayDataArbre['coord'];

            // Get ville && address
            $addressFormatted = MapService::getAddress($ArrayDataArbre['coord']['lat'], $ArrayDataArbre['coord']['long']);
            $arbre->setAddress($addressFormatted['address']);
            $arbre->setVille($addressFormatted['ville']);
            $arbre->setPays($addressFormatted['pays']);

        } else {
            $ArrayDataArbre['coord'] = $arbre->getCoord();
        }

        if (empty($ArrayDataArbre['imgUrl'])) {
            $ArrayDataArbre['imgUrl']['img1'] = $arbre->getImg1();
            $ArrayDataArbre['imgUrl']['img2'] = $arbre->getImg2();
            $ArrayDataArbre['imgUrl']['img3'] = $arbre->getImg3();
        }

        $ArrayDataArbre['address'] = $arbre->getAddress();
        $ArrayDataArbre['ville'] = $arbre->getVille();
        $ArrayDataArbre['pays'] = $arbre->getPays();
        $ArrayDataArbre['createdAt'] = $arbre->getCreatedAt();

        $form = $this->createForm(ArbreType::class, $arbre);
        $form->submit($ArrayDataArbre);

        $arbre->setStatusTravaux($arbre->getStatusTravaux());
        $arbre->setImg1($ArrayDataArbre['imgUrl']['img1']);
        $arbre->setImg2($ArrayDataArbre['imgUrl']['img2']);
        $arbre->setImg3($ArrayDataArbre['imgUrl']['img3']);

        $arbre->setCaractPied($ArrayDataArbre['caractPied'] ?? []);
        $arbre->setDict(isset($ArrayDataArbre['dict']) ? $ArrayDataArbre['dict'] : []);
        $arbre->setCritere(isset($ArrayDataArbre['critere']) ? $ArrayDataArbre['critere'] : []);
        $arbre->setNuisance($ArrayDataArbre['nuisance'] ? $ArrayDataArbre['nuisance'] : []);
        $arbre->setProximite($ArrayDataArbre['proximite'] ? $ArrayDataArbre['proximite'] : []);

        if (isset($ArrayDataArbre['proximiteWithDict'])) {
            $arbre->setProximiteWithDict($ArrayDataArbre['proximiteWithDict']);
        }
        $arbre->setTypePassage($ArrayDataArbre['typePassage'] ? $ArrayDataArbre['typePassage'] : []);
        $arbre->setEtatSanCollet($ArrayDataArbre['etatSanCollet'] ? $ArrayDataArbre['etatSanCollet'] : []);
        $arbre->setEtatSanTronc($ArrayDataArbre['etatSanTronc'] ? $ArrayDataArbre['etatSanTronc'] : []);
        $arbre->setEtatSanGeneral($ArrayDataArbre['etatSanGeneral'] ? $ArrayDataArbre['etatSanGeneral'] : []);
        $arbre->setEtatSanHouppierNuisibles($ArrayDataArbre['etatSanHouppierNuisibles'] ? array_unique($ArrayDataArbre['etatSanHouppierNuisibles']) : []);
        $arbre->setEtatSanColletChampignons($ArrayDataArbre['etatSanColletChampignons'] ? array_unique($ArrayDataArbre['etatSanColletChampignons']) : []);
        $arbre->setEtatSanTroncNuisibles($ArrayDataArbre['etatSanTroncNuisibles'] ? array_unique($ArrayDataArbre['etatSanTroncNuisibles']) : []);
        $arbre->setEtatSanTroncChampignons($ArrayDataArbre['etatSanTroncChampignons'] ? array_unique($ArrayDataArbre['etatSanTroncChampignons']) : []);
        $arbre->setEtatSanHouppierNuisibles($ArrayDataArbre['etatSanHouppierNuisibles'] ? array_unique($ArrayDataArbre['etatSanHouppierNuisibles']) : []);
        $arbre->setEtatSanHouppier($ArrayDataArbre['etatSanHouppier'] ? $ArrayDataArbre['etatSanHouppier'] : []);
        $arbre->setEtatSanHouppierChampignons($ArrayDataArbre['etatSanHouppierChampignons'] ? array_unique($ArrayDataArbre['etatSanHouppierChampignons']) : []);

        $arbre->setTravauxColletMultiple(isset($ArrayDataArbre['travauxColletMultiple']) ? $ArrayDataArbre['travauxColletMultiple'] : []);
        $arbre->setTravauxTroncMultiple(isset($ArrayDataArbre['travauxTroncMultiple']) ? $ArrayDataArbre['travauxTroncMultiple'] : []);
        $arbre->setTravauxHouppierMultiple(isset($ArrayDataArbre['travauxHouppierMultiple']) ? $ArrayDataArbre['travauxHouppierMultiple'] : []);
        $arbre->setEtatSanGeneralOther(isset($ArrayDataArbre['etatSanGeneralOther']) ? $ArrayDataArbre['etatSanGeneralOther'] : '');


        if (($this->setProchaineViste($arbre->getTravauxColletMultiple()) &&
                $this->setProchaineViste($arbre->getTravauxTroncMultiple()) &&
                $this->setProchaineViste($arbre->getTravauxHouppierMultiple())) || in_array("exam-comple", $arbre->getEtatSanGeneral())) {
            $arbre->setDateTravaux(null);
            $arbre->setDateProVisite(isset($ArrayDataArbre['dateTravaux']) ? $ArrayDataArbre['dateTravaux'] : '');
        } else {
            $arbre->setDateProVisite(null);
            $arbre->setDateTravaux(isset($ArrayDataArbre['dateTravaux']) ? $ArrayDataArbre['dateTravaux'] : '');
        }

        // Format address
        if (!$coord) {
            if (($point->getX() != $arbre->getCoord()->getX()) or ($point->getY() != $arbre->getCoord()->getY())) {
                $addressFormatted = $this->_mapService->getAddress($arbre->getCoord()->getX(), $arbre->getCoord()->getY());
                $arbre->setAddress($addressFormatted['address'] ?? '');
                $arbre->setVille($addressFormatted['ville'] ?? '');
                $arbre->setPays($addressFormatted['pays'] ?? '');
            }
        }

        $arbre->setRisqueGeneral(isset($ArrayDataArbre['risqueGeneral']) ? $ArrayDataArbre['risqueGeneral'] : []);
        $arbre->setRisque(isset($ArrayDataArbre['risque']) ? $ArrayDataArbre['risque'] : []);

        // IMAGES
        if (isset($ArrayDataArbre['userEditedDateTravaux']) && $ArrayDataArbre['userEditedDateTravaux']) {
            $arbre->setUserEditedDateTravaux(new \DateTime('' . $ArrayDataArbre['userEditedDateTravaux'] . ''));
        }
        // Others champignons
        $arbre->setEtatSanColletChampignonsAutres($ArrayDataArbre['etatSanColletChampignonsAutres'] ?? null);
        $arbre->setEtatSanTroncChampignonsAutres($ArrayDataArbre['etatSanTroncChampignonsAutres'] ?? null);
        $arbre->setEtatSanHouppierChampignonsAutres($ArrayDataArbre['etatSanHouppierChampignonsAutres'] ?? null);

        // SET ABATTAGE ARBRE
        $arbre = TravauxService::setAbattageArbre($arbre);

        $em = $this->getDoctrine()->getManager();
        $em->persist($arbre);
        $em->flush();
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

    /**
     * @param Arbre $arbre
     * @return array
     */
    public function serializer(Arbre $arbre): array
    {
        /** @var Serializer $serializer */
        $serializer = $this->get('serializer');
        return $serializer->normalize($arbre, 'json', ['groups' => ['read']]);
    }

    /**
     * @param Inventaire $object
     * @return array
     */
    public function generateObjectArbreJson(Inventaire $object): array
    {

        $_arbre['id'] = $object->getArbre()->getId();
        $_arbre['diametre'] = $object->getArbre()->getDiametre();

        // NEW FIELD
        $_arbre['hauteur'] = $object->getArbre()->getHauteur();

        $_arbre['numSujet'] = $object->getArbre()->getNumSujet();
        $_arbre['codeSite'] = $object->getArbre()->getCodeSite();
        $_arbre['caractPied'] = $object->getArbre()->getCaractPied();
        $_arbre['caractPiedOther'] = $object->getArbre()->getCaractPiedOther();
        $_arbre['caractTronc'] = $object->getArbre()->getCaractTronc();
        $_arbre['caractTroncMultiples'] = $object->getArbre()->getCaractTroncMultiples();

        $_arbre['etatSanCollet'] = $object->getArbre()->getEtatSanCollet();
        $_arbre['etatSanColletCavite'] = $object->getArbre()->getEtatSanColletCavite();

        // ETAT SAN COLLET CHMAPIGNONS
        $_arbre['etatSanColletChampignons'] = $this->_champignonService->setChampignons($object->getArbre()->getEtatSanColletChampignons());
        $_arbre['etatSanTronc'] = $object->getArbre()->getEtatSanTronc();
        $_arbre['etatSanTroncCavite'] = $object->getArbre()->getEtatSanTroncCavite();
        $_arbre['etatSanGeneral'] = $object->getArbre()->getEtatSanGeneral();
        $_arbre['etatSanGeneralOther'] = $object->getArbre()->getEtatSanGeneralOther();
        $_arbre['etatSanTroncCorpsEtranger'] = $object->getArbre()->getEtatSanTroncCorpsEtranger();

        // ETAT SAN CHAMPIGNONS
        $_arbre['etatSanTroncChampignons'] = $this->_champignonService->setChampignons($object->getArbre()->getEtatSanTroncChampignons());

        // ETAT SANITAIRE TRONC NUISIBLES A COMPLETER
        $etatSanTroncNuisibles = [];
        $_etatSanTroncNuisibles = [];

        if ($object->getArbre()->getEtatSanTroncNuisibles()) {
            foreach ($object->getArbre()->getEtatSanTroncNuisibles() as $etatSanTroncNuisibleObject) {

                $nuisible = $this->getDoctrine()->getRepository(Nuisible::class)->findOneBy(['id' => $etatSanTroncNuisibleObject]);
                if ($nuisible instanceof Nuisible) {
                    $_etatSanTroncNuisibles ['id'] = $nuisible->getId();
                    $_etatSanTroncNuisibles ['name'] = $nuisible->getName();
                    $etatSanTroncNuisibles [] = $_etatSanTroncNuisibles;
                }
            }
        }

        $_arbre['etatSanTroncNuisibles'] = $etatSanTroncNuisibles;

        $_arbre['etatSanHouppier'] = $object->getArbre()->getEtatSanHouppier();

        $_arbre['etatSanHouppierChampignons'] = $this->_champignonService->setChampignons($object->getArbre()->getEtatSanHouppierChampignons());

        // ETAT SAN HOUPPIER SANITAIRE
        $etatSanHouppierNuisibles = [];
        $_etatSanHouppierNuisibles = [];

        if ($object->getArbre()->getEtatSanHouppierNuisibles()) {

            foreach ($object->getArbre()->getEtatSanHouppierNuisibles() as $etatSanHouppierNuisibleObject) {

                $nuisible = $this->getDoctrine()->getRepository(Nuisible::class)->findOneBy(['id' => $etatSanHouppierNuisibleObject]);

                if ($nuisible instanceof Nuisible) {
                    $_etatSanHouppierNuisibles ['id'] = $nuisible->getId();
                    $_etatSanHouppierNuisibles ['name'] = $nuisible->getName();
                    $etatSanHouppierNuisibles [] = $_etatSanHouppierNuisibles;
                }
            }
        }
        $_arbre['etatSanHouppierNuisibles'] = $etatSanHouppierNuisibles;

        // Etat San hoppier other
        $_arbre['etatSanHouppierOther'] = $object->getArbre()->getEtatSanHouppierOther();
        $_arbre['etatSanColletOther'] = $object->getArbre()->getEtatSanColletOther();
        $_arbre['etatSanTroncOther'] = $object->getArbre()->getEtatSanTroncOther();

        $_arbre['risque'] = is_string($object->getArbre()->getRisque()) ? json_decode($object->getArbre()->getRisque()) : $object->getArbre()->getRisque();
        $_arbre['risqueGeneral'] = $object->getArbre()->getRisqueGeneral();
        $_arbre['critere'] = $object->getArbre()->getCritere();
        $_arbre['implantation'] = $object->getArbre()->getImplantation();
        $_arbre['domaine'] = $object->getArbre()->getdomaine();
        $_arbre['nuisance'] = $object->getArbre()->getNuisance();
        $_arbre['portArbre'] = $object->getArbre()->getPortArbre();
        $_arbre ['stadeDev'] = $object->getArbre()->getStadeDev();

        $nuisanceNuisibles = [];
        $_nuisanceNuisibles = [];

        if ($object->getArbre()->getNuisanceNuisibles()) {

            foreach ($object->getArbre()->getNuisanceNuisibles() as $nuisanceNuisibleObject) {

                $nuisible = $this->getDoctrine()->getRepository(Nuisible::class)->findOneBy(['id' => $nuisanceNuisibleObject]);

                if ($nuisible instanceof Nuisible) {
                    $_nuisanceNuisibles ['id'] = $nuisible->getId();
                    $_nuisanceNuisibles ['name'] = $nuisible->getName();
                    $nuisanceNuisibles [] = $_nuisanceNuisibles;
                }
            }
        }

        $_arbre['nuisanceNuisibles'] = $nuisanceNuisibles;

        $_arbre['proximite'] = $object->getArbre()->getProximite();
        $_arbre['proximiteOther'] = $object->getArbre()->getProximiteOther();
        $_arbre['proximiteWithDict'] = $object->getArbre()->getProximiteWithDict();
        $_arbre['tauxFreq'] = $object->getArbre()->getTauxFreq();
        $_arbre['typePassage'] = $object->getArbre()->getTypePassage();

        $_arbre['accessibilite'] = $object->getArbre()->getAccessibilite();
        $_arbre['accessibiliteOther'] = $object->getArbre()->getAccessibiliteOther();
        // END NEW FIELD
        // ESPECE
        $espece = $object->getArbre()->getEspece();
        $_espece['id'] = $espece->getId();
        $_espece['name'] = $espece->getName();
        $_espece['cultivar'] = $espece->getCultivar();
        $_espece['nomFr'] = $espece->getNomFr();
        $_espece['genre'] = $espece->getGenre();
        $_espece['categorie'] = $espece->getCategorie();
        $_espece['tarif'] = $espece->getTarif();
        $_espece['indiceEspece'] = $espece->getIndiceEspece();

        $_arbre['espece'] = $_espece;
        // IMAGE
        $_arbre['userEditedDateTravaux'] = $object->getArbre()->getUserEditedDateTravaux() ? $object->getArbre()->getUserEditedDateTravaux()->format('Y-m-d\TH:i:sO') : '';
        // FIN ESPECE

        $_arbre['varietyGrade'] = $object->getVarietyGrade() ?? $espece->getIndiceEspece();
        $_arbre['healthIndex'] = $object->getHealthIndex();
        $_arbre['aestheticIndex'] = $object->getAestheticIndex();
        $_arbre['locationIndex'] = $object->getLocationIndex();
        $_arbre['aestheticColumn'] = $object->getAestheticColumn();
        $_arbre['healthColumn'] = $object->getHealthColumn();

        if (!$object->getVarietyGrade() || !$object->getAestheticIndex() || !$object->getLocationIndex()) {
            $_arbre['beva'] = '';
        } else {
            $data = [
                "varietyGrade" => $object->getVarietyGrade(),
                "healthIndex" => $object->getHealthIndex(),
                "aestheticIndex" => $object->getAestheticIndex(),
                "locationIndex" => $object->getLocationIndex()
            ];
            $_arbre['beva'] = BevaService::createBeva($data, $object->getArbre());
        }

        // TRAVAUX
        $_arbre['abattage'] = $object->getArbre()->getAbattage();
        $_arbre['travauxCollet'] = $object->getArbre()->getTravauxCollet();
        $_arbre['travauxColletOther'] = $object->getArbre()->getTravauxColletOther();
        $_arbre['travauxTroncOther'] = $object->getArbre()->getTravauxTroncOther();
        $_arbre['travauxTronc'] = $object->getArbre()->getTravauxTronc();
        $_arbre['travauxTroncProtection'] = $object->getArbre()->getTravauxTroncProtection();
        $_arbre['travauxHouppier'] = $object->getArbre()->getTravauxHouppier();

        $_arbre['travauxHouppierMultiple'] = $object->getArbre()->getTravauxHouppierMultiple();
        $_arbre['travauxTroncMultiple'] = $object->getArbre()->getTravauxTroncMultiple();
        $_arbre['travauxColletMultiple'] = $object->getArbre()->getTravauxColletMultiple();

        $_arbre['travauxHouppierOther'] = $object->getArbre()->getTravauxHouppierOther();
        $_arbre['dateTravaux'] = $object->getArbre()->getDateTravaux();
        $_arbre['critereOther'] = $object->getArbre()->getCritereOther();
        $_arbre['risqueGeneralOther'] = $object->getArbre()->getRisqueGeneralOther();
        $_arbre['typePassageOther'] = $object->getArbre()->getTypePassageOther();

        $_arbre ['dateProVisite'] = $object->getArbre()->getDateProVisite();
        $_arbre ['etatSanTroncNuisiblesAutres'] = $object->getArbre()->getEtatSanTroncNuisiblesAutres();
        $_arbre ['etatSanHouppierNuisiblesAutres'] = $object->getArbre()->getEtatSanHouppierNuisiblesAutres();
        $_arbre ['statusTravaux'] = $object->getArbre()->getStatusTravaux();

        $_arbre['address'] = ($object->getArbre()->getAddress() == 'Unnamed Road') ? 'Indéfini' : $object->getArbre()->getAddress();
        $_arbre['ville'] = $object->getArbre()->getVille();
        $_arbre['pays'] = $object->getArbre()->getPays();

        $_arbre ['imgUrl'] = [
            'img1' => $object->getArbre()->getImg1(),
            'img2' => $object->getArbre()->getImg2(),
            'img3' => $object->getArbre()->getImg3()
        ];

        $_arbre['coord'] = MapService::serializeCoord($object->getArbre());

        // Champignons autres
        $_arbre['etatSanColletChampignonsAutres'] = $object->getArbre()->getEtatSanColletChampignonsAutres();
        $_arbre['etatSanTroncChampignonsAutres'] = $object->getArbre()->getEtatSanTroncChampignonsAutres();
        $_arbre['etatSanHouppierChampignonsAutres'] = $object->getArbre()->getEtatSanHouppierChampignonsAutres();

        return $_arbre;
    }

    /**
     * @param $addArbre
     * @return array
     */
    public function newArbre($addArbre): array
    {
        $arbre = new Arbre();
        // INFORMATIONS OBLIGATOIRES
        if (!isset($addArbre['coord']) || !isset($addArbre['espece']) || !isset($addArbre['diametre']) || !isset($addArbre['hauteur'])) {
            return [
                'message' => 'Informations obligatoires',
                'errorCode' => 300
            ];
        }

        $arbre->setDiametre($addArbre['diametre']);
        $arbre->setCodeSite($addArbre['codeSite'] ?? '');
        $arbre->setNumSujet($addArbre['numSujet'] ?? '');
        $arbre->setHauteur($addArbre['hauteur']);

        $arbre->setImg1($addArbre['img1'] ?? '');
        $arbre->setImg2($addArbre['img2'] ?? '');
        $arbre->setImg3($addArbre['img3'] ?? '');

        $arbre->setRisque(json_encode($addArbre['risque'] ?? ''));

        // Getting address && cordinate
        $addressFormatted = MapService::getAddress($addArbre['coord']['lat'], $addArbre['coord']['long']);
        $arbre->setAddress($addressFormatted['address']);
        $arbre->setVille($addressFormatted['ville']);
        $arbre->setPays($addressFormatted['pays']);
        $point = new Point($addArbre['coord']['lat'], $addArbre['coord']['long']);
        $arbre->setCoord($point);

        // set Espece
        /** @var Espece $espece */
        $espece = $this->getDoctrine()->getRepository(Espece::class)->findOneBy(['id' => $addArbre['espece']]);
        $arbre->setEspece($espece);
        $arbre->setCreatedAt(new \DateTime('now'));

        try {
            $this->getDoctrine()->getManager()->persist($arbre);
            $this->getDoctrine()->getManager()->flush();
            return [
                "message" => "Enregistrement effectué avec succès",
                "id" => $arbre->getId(),
                "errorCode" => 200
            ];
        } catch (\Doctrine\DBAL\Exception $e) {
            return [
                "message" => "Impossible d'enrégistrer l'arbre",
                "errorCode" => 300
            ];
        }
    }
}
