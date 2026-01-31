<?php

namespace App\Service\Notification;

use App\Entity\Notification;
use App\Entity\User;
use App\Service\RequestCheckerService;
use Doctrine\ORM\EntityManagerInterface;

class NotificationService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly RequestCheckerService $requestCheckerService
    ) {}

    public function createNotification(
        User $recipient,
        string $type,
        string $message,
        bool $isRead = false,
        ?\DateTimeImmutable $createdAt = null
    ): Notification {
        $notification = (new Notification())
            ->setRecipient($recipient)
            ->setType($type)
            ->setMessage($message)
            ->setIsRead($isRead)
            ->setCreatedAt($createdAt ?? new \DateTimeImmutable());

        $this->requestCheckerService->validateRequestDataByConstraints($notification);

        $this->entityManager->persist($notification);

        return $notification;
    }

    public function markAsRead(Notification $notification): void
    {
        $notification->setIsRead(true);

        $this->requestCheckerService->validateRequestDataByConstraints($notification);
    }

    public function markAsUnread(Notification $notification): void
    {
        $notification->setIsRead(false);

        $this->requestCheckerService->validateRequestDataByConstraints($notification);
    }

    public function updateNotification(Notification $notification, array $data): void
    {
        if (array_key_exists('isRead', $data)) {
            $notification->setIsRead((bool) $data['isRead']);
        }

        $this->requestCheckerService->validateRequestDataByConstraints($notification);
    }

    public function removeNotification(Notification $notification): void
    {
        $this->entityManager->remove($notification);
    }

    public function flush(): void
    {
        $this->entityManager->flush();
    }
}
