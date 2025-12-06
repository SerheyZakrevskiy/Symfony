<?php

namespace App\Controller;

use App\Entity\FriendRequest;
use App\Repository\FriendRequestRepository;
use App\Service\SocialService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/friend-request')]
class FriendRequestController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private SocialService $service
    ) {}

    #[Route('/', methods: ['GET'])]
    public function index(FriendRequestRepository $repo): JsonResponse
    {
        return $this->json($repo->findAll());
    }

    #[Route('/', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $friendRequest = $this->service->createFriendRequest($data);

        return $this->json($friendRequest, 201);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(FriendRequest $requestEntity): JsonResponse
    {
        return $this->json($requestEntity);
    }

    #[Route('/{id}/accept', methods: ['POST'])]
    public function accept(FriendRequest $requestEntity): JsonResponse
    {
        $requestEntity->setStatus('accepted');
        $this->em->flush();

        return $this->json([
            'message' => 'Friend request accepted',
            'id' => $requestEntity->getId(),
        ]);
    }

    #[Route('/{id}/reject', methods: ['POST'])]
    public function reject(FriendRequest $requestEntity): JsonResponse
    {
        $requestEntity->setStatus('rejected');
        $this->em->flush();

        return $this->json([
            'message' => 'Friend request rejected',
            'id' => $requestEntity->getId(),
        ]);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(FriendRequest $requestEntity): JsonResponse
    {
        $this->em->remove($requestEntity);
        $this->em->flush();

        return $this->json(['message' => 'Friend request deleted']);
    }
}
