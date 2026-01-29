<?php

namespace App\Service\User;

use App\Entity\User;
use App\Services\RequestCheckerService;
use Doctrine\ORM\EntityManagerInterface;

class UserService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly RequestCheckerService $requestCheckerService
    ) {}

    public function createUser(
        string $username,
        string $email,
        string $password,
        ?\DateTimeImmutable $createdAt = null
    ): User {
        $user = (new User())
            ->setUsername($username)
            ->setEmail($email)
            ->setPassword($password)
            ->setCreatedAt($createdAt ?? new \DateTimeImmutable());

        $this->requestCheckerService->validateRequestDataByConstraints($user);

        $this->entityManager->persist($user);

        return $user;
    }

    public function updateProfile(User $user, array $data): void
    {

        if (array_key_exists('username', $data)) {
            $user->setUsername((string) $data['username']);
        }

        if (array_key_exists('email', $data)) {
            $user->setEmail((string) $data['email']);
        }

        if (array_key_exists('bio', $data)) {
            $value = $data['bio'];
            $user->setBio($value === null ? null : (string) $value);
        }

        if (array_key_exists('avatarUrl', $data)) {
            $value = $data['avatarUrl'];
            $user->setAvatarUrl($value === null ? null : (string) $value);
        }

        $this->requestCheckerService->validateRequestDataByConstraints($user);
    }

    public function changePassword(User $user, string $newPassword): void
    {
        $user->setPassword($newPassword);

        $this->requestCheckerService->validateRequestDataByConstraints($user);
    }

    public function removeUser(User $user): void
    {
        $this->entityManager->remove($user);
    }

    public function flush(): void
    {
        $this->entityManager->flush();
    }
}
