<?php

namespace App\Service\Message;

use App\Entity\Message;
use App\Entity\User;
use App\Service\RequestCheckerService;
use Doctrine\ORM\EntityManagerInterface;

class MessageService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly RequestCheckerService $requestCheckerService
    ) {}

    public function createMessage(
        User $sender,
        User $receiver,
        string $content,
        ?\DateTimeImmutable $createdAt = null
    ): Message {
        $message = (new Message())
            ->setSender($sender)
            ->setReceiver($receiver)
            ->setContent($content)
            ->setCreatedAt($createdAt ?? new \DateTimeImmutable());

        $this->requestCheckerService->validateRequestDataByConstraints($message);

        $this->entityManager->persist($message);

        return $message;
    }

    public function removeMessage(Message $message): void
    {
        $this->entityManager->remove($message);
    }

    public function flush(): void
    {
        $this->entityManager->flush();
    }
}
