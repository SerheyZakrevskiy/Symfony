<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post as ApiPost;
use ApiPlatform\Metadata\Put;
use App\Repository\MessageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
#[ApiResource(
    operations: [
        new Get(normalizationContext: ['groups' => ['message:read']]),
        new GetCollection(normalizationContext: ['groups' => ['message:read']]),
        new ApiPost(
            denormalizationContext: ['groups' => ['message:write']],
            normalizationContext: ['groups' => ['message:read']]
        ),
        new Put(
            denormalizationContext: ['groups' => ['message:write']],
            normalizationContext: ['groups' => ['message:read']]
        ),
        new Patch(
            denormalizationContext: ['groups' => ['message:write']],
            normalizationContext: ['groups' => ['message:read']]
        ),
        new Delete(),
    ],
    paginationEnabled: true,
    paginationItemsPerPage: 10
)]
class Message
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['message:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'sentMessages')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull]
    #[Groups(['message:read', 'message:write'])]
    private ?User $sender = null;

    #[ORM\ManyToOne(inversedBy: 'receivedMessages')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull]
    #[Groups(['message:read', 'message:write'])]
    private ?User $receiver = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    #[Assert\Length(
        min: 1,
        max: 5000,
        minMessage: 'Message cannot be empty',
        maxMessage: 'Message cannot be longer than {{ limit }} characters'
    )]
    #[Groups(['message:read', 'message:write'])]
    private string $content = '';

    #[ORM\Column]
    #[Assert\NotNull]
    #[Assert\Type(\DateTimeImmutable::class)]
    #[Groups(['message:read'])]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSender(): ?User
    {
        return $this->sender;
    }

    public function setSender(?User $sender): static
    {
        $this->sender = $sender;
        return $this;
    }

    public function getReceiver(): ?User
    {
        return $this->receiver;
    }

    public function setReceiver(?User $receiver): static
    {
        $this->receiver = $receiver;
        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;
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
    public function validateSenderReceiver(Assert\ExecutionContextInterface $context): void
    {
        if ($this->sender !== null && $this->receiver !== null && $this->sender === $this->receiver) {
            $context
                ->buildViolation('User cannot send message to himself')
                ->atPath('receiver')
                ->addViolation();
        }
    }
}
