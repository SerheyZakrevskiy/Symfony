<?php

namespace App\Controller;

use App\Entity\Follow;
use App\Repository\FollowRepository;
use App\Repository\UserRepository;
use App\Service\Follow\FollowService;
use App\Service\RequestCheckerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/follow')]
class FollowController extends AbstractController
{
    private const REQUIRED_FIELDS_FOR_CREATE_FOLLOW = ['followerId', 'followingId'];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly FollowService $followService,
        private readonly RequestCheckerService $requestCheckerService,
        private readonly UserRepository $userRepository
    ) {}

    #[Route('/', methods: ['GET'])]
    public function index(FollowRepository $repo): JsonResponse
    {
        return $this->json($repo->findAll(), Response::HTTP_OK);
    }

    #[Route('/', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $this->requestCheckerService->check($data, self::REQUIRED_FIELDS_FOR_CREATE_FOLLOW);

        $follower = $this->userRepository->find($data['followerId']);
        if (!$follower) {
            return new JsonResponse(['error' => 'Follower not found'], Response::HTTP_NOT_FOUND);
        }

        $following = $this->userRepository->find($data['followingId']);
        if (!$following) {
            return new JsonResponse(['error' => 'Following user not found'], Response::HTTP_NOT_FOUND);
        }

        $follow = $this->followService->createFollow($follower, $following);

        $this->entityManager->flush();

        return $this->json($follow, Response::HTTP_CREATED);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(Follow $follow): JsonResponse
    {
        return $this->json($follow, Response::HTTP_OK);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(Follow $follow): JsonResponse
    {
        $this->followService->removeFollow($follow);

        $this->entityManager->flush();

        return $this->json(['message' => 'Follow removed'], Response::HTTP_OK);
    }
}
