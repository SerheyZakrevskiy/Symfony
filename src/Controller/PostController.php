<?php

namespace App\Controller;

use App\Entity\Post;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use App\Service\Post\PostService;
use App\Service\RequestCheckerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/post')]
class PostController extends AbstractController
{
    private const REQUIRED_FIELDS_FOR_CREATE_POST = ['title', 'content', 'authorId'];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PostService $postService,
        private readonly RequestCheckerService $requestCheckerService,
        private readonly UserRepository $userRepository,
        private readonly PostRepository $postRepository,
    ) {}

    #[Route('', methods: ['GET'])]
    public function getCollection(Request $request): JsonResponse
    {
        $q = $request->query->all();

        $itemsPerPage = isset($q['itemsPerPage']) ? max(1, (int) $q['itemsPerPage']) : 10;
        $page = isset($q['page']) ? max(1, (int) $q['page']) : 1;

        $data = $this->postRepository->getAllPostsByFilter($q, $itemsPerPage, $page);

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = $this->safeJson($request);
        if ($data instanceof JsonResponse) {
            return $data;
        }

        $this->requestCheckerService->check($data, self::REQUIRED_FIELDS_FOR_CREATE_POST);

        $authorId = (int) $data['authorId'];
        $author = $this->userRepository->find($authorId);
        if (!$author) {
            return $this->json(['error' => 'Author not found'], Response::HTTP_NOT_FOUND);
        }

        $post = $this->postService->createPost(
            (string) $data['title'],
            (string) $data['content'],
            $author
        );

        $this->entityManager->flush();
        return $this->json($post, Response::HTTP_CREATED);
    }

    #[Route('/{id<\d+>}', methods: ['GET'])]
    public function show(Post $post): JsonResponse
    {
        return $this->json($post, Response::HTTP_OK);
    }

    #[Route('/{id<\d+>}', methods: ['PUT', 'PATCH'])]
    public function update(Request $request, Post $post): JsonResponse
    {
        $data = $this->safeJson($request);
        if ($data instanceof JsonResponse) {
            return $data;
        }
        $this->postService->updatePost($post, $data);

        $this->entityManager->flush();

        return $this->json($post, Response::HTTP_OK);
    }

    #[Route('/{id<\d+>}', methods: ['DELETE'])]
    public function delete(Post $post): JsonResponse
    {
        $this->postService->removePost($post);
        $this->entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }


    private function safeJson(Request $request): array|JsonResponse
    {
        try {
            $data = $request->toArray();
        } catch (\Throwable) {
            return $this->json(['error' => 'Invalid JSON body'], Response::HTTP_BAD_REQUEST);
        }

        if (!is_array($data) || $data === []) {
            return $this->json(['error' => 'Empty request body'], Response::HTTP_BAD_REQUEST);
        }

        return $data;
    }
}
