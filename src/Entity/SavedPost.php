<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post as ApiPost;
use ApiPlatform\Metadata\Put;
use App\Repository\SavedPostRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: SavedPostRepository::class)]
#[UniqueEntity(
    fields: ['author', 'post'],
    message: 'Post is already saved by this user'
)]
#[ApiResource(
    operations: [
        new Get(normalizationContext: ['groups' => ['saved_post:read']]),
        new GetCollection(normalizationContext: ['groups' => ['saved_post:read']]),
        new ApiPost(
            denormalizationContext: ['groups' => ['saved_post:write']],
            normalizationContext: ['groups' => ['saved_post:read']]
        ),
        new Put(
            denormalizationContext: ['groups' => ['saved_post:write']],
            normalizationContext: ['groups' => ['saved_post:read']]
        ),
        new Patch(
            denormalizationContext: ['groups' => ['saved_post:write']],
            normalizationContext: ['groups' => ['saved_post:read']]
        ),
        new Delete(),
    ],
    paginationEnabled: true,
    paginationItemsPerPage: 10
)]
class SavedPost
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['saved_post:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull]
    #[Groups(['saved_post:read', 'saved_post:write'])]
    private ?User $author = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull]
    #[Groups(['saved_post:read', 'saved_post:write'])]
    private ?Post $post = null;

    #[ORM\Column]
    #[Assert\NotNull]
    #[Assert\Type(\DateTimeImmutable::class)]
    #[Groups(['saved_post:read'])]
    private \DateTimeImmutable $savedAt;

    public function __construct()
    {
        $this->savedAt = new \DateTimeImmutable();
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

    public function getPost(): ?Post
    {
        return $this->post;
    }

    public function setPost(?Post $post): static
    {
        $this->post = $post;
        return $this;
    }

    public function getSavedAt(): \DateTimeImmutable
    {
        return $this->savedAt;
    }

    public function setSavedAt(\DateTimeImmutable $savedAt): static
    {
        $this->savedAt = $savedAt;
        return $this;
    }
}
