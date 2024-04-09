<?php

namespace App\Service;

use App\Entity\Arbre;
use App\Entity\History;
use App\Entity\HistoryDocs;
use App\Entity\Inventaire;
use App\Service\TokenService;
use App\Service\ImageService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class HistoryService
{
    private $entityManager;
    private $tokenService;
    private $ImageService;
    private $parameterBag;
    private $serializer;

    public function __construct(SerializerInterface $serializer,EntityManagerInterface $entityManager,TokenService $tokenService,ImageService $ImageService, ParameterBagInterface $parameterBag)
    {
        $this->entityManager = $entityManager;
        $this->tokenService = $tokenService;
        $this->ImageService = $ImageService;
        $this->parameterBag = $parameterBag;
        $this->serializer = $serializer;
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
            'data' => $exception->getMessage(),
            'errorCode' => 500
            ];
        }

    }

    public function addHistory(Inventaire $inventaire,Arbre $arbre)
    {
        try {

            if ($inventaire->getArbre())
            {
            $arbreArray = $this->entityManager->getRepository(Arbre::class)->getNotProxyArbre($arbre);
            $oldhistory = new History();
                foreach ($arbreArray as $propertyName => $propertyValue) {
                    if (property_exists(History::class, $propertyName)) {
                        $setterMethod = 'set' . ucfirst($propertyName);
                        if (method_exists($oldhistory, $setterMethod)) {
                            if ($propertyValue != null && $propertyValue != 'null') {
                                $oldhistory->$setterMethod($propertyValue);
                            }
                        }
                    }
                }
            $oldhistory->setCreatedAt(new \DateTime('now'));
            $oldhistory->setEspece($arbre->getEspece());
            $oldhistory->setInventaire($inventaire);
            $this->entityManager->persist($oldhistory);
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
            if (isset($inputs['inventaireId']) && !is_null($inputs['inventaireId']))
            {
                $workHistory = $this->entityManager->getRepository(History::class)->findBy([], ['createdAt' => 'DESC'], 3, 0);
                $listperinventaire = $this->entityManager->getRepository(History::class)->findBy(['inventaire' => $inputs['inventaireId'],array('createdAt' => 'DESC')]);
                return [
                    'data' => [$listperinventaire,$workHistory],
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
                'data' => $exception->getMessage(),
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

    public function getPropertyType(string $className, string $propertyName): ?string {
        $reflection = new ReflectionClass($className);
        $property = $reflection->getProperty($propertyName);
        $type = $property->getType();
        return $type ? $type->getName() : null;
    }

}