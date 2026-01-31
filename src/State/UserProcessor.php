<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UserProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly ProcessorInterface $persistProcessor,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if ($data instanceof User) {
            if (!$data->getCreatedAt()) {
                $data->setCreatedAt(new \DateTimeImmutable());
            }
            $plain = $data->getPassword();
            if (is_string($plain) && $plain !== '' && !str_starts_with($plain, '$argon2') && !str_starts_with($plain, '$2y$')) {
                $data->setPassword($this->passwordHasher->hashPassword($data, $plain));
            }
        }

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}
