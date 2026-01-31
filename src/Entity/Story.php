<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post as ApiPost;
use ApiPlatform\Metadata\Put;
use App\Repository\StoryRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: StoryRepository::class)]
#[ApiResource(
    operations: [
        new Get(normalizationContext: ['groups' => ['story:read']]),
        new GetCollection(normalizationContext: ['groups' => ['story:read']]),
        new ApiPost(
            denormalizationContext: ['groups' => ['story:write']],
            normalizationContext: ['groups' => ['story:read']]
        ),
        new Put(
            denormalizationContext: ['groups' => ['story:write']],
            normalizationContext: ['groups' => ['story:read']]
        ),
        new Patch(
            denormalizationContext: ['groups' => ['story:write']],
            normalizationContext: ['groups' => ['story:read']]
        ),
        new Delete(),
    ],
    paginationEnabled: true,
    paginationItemsPerPage: 10
)]
class Story
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['story:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull]
    #[Groups(['story:read', 'story:write'])]
    private ?User $author = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[Assert\Url]
    #[Groups(['story:read', 'story:write'])]
    private string $mediaUrl = '';

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    #[Groups(['story:read', 'story:write'])]
    private ?string $caption = null;

    #[ORM\Column]
    #[Assert\NotNull]
    #[Assert\Type(\DateTimeImmutable::class)]
    #[Groups(['story:read'])]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    #[Assert\NotNull]
    #[Assert\Type(\DateTimeImmutable::class)]
    #[Groups(['story:read', 'story:write'])]
    private \DateTimeImmutable $expiresAt;

    public function __construct()
    {
        $now = new \DateTimeImmutable();
        $this->createdAt = $now;
        $this->expiresAt = $now->modify('+24 hours');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): static
    {
        $this->author = $author;
        return $this;
    }

    public function getMediaUrl(): string
    {
        return $this->mediaUrl;
    }

    public function setMediaUrl(string $mediaUrl): static
    {
        $this->mediaUrl = $mediaUrl;
        return $this;
    }

    public function getCaption(): ?string
    {
        return $this->caption;
    }

    public function setCaption(?string $caption): static
    {
        $this->caption = $caption;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getExpiresAt(): \DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(\DateTimeImmutable $expiresAt): static
    {
        $this->expiresAt = $expiresAt;
        return $this;
    }

    #[Assert\Callback]
    public function validateExpiration(Assert\ExecutionContextInterface $context): void
    {
        if ($this->expiresAt <= $this->createdAt) {
            $context
                ->buildViolation('Story expiration date must be later than creation date')
                ->atPath('expiresAt')
                ->addViolation();
        }
    }
}
