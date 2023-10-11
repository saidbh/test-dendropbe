<?php

namespace App\Controller;

use App\Entity\Notification;
use App\Service\NotificationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/notifications")
 */
class NotificationController extends AbstractController
{
    private $_notifService;

    public function __construct(NotificationService $service)
    {
        $this->_notifService = $service;
    }

    /**
     * @Route("", name="get_all_notification", methods="GET")
     */
    public function getAll(Request $request): JsonResponse
    {
        $data = $this->_notifService->getNotification($request);
        return $this->json($data['data'], $data['statusCode']);
    }

    /**
     * @Route("/{id}", name="get_notification", methods="PATCH")
     */
    public function changeStatusNotif(Request $request, Notification $notif): JsonResponse
    {
        $data = $this->_notifService->changeStatusNotif($request, $notif);
        return $this->json($data['data'], $data['statusCode']);
    }

    /**
     * @Route("", name="add_notif", methods="POST")
     */
    public function add(Request $request): JsonResponse
    {
        $data = $this->_notifService->add($request);
        return $this->json($data['data'], $data['statusCode']);
    }
}
