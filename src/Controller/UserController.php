<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\SocialService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/user')]
class UserController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private SocialService $socialService
    ) {}

    #[Route('/', methods: ['GET'])]
    public function index(UserRepository $userRepository): JsonResponse
    {
        return $this->json($userRepository->findAll());
    }

    #[Route('/', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $user = $this->socialService->createUser($data);

        return $this->json($user, 201);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(User $user): JsonResponse
    {
        return $this->json($user);
    }

    #[Route('/{id}', methods: ['PUT', 'PATCH'])]
    public function update(Request $request, User $user): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (isset($data['username'])) $user->setUsername($data['username']);
        if (isset($data['email'])) $user->setEmail($data['email']);
        if (isset($data['password'])) $user->setPassword($data['password']);

        $this->em->flush();

        return $this->json($user);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(User $user): JsonResponse
    {
        $this->em->remove($user);
        $this->em->flush();

        return $this->json(['message' => 'User deleted']);
    }
}
