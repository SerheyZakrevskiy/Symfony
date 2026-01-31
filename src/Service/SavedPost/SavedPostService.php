<?php

namespace App\Service\SavedPost;

use App\Entity\SavedPost;
use App\Entity\Post;
use App\Entity\User;
use App\Service\RequestCheckerService;
use Doctrine\ORM\EntityManagerInterface;

class SavedPostService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly RequestCheckerService $requestCheckerService
    ) {}

    public function createSavedPost(
        User $author,
        Post $post,
        ?\DateTimeImmutable $savedAt = null
    ): SavedPost {
        $savedPost = (new SavedPost())
            ->setAuthor($author)
            ->setPost($post)
            ->setSavedAt($savedAt ?? new \DateTimeImmutable());

        $this->requestCheckerService->validateRequestDataByConstraints($savedPost);

        $this->entityManager->persist($savedPost);

        return $savedPost;
    }

    public function removeSavedPost(SavedPost $savedPost): void
    {
        $this->entityManager->remove($savedPost);
    }

    public function flush(): void
    {
        $this->entityManager->flush();
    }
}
