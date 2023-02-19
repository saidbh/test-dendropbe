<?php

namespace App\EventSubscriber;

use App\Entity\Groupe;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class EndDateSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['IsDateSubscriptionEvent', 33]
        ];
    }

    public function IsDateSubscriptionEvent(GetResponseForControllerResultEvent $event)
    {
        $entity = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();
//        dd($method);

        if ($entity instanceof Groupe) {
            return [];
        }
        return [];
    }

}
