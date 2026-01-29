<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\RequestCheckerService;
use App\Service\User\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/user')]
class UserController extends AbstractController
{
    private const REQUIRED_FIELDS_FOR_CREATE_USER = ['username', 'email', 'password'];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserService $userService,
        private readonly RequestCheckerService $requestCheckerService
    ) {}

    #[Route('/', methods: ['GET'])]
    public function index(UserRepository $userRepository): JsonResponse
    {
        return $this->json($userRepository->findAll(), Response::HTTP_OK);
    }

    #[Route('/', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $this->requestCheckerService->check($data, self::REQUIRED_FIELDS_FOR_CREATE_USER);

        $user = $this->userService->createUser(
            (string) $data['username'],
            (string) $data['email'],
            (string) $data['password']
        );

        $this->entityManager->flush();

        return $this->json($user, Response::HTTP_CREATED);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(User $user): JsonResponse
    {
        return $this->json($user, Response::HTTP_OK);
    }

    #[Route('/{id}', methods: ['PUT', 'PATCH'])]
    public function update(Request $request, User $user): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $this->userService->updateProfile($user, $data);

        if (array_key_exists('password', $data) && is_string($data['password']) && $data['password'] !== '') {
            $this->userService->changePassword($user, (string) $data['password']);
        }

        $this->entityManager->flush();

        return $this->json($user, Response::HTTP_OK);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(User $user): JsonResponse
    {
        $this->userService->removeUser($user);

        $this->entityManager->flush();

        return $this->json(['message' => 'User deleted'], Response::HTTP_OK);
    }
}
