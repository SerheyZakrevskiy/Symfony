<?php

namespace App\Service\FriendRequest;

use App\Entity\FriendRequest;
use App\Entity\User;
use App\Service\RequestCheckerService;
use Doctrine\ORM\EntityManagerInterface;

class FriendRequestService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly RequestCheckerService $requestCheckerService
    ) {}

    public function createFriendRequest(
        User $fromUser,
        User $toUser,
        ?\DateTimeImmutable $createdAt = null
    ): FriendRequest {
        $friendRequest = (new FriendRequest())
            ->setFromUser($fromUser)
            ->setToUser($toUser)
            ->setStatus(FriendRequest::STATUS_PENDING)
            ->setCreatedAt($createdAt ?? new \DateTimeImmutable());

        $this->requestCheckerService->validateRequestDataByConstraints($friendRequest);

        $this->entityManager->persist($friendRequest);

        return $friendRequest;
    }

    public function accept(FriendRequest $friendRequest): void
    {
        $friendRequest->setStatus(FriendRequest::STATUS_ACCEPTED);

        $this->requestCheckerService->validateRequestDataByConstraints($friendRequest);
    }

    public function reject(FriendRequest $friendRequest): void
    {
        $friendRequest->setStatus(FriendRequest::STATUS_REJECTED);

        $this->requestCheckerService->validateRequestDataByConstraints($friendRequest);
    }

    public function updateStatus(FriendRequest $friendRequest, string $status): void
    {
        $allowed = [
            FriendRequest::STATUS_PENDING,
            FriendRequest::STATUS_ACCEPTED,
            FriendRequest::STATUS_REJECTED,
        ];

        if (!in_array($status, $allowed, true)) {

            throw new \InvalidArgumentException('Invalid friend request status');
        }

        $friendRequest->setStatus($status);

        $this->requestCheckerService->validateRequestDataByConstraints($friendRequest);
    }

    public function removeFriendRequest(FriendRequest $friendRequest): void
    {
        $this->entityManager->remove($friendRequest);
    }

    public function flush(): void
    {
        $this->entityManager->flush();
    }
}
