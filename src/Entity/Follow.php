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
use App\Repository\FollowRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: FollowRepository::class)]
#[ORM\Table(name: 'follow')]
#[UniqueEntity(fields: ['follower', 'following'], message: 'This follow relationship already exists')]
#[ApiResource(
    operations: [
        new Get(normalizationContext: ['groups' => ['follow:read']]),
        new GetCollection(normalizationContext: ['groups' => ['follow:read']]),
        new ApiPost(
            denormalizationContext: ['groups' => ['follow:write']],
            normalizationContext: ['groups' => ['follow:read']]
        ),
        new Put(
            denormalizationContext: ['groups' => ['follow:write']],
            normalizationContext: ['groups' => ['follow:read']]
        ),
        new Patch(
            denormalizationContext: ['groups' => ['follow:write']],
            normalizationContext: ['groups' => ['follow:read']]
        ),
        new Delete(),
    ],
    paginationEnabled: true,
    paginationItemsPerPage: 10
)]
#[ApiFilter(SearchFilter::class, properties: [
    'follower' => 'exact',
    'following' => 'exact',
])]
#[ApiFilter(DateFilter::class, properties: ['createdAt'])]
#[ApiFilter(OrderFilter::class, properties: ['id', 'createdAt'], arguments: ['orderParameterName' => 'order'])]
class Follow
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['follow:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'following')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull]
    #[Groups(['follow:read', 'follow:write'])]
    private ?User $follower = null;

    #[ORM\ManyToOne(inversedBy: 'followers')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull]
    #[Groups(['follow:read', 'follow:write'])]
    private ?User $following = null;

    #[ORM\Column]
    #[Assert\NotNull]
    #[Assert\Type(\DateTimeImmutable::class)]
    #[Groups(['follow:read'])]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFollower(): ?User
    {
        return $this->follower;
    }

    public function setFollower(?User $follower): static
    {
        $this->follower = $follower;
        return $this;
    }

    public function getFollowing(): ?User
    {
        return $this->following;
    }

    public function setFollowing(?User $following): static
    {
        $this->following = $following;
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
    public function validateNoSelfFollow(Assert\ExecutionContextInterface $context): void
    {
        if ($this->follower !== null && $this->following !== null && $this->follower === $this->following) {
            $context
                ->buildViolation('User cannot follow himself')
                ->atPath('following')
                ->addViolation();
        }
    }
}
