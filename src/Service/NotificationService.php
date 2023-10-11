<?php

namespace App\Service;

use App\Entity\Notification;
use App\Message\NotificationMessage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\Serializer;

class NotificationService extends AbstractController
{
    private $_tokenService;
    private $_bus;

    public function __construct(TokenService $tokenService, MessageBusInterface $bus)
    {
        $this->_tokenService = $tokenService;
        $this->_bus = $bus;
    }

    public function getNotification(Request $request): array
    {
        $result = $this->_tokenService->MiddlewareDendroUser($request->headers->get('Authorization'));
        if (!isset($result['user']) || !$result['user']) {
            return $result;
        }
        $notifs = $this->getDoctrine()->getRepository(Notification::class)->findAll();

        $data = array_map(function (Notification $notif) {
            return $this->getSerializerNotif($notif);
        }, $notifs);

        return ['data' => $data, 'statusCode' => Response::HTTP_OK];
    }

    public function changeStatusNotif(Request $request, Notification $notif)
    {
        $result = $this->_tokenService->MiddlewareDendroUser($request->headers->get('Authorization'));

        if (!isset($result['user']) || !$result['user']) {
            return $result;
        }

        /** @var Serializer $serializer */
        $serializer = $this->get('serializer');
        $data = $serializer->decode($request->getContent(), 'json');

        if (!isset($data['status']) || !$data['status']) {
            return [
                'data' => ['message' => 'Information obligatoire'],
                'statusCode' => Response::HTTP_BAD_REQUEST
            ];
        }
        // To update $notif
        $notif->setStatut($data['status']);
        $notif->setUpdatedAt(new \DateTime('now'));

        try {
            $em = $this->getDoctrine()->getManager();
            $em->persist($notif);
            $em->flush();
            return ['data' => Response::HTTP_OK];
        } catch (\Exception $e) {
            return ['data' => '', 'statusCode' => Response::HTTP_BAD_REQUEST];
        }
    }

    public function add(Request $request)
    {
        $result = $this->_tokenService->MiddlewareDendroUser($request->headers->get('Authorization'));

        if (!isset($result['user']) || !$result['user']) {
            return $result;
        }

        /** @var Serializer $serializer */
        $serializer = $this->get('serializer');
        $data = $serializer->decode($request->getContent(), 'json');

        return $this->_bus->dispatch(new NotificationMessage($data));
    }

    public function deleteNotif(Notification $notif)
    {
        try {
            $em = $this->getDoctrine()->getManager();
            $em->persist($notif);
            $em->flush();
            return ['data' => 'notification supprimé avec succès', Response::HTTP_OK];
        } catch (\Exception $e) {
            return ['data' => 'Un problème est survenu', Response::HTTP_BAD_REQUEST];
        }
    }

    public function getSerializerNotif(Notification $notif)
    {
        /** @var Serializer $serializer */
        $serializer = $this->get('serializer');
        return $serializer->normalize($notif, 'json', ['groups' => 'default']);
    }
}
