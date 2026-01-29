<?php

namespace App\Service\Comment;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\User;
use App\Service\RequestCheckerService;
use Doctrine\ORM\EntityManagerInterface;

class CommentService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly RequestCheckerService $requestCheckerService
    ) {}

    public function createComment(
        string $content,
        User $author,
        Post $post,
        ?\DateTimeImmutable $createdAt = null
    ): Comment {
        $comment = (new Comment())
            ->setContent($content)
            ->setAuthor($author)
            ->setPost($post)
            ->setCreatedAt($createdAt ?? new \DateTimeImmutable());

        $this->requestCheckerService->validateRequestDataByConstraints($comment);

        $this->entityManager->persist($comment);

        return $comment;
    }

    public function updateComment(Comment $comment, array $data): void
    {
        $allowed = ['content'];

        foreach ($data as $key => $value) {
            if (!in_array($key, $allowed, true)) {
                continue;
            }

            if ($key === 'content') {
                $comment->setContent((string) $value);
            }
        }

        $this->requestCheckerService->validateRequestDataByConstraints($comment);
    }

    public function removeComment(Comment $comment): void
    {
        $this->entityManager->remove($comment);
    }

    public function flush(): void
    {
        $this->entityManager->flush();
    }
}
