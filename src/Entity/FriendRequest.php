<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post as ApiPost;
use ApiPlatform\Metadata\Put;
use App\Repository\FriendRequestRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: FriendRequestRepository::class)]
#[ORM\Table(name: 'friend_request')]
#[UniqueEntity(fields: ['fromUser', 'toUser'], message: 'Friend request already exists')]
#[ApiResource(
    operations: [
        new Get(normalizationContext: ['groups' => ['friend_request:read']]),
        new GetCollection(normalizationContext: ['groups' => ['friend_request:read']]),
        new ApiPost(
            denormalizationContext: ['groups' => ['friend_request:write']],
            normalizationContext: ['groups' => ['friend_request:read']]
        ),
        new Put(
            denormalizationContext: ['groups' => ['friend_request:write_update']],
            normalizationContext: ['groups' => ['friend_request:read']]
        ),
        new Patch(
            denormalizationContext: ['groups' => ['friend_request:write_update']],
            normalizationContext: ['groups' => ['friend_request:read']]
        ),
        new Delete(),
    ],
    paginationEnabled: true,
    paginationItemsPerPage: 10
)]
#[ApiFilter(SearchFilter::class, properties: [
    'fromUser' => 'exact',
    'toUser' => 'exact',
    'status' => 'exact',
])]
#[ApiFilter(DateFilter::class, properties: ['createdAt'])]
#[ApiFilter(OrderFilter::class, properties: ['id', 'createdAt'], arguments: ['orderParameterName' => 'order'])]
class FriendRequest
{
    public const STATUS_PENDING  = 'pending';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['friend_request:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull]
    #[Groups(['friend_request:read', 'friend_request:write'])]
    private ?User $fromUser = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull]
    #[Groups(['friend_request:read', 'friend_request:write'])]
    private ?User $toUser = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: [
        self::STATUS_PENDING,
        self::STATUS_ACCEPTED,
        self::STATUS_REJECTED
    ])]
    #[Groups(['friend_request:read', 'friend_request:write_update'])]
    private string $status = self::STATUS_PENDING;

    #[ORM\Column]
    #[Assert\NotNull]
    #[Assert\Type(\DateTimeImmutable::class)]
    #[Groups(['friend_request:read'])]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->status = self::STATUS_PENDING;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFromUser(): ?User
    {
        return $this->fromUser;
    }

    public function setFromUser(?User $fromUser): static
    {
        $this->fromUser = $fromUser;
        return $this;
    }

    public function getToUser(): ?User
    {
        return $this->toUser;
    }

    public function setToUser(?User $toUser): static
    {
        $this->toUser = $toUser;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
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

    #[Assert\Callback]
    public function validateUsers(Assert\ExecutionContextInterface $context): void
    {
        if ($this->fromUser !== null && $this->toUser !== null && $this->fromUser === $this->toUser) {
            $context
                ->buildViolation('User cannot send friend request to himself')
                ->atPath('toUser')
                ->addViolation();
        }
    }
}
