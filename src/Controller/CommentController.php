<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Repository\CommentRepository;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use App\Service\Comment\CommentService;
use App\Service\RequestCheckerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/comment')]
class CommentController extends AbstractController
{
    private const REQUIRED_FIELDS_FOR_CREATE_COMMENT = ['content', 'authorId', 'postId'];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CommentService $commentService,
        private readonly RequestCheckerService $requestCheckerService,
        private readonly UserRepository $userRepository,
        private readonly PostRepository $postRepository
    ) {}

    #[Route('/', methods: ['GET'])]
    public function index(CommentRepository $commentRepo): JsonResponse
    {
        return $this->json($commentRepo->findAll(), Response::HTTP_OK);
    }

    #[Route('/', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $this->requestCheckerService->check($data, self::REQUIRED_FIELDS_FOR_CREATE_COMMENT);

        $author = $this->userRepository->find($data['authorId']);
        if (!$author) {
            return new JsonResponse(['error' => 'Author not found'], Response::HTTP_NOT_FOUND);
        }

        $post = $this->postRepository->find($data['postId']);
        if (!$post) {
            return new JsonResponse(['error' => 'Post not found'], Response::HTTP_NOT_FOUND);
        }

        $comment = $this->commentService->createComment(
            (string) $data['content'],
            $author,
            $post
        );

        $this->entityManager->flush();

        return $this->json($comment, Response::HTTP_CREATED);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(Comment $comment): JsonResponse
    {
        return $this->json($comment, Response::HTTP_OK);
    }

    #[Route('/{id}', methods: ['PUT', 'PATCH'])]
    public function update(Request $request, Comment $comment): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $this->commentService->updateComment($comment, $data);

        $this->entityManager->flush();

        return $this->json($comment, Response::HTTP_OK);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(Comment $comment): JsonResponse
    {
        $this->commentService->removeComment($comment);

        $this->entityManager->flush();

        return $this->json(['message' => 'Comment deleted'], Response::HTTP_OK);
    }
}
