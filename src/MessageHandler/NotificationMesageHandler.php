<?php

namespace App\MessageHandler;

use App\Entity\Groupe;
use App\Entity\Notification;
use App\Message\NotificationMessage;
use App\Service\NotificationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class NotificationMesageHandler extends AbstractController
{
    private $_service;

    public function __construct(NotificationService $service)
    {
        $this->_service = $service;
    }

    public function __invoke(NotificationMessage $notifMessage)
    {
        if (is_numeric($notifMessage->groupeId())) {
            $groupe = $this->getDoctrine()->getRepository(Groupe::class)->findOneBy(['id' => $notifMessage->groupeId()]);
            if (!$groupe instanceof Groupe) {
                return [
                    'data' => ['message' => 'Groupe non défini', 'error' => Response::HTTP_BAD_REQUEST],
                    'statusCode' => Response::HTTP_OK
                ];
            }
        }
        $notif = new Notification();

        $notif->setStatut($notifMessage->status());
        $notif->setType($notifMessage->type());
        $notif->setGroupeId($groupe->getId());
        $notif->setCreatedAt(new \DateTime('now'));

        try {
            $em = $this->getDoctrine()->getManager();
            $em->persist($notif);
            $em->flush();
            return [
                'data' => ['message' => 'Notification ajoutée avec succès'],
                'statusCode' => Response::HTTP_OK
            ];
        } catch (\Exception $e) {
            return [
                'data' => ['message' => 'Impossible to add forfait', 'error' => Response::HTTP_BAD_REQUEST],
                'statusCode' => Response::HTTP_BAD_REQUEST
            ];
        }
    }

    private function fetchObjectToArray(NotificationMessage $notif)
    {

    }

}
