<?php

namespace App\Service;

use App\Entity\Arbre;
use App\Entity\Epaysage;
use App\Entity\Essence;
use App\Entity\Inventaire;
use App\Entity\Travaux;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Serializer;

class TravauxService extends AbstractController
{
    const ABBATAGE_CRITERIA = ['abattage-simple', 'abattage-en-vue-de-remplacement'];

    public function addTravaux(array $data, ?array $etatSanGnl = [])
    {
        if (!self::isTravauxOrVisite($data)) {
            return ['NO_TRAVAUX' => false];
        }

        if (isset($data['essenceId']) && $data['essenceId']) { // cas d'une essence
            $essence = $this->getDoctrine()->getRepository(Essence::class)->findOneBy(['id' => $data['essenceId']]);
            if ($essence instanceof Essence) {
                $travaux = $this->setTravauxEssence($data, $etatSanGnl);
            } else {
                return [
                    'data' => [
                        'message' => 'essence not found'
                    ],
                    'statusCode' => Response::HTTP_NOT_FOUND
                ];
            }
        } else if (isset($data['arbreId']) && $data['arbreId']) { // cas d'un arbre
            $arbre = $this->getDoctrine()->getRepository(Arbre::class)->findOneBy(['id' => $data['arbreId']]);
            if ($arbre instanceof Arbre) {
                $travaux = $this->setTravauxArbre($data, $etatSanGnl);
            } else {
                return [
                    'data' => [
                        'message' => 'arbre not found'
                    ],
                    'statusCode' => Response::HTTP_NOT_FOUND
                ];
            }
        } else {
            return [
                'data' => [
                    'message' => 'Impossible to add travaux',
                    'errorCodeMessage' => 'NO_CORRECT_ID'
                ],
                'statusCode' => Response::HTTP_BAD_REQUEST
            ];
        }
        // check if prochaine visite
        $travaux->setCreatedAt(new \DateTime('now'));

        try {
            $this->getDoctrine()->getRepository(Travaux::class)->add($travaux);
            return [
                'data' => [
                    'isDone' => true,
                ],
                'statusCode' => Response::HTTP_OK
            ];
        } catch (\Exception $e) {
            return [
                'data' => [
                    'message' => 'opration impossible'
                ],
                'statusCode' => Response::HTTP_INTERNAL_SERVER_ERROR];
        }
    }

    public function setTravauxEssence(array $data, $etatSanGnl): array
    {
        /** @var Serializer */
        $serializer = $this->get('serializer');
        $dataEssence = [
            'essenceId' => $data['essenceId'],
            'epaysageId' => $data['essenceId'],
            'inventaireId' => $data['epaysageId'],

            'type' => $data['type'],
            'travaux' => isset($data['travaux']) ? $data['travaux'] : [],
            'travauxOther' => $data['travauxOther'],
            'travauxSoin' => $data['travauxSoin'],
            'travauxProtection' => $data['travauxProtection'],
            'nbreSujetConcerne' => $data['nbreSujetConcerne'],

            'dateTravaux' => $data['dateTravaux'],
            'dateProVisite' => $data['dateProVisite'],
            'userEditedDateTravaux' => $data['userEditedDateTravaux']
        ];

        if ($this->setProchaineViste($data['type'], $data['travaux']) || InventaireService::isExamenComplementaire($etatSanGnl)) {
            $data['dateProVisite'] = isset($data['dateTravaux']) ? $data['dateTravaux'] : '';
            $data['travaux'] = null;
        } else {
            $data['dateProVisite'] = null;
        }
        return $serializer->denormalize($dataEssence, Travaux::class, 'json');
    }

    public function setTravauxArbre(array $data, array $etatSanGnl)
    {
        /** @var Serializer */
        $serializer = $this->get('serializer');
        $dataArbre = [
            'arbreId' => $data['arbreId'],
            'inventaireId' => $data['inventaireId'],
            'type' => 'ARBRE',
            'abattage' => $data['abattage'],
            'travauxColletMultiple' => isset($data['travauxColletMultiple']) ? $data['travauxColletMultiple'] : [],
            'travauxTroncMultiple' => isset($data['travauxTroncMultiple']) ? $data['travauxTroncMultiple'] : [],
            'travauxHouppierMultiple' => isset($data['travauxHouppierMultiple']) ? $data['travauxHouppierMultiple'] : [],
            'travauxCommentaire' => $data['travauxCommentaire'],
            'travauxHouppierOther' => $data['travauxHouppierOther'],
            'travauxColletOther' => $data['travauxColletOther'],
            'travauxTroncOther' => $data['travauxTroncOther'],
            'travauxTroncProtection' => $data['travauxTroncProtection'],

            'dateTravaux' => $data['dateTravaux'],
            'dateProVisite' => $data['dateProVisite'],
            'userEditedDateTravaux' => $data['userEditedDateTravaux']
        ];

        if (($this->setProchaineViste($data['type'], $data['travauxColletMultiple']) &&
                $this->setProchaineViste($data['type'], $data['travauxTroncMultiple']) &&
                $this->setProchaineViste($data['type'], $data['travauxHouppierMultiple'])) || InventaireService::isExamenComplementaire($etatSanGnl)) {
            $data['dateProVisite'] = isset($data['dateTravaux']) ? $data['dateTravaux'] : '';
            $data['dateTravaux'] = null;
        } else {
            $data['dateProVisite'] = null;
        }
        return $serializer->denormalize($dataArbre, Travaux::class, 'json');
    }

    public function setProchaineViste(string $type, array $travaux)
    {
        // Check if prochaine visite
        if (strtoupper($type) != 'ARBRE') {
            $data = array_filter($travaux, function ($e) {
                return $e === 'aucun-travaux';
            });
        } else {
            $data = array_filter($travaux, function ($e) {
                return $e === 'aucun-travaux';
            });
        }
        return count($data) >= 1 || count($travaux) == 0;
    }

    public static function isTravauxOrVisite(array $data)
    {
        return (bool) (isset($data['userEditedDateTravaux']) && $data['userEditedDateTravaux']);
    }

    public static function setAbattageArbre(Arbre $arbre): Arbre {
        if($arbre->getAbattage()) {
            $arbre->setTravauxColletMultiple([]);
            $arbre->setTravauxTroncMultiple([]);
            $arbre->setTravauxHouppierMultiple([]);
        }
        return $arbre;
    }

    /**
     * @param Inventaire $inventaire
     */
    public function validTravauxInventory(Inventaire $inventaire): void {
        $em = $this->getDoctrine()->getManager();
        if ($inventaire->getArbre()) {
            $object = $this->getDoctrine()->getRepository(Arbre::class)->findOneBy(["id" => $inventaire->getArbre()->getId()]);
            $object->setStatusTravaux(true);
            $em->persist($object);
        } else {
            /** @var Epaysage $object */
            $object = $this->getDoctrine()->getRepository(Epaysage::class)->findOneBy(["id" => $inventaire->getEpaysage()->getId()]);
            foreach ($object->getEssence() as $essence) {
                $essence->setStatusTravaux(true);
                $em->persist($essence);
                $em->flush();
            }
            $em->persist($object);
        }
        $inventaire->setUpdatedAt(new \DateTime('now'));
        $em->persist($inventaire);
        $em->flush();
    }
}
