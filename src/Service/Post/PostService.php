<?php

namespace App\Service\Post;

use App\Entity\Post;
use App\Entity\User;
use App\Service\RequestCheckerService;
use Doctrine\ORM\EntityManagerInterface;

class PostService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly RequestCheckerService $requestCheckerService
    ) {}

    public function createPost(
        string $title,
        string $content,
        User $author,
        ?\DateTimeImmutable $createdAt = null
    ): Post {
        $post = (new Post())
            ->setTitle($title)
            ->setContent($content)
            ->setAuthor($author)
            ->setCreatedAt($createdAt ?? new \DateTimeImmutable());

        $this->requestCheckerService->validateRequestDataByConstraints($post);

        $this->entityManager->persist($post);

        return $post;
    }

    public function updatePost(Post $post, array $data): void
    {
        if (array_key_exists('title', $data)) {
            $post->setTitle((string) $data['title']);
        }

        if (array_key_exists('content', $data)) {
            $post->setContent((string) $data['content']);
        }

        $this->requestCheckerService->validateRequestDataByConstraints($post);
    }

    public function removePost(Post $post): void
    {
        $this->entityManager->remove($post);
    }

    public function flush(): void
    {
        $this->entityManager->flush();
    }
}
