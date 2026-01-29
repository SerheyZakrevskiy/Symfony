<?php

namespace App\Controller;

use App\Entity\Like;
use App\Repository\LikeRepository;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use App\Service\Like\LikeService;
use App\Service\RequestCheckerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/like')]
class LikeController extends AbstractController
{
    private const REQUIRED_FIELDS_FOR_CREATE_LIKE = ['likedById', 'postId'];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly LikeService $likeService,
        private readonly RequestCheckerService $requestCheckerService,
        private readonly UserRepository $userRepository,
        private readonly PostRepository $postRepository
    ) {}

    #[Route('/', methods: ['GET'])]
    public function index(LikeRepository $repo): JsonResponse
    {
        return $this->json($repo->findAll(), Response::HTTP_OK);
    }

    #[Route('/', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $this->requestCheckerService->check($data, self::REQUIRED_FIELDS_FOR_CREATE_LIKE);

        $likedBy = $this->userRepository->find($data['likedById']);
        if (!$likedBy) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $post = $this->postRepository->find($data['postId']);
        if (!$post) {
            return new JsonResponse(['error' => 'Post not found'], Response::HTTP_NOT_FOUND);
        }

        $like = $this->likeService->createLike($likedBy, $post);

        $this->entityManager->flush();

        return $this->json($like, Response::HTTP_CREATED);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(Like $like): JsonResponse
    {
        return $this->json($like, Response::HTTP_OK);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(Like $like): JsonResponse
    {
        $this->likeService->removeLike($like);

        $this->entityManager->flush();

        return $this->json(['message' => 'Like removed'], Response::HTTP_OK);
    }
}
