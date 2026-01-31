<?php

namespace App\EventSubscriber;

use App\Entity\Notification;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Events;
use Psr\Log\LoggerInterface;

final class NotificationUpdatedSubscriber implements EventSubscriber
{
    public function __construct(
        private readonly LoggerInterface $logger
    ) {}

    public function getSubscribedEvents(): array
    {
        return [Events::postUpdate];
    }

    public function postUpdate(PostUpdateEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof Notification) {
            return;
        }

        $em = $args->getObjectManager();
        $uow = $em->getUnitOfWork();
        $changeSet = $uow->getEntityChangeSet($entity);

        $isReadChanged = array_key_exists('isRead', $changeSet);

        $this->logger->info('Notification updated', [
            'notificationId' => $entity->getId(),
            'type' => $entity->getType(),
            'isRead' => $entity->isRead(),
            'isReadChanged' => $isReadChanged,
        ]);
    }
}
