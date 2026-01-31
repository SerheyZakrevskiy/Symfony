<?php

namespace App\EventSubscriber;

use App\Entity\Post;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Events;
use Psr\Log\LoggerInterface;

final class PostUpdatedSubscriber implements EventSubscriber
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

        if (!$entity instanceof Post) {
            return;
        }

        $em = $args->getObjectManager();
        $uow = $em->getUnitOfWork();
        $changeSet = $uow->getEntityChangeSet($entity);

        // Оставляем только интересные поля
        $filtered = [];
        foreach (['title', 'content', 'author', 'createdAt'] as $field) {
            if (array_key_exists($field, $changeSet)) {
                $filtered[$field] = [
                    'old' => is_object($changeSet[$field][0]) ? get_class($changeSet[$field][0]) : $changeSet[$field][0],
                    'new' => is_object($changeSet[$field][1]) ? get_class($changeSet[$field][1]) : $changeSet[$field][1],
                ];
            }
        }

        $this->logger->info('Post updated', [
            'postId' => $entity->getId(),
            'changes' => $filtered,
        ]);
    }
}
