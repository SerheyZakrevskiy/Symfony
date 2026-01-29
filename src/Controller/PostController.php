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
        private readonly UserRepository $userRepository
    ) {}

    #[Route('/', methods: ['GET'])]
    public function getCollection(Request $request, PostRepository $repo): JsonResponse
    {
        $q = $request->query->all();

        $itemsPerPage = isset($q['itemsPerPage']) ? (int) $q['itemsPerPage'] : 10;
        $page = isset($q['page']) ? (int) $q['page'] : 1;

        $data = $repo->getAllPostsByFilter($q, $itemsPerPage, $page);

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $this->requestCheckerService->check($data, self::REQUIRED_FIELDS_FOR_CREATE_POST);

        $author = $this->userRepository->find($data['authorId']);
        if (!$author) {
            return new JsonResponse(['error' => 'Author not found'], Response::HTTP_NOT_FOUND);
        }

        $post = $this->postService->createPost(
            (string) $data['title'],
            (string) $data['content'],
            $author
        );

        $this->entityManager->flush();

        return $this->json($post, Response::HTTP_CREATED);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(Post $post): JsonResponse
    {
        return $this->json($post, Response::HTTP_OK);
    }

    #[Route('/{id}', methods: ['PUT', 'PATCH'])]
    public function update(Request $request, Post $post): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $this->postService->updatePost($post, $data);

        $this->entityManager->flush();

        return $this->json($post, Response::HTTP_OK);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(Post $post): JsonResponse
    {
        $this->postService->removePost($post);

        $this->entityManager->flush();

        return $this->json(['status' => 'deleted'], Response::HTTP_OK);
    }
}
