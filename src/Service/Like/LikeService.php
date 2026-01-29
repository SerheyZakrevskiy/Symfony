<?php

namespace App\Service\Like;

use App\Entity\Like;
use App\Entity\Post;
use App\Entity\User;
use App\Service\RequestCheckerService;
use Doctrine\ORM\EntityManagerInterface;

class LikeService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly RequestCheckerService $requestCheckerService
    ) {}

    public function createLike(
        User $likedBy,
        Post $post,
        ?\DateTimeImmutable $createdAt = null
    ): Like {
        $like = (new Like())
            ->setLikedBy($likedBy)
            ->setPost($post)
            ->setCreatedAt($createdAt ?? new \DateTimeImmutable());

        $this->requestCheckerService->validateRequestDataByConstraints($like);

        $this->entityManager->persist($like);

        return $like;
    }

    public function removeLike(Like $like): void
    {
        $this->entityManager->remove($like);
    }

    public function flush(): void
    {
        $this->entityManager->flush();
    }
}
