<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post as ApiPost;
use ApiPlatform\Metadata\Put;
use App\Repository\NotificationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: NotificationRepository::class)]
#[ApiResource(
    operations: [
        new Get(normalizationContext: ['groups' => ['notification:read']]),
        new GetCollection(normalizationContext: ['groups' => ['notification:read']]),
        new ApiPost(
            denormalizationContext: ['groups' => ['notification:write']],
            normalizationContext: ['groups' => ['notification:read']]
        ),
        new Put(
            denormalizationContext: ['groups' => ['notification:write']],
            normalizationContext: ['groups' => ['notification:read']]
        ),
        new Patch(
            denormalizationContext: ['groups' => ['notification:write']],
            normalizationContext: ['groups' => ['notification:read']]
        ),
        new Delete(),
    ],
    paginationEnabled: true,
    paginationItemsPerPage: 10
)]
class Notification
{
    public const TYPE_LIKE = 'like';
    public const TYPE_COMMENT = 'comment';
    public const TYPE_FOLLOW = 'follow';
    public const TYPE_MESSAGE = 'message';
    public const TYPE_FRIEND_REQUEST = 'friend_request';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['notification:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'notifications')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull]
    #[Groups(['notification:read', 'notification:write'])]
    private ?User $recipient = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: [
        self::TYPE_LIKE,
        self::TYPE_COMMENT,
        self::TYPE_FOLLOW,
        self::TYPE_MESSAGE,
        self::TYPE_FRIEND_REQUEST,
    ])]
    #[Groups(['notification:read', 'notification:write'])]
    private string $type = self::TYPE_LIKE;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    #[Assert\Length(
        min: 1,
        max: 3000,
        minMessage: 'Notification message cannot be empty',
        maxMessage: 'Notification message cannot be longer than {{ limit }} characters'
    )]
    #[Groups(['notification:read', 'notification:write'])]
    private string $message = '';

    #[ORM\Column]
    #[Assert\NotNull]
    #[Assert\Type('bool')]
    #[Groups(['notification:read', 'notification:write'])]
    private bool $isRead = false;

    #[ORM\Column]
    #[Assert\NotNull]
    #[Assert\Type(\DateTimeImmutable::class)]
    #[Groups(['notification:read'])]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRecipient(): ?User
    {
        return $this->recipient;
    }

    public function setRecipient(?User $recipient): static
    {
        $this->recipient = $recipient;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;
        return $this;
    }

    public function isRead(): bool
    {
        return $this->isRead;
    }

    public function setIsRead(bool $isRead): static
    {
        $this->isRead = $isRead;
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
}
