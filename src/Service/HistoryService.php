<?php

namespace App\Service;

use App\Entity\History;
use App\Entity\HistoryDocs;
use App\Entity\Inventaire;
use App\Service\TokenService;
use App\Service\ImageService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;

class HistoryService
{
    private $entityManager;
    private $tokenService;
    private $ImageService;

    private $parameterBag;

    public function __construct(EntityManagerInterface $entityManager,TokenService $tokenService,ImageService $ImageService, ParameterBagInterface $parameterBag)
    {
        $this->entityManager = $entityManager;
        $this->tokenService = $tokenService;
        $this->ImageService = $ImageService;
        $this->parameterBag = $parameterBag;
    }
    public function getHistoryList($request)
    {
        try {
            $data = $this->tokenService->MiddlewareNormalUser($request->headers->get('Authorization'));
            if (!isset($data['user']) || !$data['user']) {
                return [
                    'data' => "unauthorized !",
                    'errorCode' => 401
                ];
            }
            $user = $data['user'];
            $data = $this->entityManager->getRepository(History::class)->findBy(['user'=> $user]);
            return [
                'data' => $data,
                'errorCode' => 200
            ];
        }catch (\Exception $exception)
        {
           return [
            'data' => 'Erreur serveur !',
            'errorCode' => 500
            ];
        }

    }

    public function addHistory(Inventaire $inventaire)
    {
        try {

            if ($inventaire->getArbre())
            {
            $arbre = $inventaire->getArbre();
            $history = new History();

            $history->setInventaire($inventaire);
            $history->setEspece($arbre->getEspece());
            $history->setDiametre($arbre->getDiametre());
            $history->setCreatedAt($arbre->getCreatedAt());
            $history->setUpdatedAt($arbre->getUpdatedAt());
            $history->setCoord($arbre->getCoord());
            $history->setUser($arbre->getUser());
            $history->setCodeSite($arbre->getCodeSite());
            $history->setNumSujet($arbre->getNumSujet());
            $history->setCritere($arbre->getCritere());
            $history->setImplantation($arbre->getImplantation());
            $history->setDomaine($arbre->getDomaine());
            $history->setNuisance($arbre->getNuisance());
            $history->setProximite($arbre->getProximite());
            $history->setTauxFreq($arbre->getTauxFreq());
            $history->setTypePassage($arbre->getTypePassage());
            $history->setAccessibilite($arbre->getAccessibilite());
            $history->setAbattage($arbre->getAbattage());
            $history->setTravauxCollet($arbre->getTravauxCollet());
            $history->setTravauxTronc($arbre->getTravauxTronc());
            $history->setTravauxHouppier($arbre->getTravauxHouppier());
            $history->setDateTravaux($arbre->getDateTravaux());
            $history->setDateProVisite($arbre->getDateProVisite());
            $history->setComProVisite($arbre->getComProVisite());
            $history->setCaractPied($arbre->getCaractPied());
            $history->setCaractTronc($arbre->getCaractTronc());
            $history->setHauteur($arbre->getHauteur());
            $history->setPortArbre($arbre->getPortArbre());
            $history->setStadeDev($arbre->getStadeDev());
            $history->setEtatSanCollet($arbre->getEtatSanCollet());
            $history->setEtatSanTronc($arbre->getEtatSanTronc());
            $history->setEtatSanHouppier($arbre->getEtatSanHouppier());
            $history->addNuisanceNuisibles($arbre->getNuisanceNuisibles());
            $history->setAddress($arbre->getAddress());
            $history->setComAccess($arbre->getComAccess());
            $history->setDict($arbre->getDict());
            $history->setRisque($arbre->getRisque());
            $history->setProximiteOther($arbre->getProximiteOther());
            $history->setAccessibiliteOther($arbre->getProximiteOther());
            $history->setCaractPiedOther($arbre->getCaractPiedOther());
            $history->setCaractTroncMultiples($arbre->getCaractTroncMultiples());
            $history->setEtatSanColletCavite($arbre->getEtatSanColletCavite());
            $history->setEtatSanColletChampignons($arbre->getEtatSanColletChampignons());
            $history->setEtatSanTroncCavite($arbre->getEtatSanTroncCavite());
            $history->setEtatSanTroncCorpsEtranger($arbre->getEtatSanTroncCorpsEtranger());
            $history->setEtatSanTroncChampignons($arbre->getEtatSanTroncChampignons());
            $history->setEtatSanTroncNuisibles($arbre->getEtatSanTroncNuisibles());
            $history->setEtatSanHouppierChampignons($arbre->getEtatSanHouppierChampignons());
            $history->setEtatSanHouppierNuisibles($arbre->getEtatSanHouppierNuisibles());
            $history->setRisqueGeneral($arbre->getRisqueGeneral());
            $history->setTravauxCommentaire($arbre->getTravauxCommentaire());
            $history->setCritereOther($arbre->getCritereOther());
            $history->setRisqueGeneralOther($arbre->getRisqueGeneralOther());
            $history->setTypePassageOther($arbre->getTypePassageOther());
            $history->setTypeIntervention($arbre->getTypeIntervention());
            $history->setEtatSanGeneral($arbre->getEtatSanGeneral());
            $history->setUserEditedDateTravaux($arbre->getUserEditedDateTravaux());
            $history->setTravauxTroncOther($arbre->getTravauxTroncOther());
            $history->setTravauxColletOther($arbre->getTravauxColletOther());
            $history->setTravauxHouppierOther($arbre->getTravauxHouppierOther());
            $history->setProximiteWithDict($arbre->getProximiteWithDict());
            $history->setVille($arbre->getVille());
            $history->setPays($arbre->getPays());
            $history->setTravauxTroncProtection($arbre->getTravauxTroncProtection());
            $history->setImg1($arbre->getImg1());
            $history->setImg2($arbre->getImg2());
            $history->setImg3($arbre->getImg3());
            $history->setEtatSanTroncNuisiblesAutres($arbre->getEtatSanTroncNuisiblesAutres());
            $history->setEtatSanHouppierNuisiblesAutres($arbre->getEtatSanHouppierNuisiblesAutres());
            $history->setTravauxColletMultiple($arbre->getTravauxColletMultiple());
            $history->setTravauxTroncMultiple($arbre->getTravauxTroncMultiple());
            $history->setTravauxHouppierMultiple($arbre->getTravauxHouppierMultiple());
            $history->setStatusTravaux($arbre->getStatusTravaux());
            $history->setEtatSanColletChampignonsAutres($arbre->getEtatSanColletChampignonsAutres());
            $history->setEtatSanTroncChampignonsAutres($arbre->getEtatSanTroncChampignonsAutres());
            $history->setEtatSanHouppierChampignonsAutres($arbre->getEtatSanHouppierChampignonsAutres());
            $history->setEtatSanColletOther($arbre->getEtatSanColletOther());
            $history->setEtatSanTroncOther($arbre->getEtatSanTroncOther());
            $history->setEtatSanHouppierOther($arbre->getEtatSanHouppierOther());
            $history->setEtatSanGeneralOther($arbre->getEtatSanGeneralOther());

            $this->entityManager->persist($history);
            $this->entityManager->flush();
            }

            return [
                'data' => 'History added sucessfully !',
                'errorCode' => 200
            ];

        }catch (\Exception $exception)
        {
            return [
                'data' => 'Erreur serveur !',
                'errorCode' => 500
            ];
        }
    }

    public function getHistoryPerInventaire($request)
    {
        try {
            $inputs = $request->query->all();
            $data = $this->tokenService->MiddlewareNormalUser($request->headers->get('Authorization'));
            if (!isset($data['user']) || !$data['user']) {
                return [
                    'data' => "unauthorized !",
                    'errorCode' => 401
                ];
            }
            $user = $data['user'];
            if (isset($inputs['inventaireId']) && !is_null($inputs['inventaireId']))
            {
                $listperinventaire = $this->entityManager->getRepository(History::class)->findBy(['user' => $user,'inventaire' => $inputs['inventaireId'],array('userEditedDateTravaux' => 'DESC')]);
                return [
                    'data' => $listperinventaire,
                    'errorCode' => 200
                ];
            }else
            {
                return [
                    'data' => 'Erreur params !',
                    'errorCode' => 400
                ];
            }

        }catch (\Exception $exception)
        {
            return [
                'data' => 'Erreur serveur !',
                'errorCode' => 500
            ];
        }

    }

    public function uploadHistoryDocs($request)
    {
        try {
            $historyId = $request->request->get('historyid');
            $files = $request->files->get('files');
            $data = $this->tokenService->MiddlewareNormalUser($request->headers->get('Authorization'));
            if (!isset($data['user']) || !$data['user']) {
                return [
                    'data' => "unauthorized !",
                    'errorCode' => 401
                ];
            }
            $user = $data['user'];
            if (isset($files) && !is_null($files) && isset($historyId) && !is_null($historyId))
            {
                $history = $this->entityManager->getRepository(History::class)->findOneBy(['user' => $user,'id' => $historyId]);
                foreach ($files as $file)
                {
                    $filename = $this->ImageService->addImage($file,$this->parameterBag->get('history_files'),false);
                    if (!isset($filename['data']))
                    {
                        $docs = new HistoryDocs();
                        $docs->setPath($this->parameterBag->get('history_files').$filename);
                        $docs->setHistory($history);
                        $this->entityManager->persist($docs);
                        $this->entityManager->flush();

                    }
                }
                return [
                    'data' => "Image upload avec succès !",
                    'errorCode' => 200
                ];
            }else
            {
                return [
                    'data' => 'Erreur params !',
                    'errorCode' => 400
                ];
            }

        }catch (\Exception $exception)
        {
            return [
                'data' => 'Erreur serveur !',
                'errorCode' => 500
            ];
        }

    }

}