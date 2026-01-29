<?php

namespace App\Service\Story;

use App\Entity\Story;
use App\Entity\User;
use App\Services\RequestCheckerService;
use Doctrine\ORM\EntityManagerInterface;

class StoryService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly RequestCheckerService $requestCheckerService
    ) {}

    public function createStory(
        User $author,
        string $mediaUrl,
        ?string $caption = null,
        ?\DateTimeImmutable $createdAt = null,
        ?\DateTimeImmutable $expiresAt = null
    ): Story {
        $createdAt = $createdAt ?? new \DateTimeImmutable();
        $expiresAt = $expiresAt ?? $createdAt->modify('+24 hours');

        $story = (new Story())
            ->setAuthor($author)
            ->setMediaUrl($mediaUrl)
            ->setCaption($caption)
            ->setCreatedAt($createdAt)
            ->setExpiresAt($expiresAt);

        $this->requestCheckerService->validateRequestDataByConstraints($story);

        $this->entityManager->persist($story);

        return $story;
    }

    public function updateStory(Story $story, array $data): void
    {
        if (array_key_exists('caption', $data)) {
            $value = $data['caption'];
            $story->setCaption($value === null ? null : (string) $value);
        }

        $this->requestCheckerService->validateRequestDataByConstraints($story);
    }

    public function removeStory(Story $story): void
    {
        $this->entityManager->remove($story);
    }

    public function flush(): void
    {
        $this->entityManager->flush();
    }
}
