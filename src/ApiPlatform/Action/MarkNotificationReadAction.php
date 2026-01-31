<?php

namespace App\ApiPlatform\Action;

use App\Entity\Notification;
use Doctrine\ORM\EntityManagerInterface;

final class MarkNotificationReadAction
{
    public function __construct(
        private readonly EntityManagerInterface $em
    ) {}

    public function __invoke(Notification $notification): Notification
    {
        $notification->setIsRead(true);

        $this->em->flush();

        return $notification;
    }
}
