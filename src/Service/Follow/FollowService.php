<?php

namespace App\Service\Follow;

use App\Entity\Follow;
use App\Entity\User;
use App\Service\RequestCheckerService;
use Doctrine\ORM\EntityManagerInterface;

class FollowService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly RequestCheckerService $requestCheckerService
    ) {}

    public function createFollow(
        User $follower,
        User $following,
        ?\DateTimeImmutable $createdAt = null
    ): Follow {
        $follow = (new Follow())
            ->setFollower($follower)
            ->setFollowing($following)
            ->setCreatedAt($createdAt ?? new \DateTimeImmutable());

        $this->requestCheckerService->validateRequestDataByConstraints($follow);

        $this->entityManager->persist($follow);

        return $follow;
    }

    public function removeFollow(Follow $follow): void
    {
        $this->entityManager->remove($follow);
    }

    public function flush(): void
    {
        $this->entityManager->flush();
    }
}
