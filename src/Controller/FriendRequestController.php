<?php

namespace App\Controller;

use App\Entity\FriendRequest;
use App\Repository\FriendRequestRepository;
use App\Repository\UserRepository;
use App\Service\FriendRequest\FriendRequestService;
use App\Service\RequestCheckerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/friend-request')]
class FriendRequestController extends AbstractController
{
    private const REQUIRED_FIELDS_FOR_CREATE_FRIEND_REQUEST = ['fromUserId', 'toUserId'];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly FriendRequestService $friendRequestService,
        private readonly RequestCheckerService $requestCheckerService,
        private readonly UserRepository $userRepository
    ) {}

    #[Route('/', methods: ['GET'])]
    public function index(FriendRequestRepository $repo): JsonResponse
    {
        return $this->json($repo->findAll(), Response::HTTP_OK);
    }

    #[Route('/', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $this->requestCheckerService->check($data, self::REQUIRED_FIELDS_FOR_CREATE_FRIEND_REQUEST);

        $fromUser = $this->userRepository->find($data['fromUserId']);
        if (!$fromUser) {
            return new JsonResponse(['error' => 'From user not found'], Response::HTTP_NOT_FOUND);
        }

        $toUser = $this->userRepository->find($data['toUserId']);
        if (!$toUser) {
            return new JsonResponse(['error' => 'To user not found'], Response::HTTP_NOT_FOUND);
        }

        $friendRequest = $this->friendRequestService->createFriendRequest($fromUser, $toUser);

        $this->entityManager->flush();

        return $this->json($friendRequest, Response::HTTP_CREATED);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(FriendRequest $requestEntity): JsonResponse
    {
        return $this->json($requestEntity, Response::HTTP_OK);
    }

    #[Route('/{id}/accept', methods: ['POST'])]
    public function accept(FriendRequest $requestEntity): JsonResponse
    {
        $this->friendRequestService->accept($requestEntity);
        $this->entityManager->flush();

        return $this->json([
            'message' => 'Friend request accepted',
            'id' => $requestEntity->getId(),
            'status' => $requestEntity->getStatus(),
        ], Response::HTTP_OK);
    }

    #[Route('/{id}/reject', methods: ['POST'])]
    public function reject(FriendRequest $requestEntity): JsonResponse
    {
        $this->friendRequestService->reject($requestEntity);
        $this->entityManager->flush();

        return $this->json([
            'message' => 'Friend request rejected',
            'id' => $requestEntity->getId(),
            'status' => $requestEntity->getStatus(),
        ], Response::HTTP_OK);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(FriendRequest $requestEntity): JsonResponse
    {
        $this->friendRequestService->removeFriendRequest($requestEntity);
        $this->entityManager->flush();

        return $this->json(['message' => 'Friend request deleted'], Response::HTTP_OK);
    }
}
