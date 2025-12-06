<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Repository\CommentRepository;
use App\Service\SocialService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/comment')]
class CommentController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private SocialService $socialService
    ) {}

    #[Route('/', methods: ['GET'])]
    public function index(CommentRepository $commentRepo): JsonResponse
    {
        return $this->json($commentRepo->findAll());
    }

    #[Route('/', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $comment = $this->socialService->createComment($data);

        return $this->json($comment, 201);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(Comment $comment): JsonResponse
    {
        return $this->json($comment);
    }

    #[Route('/{id}', methods: ['PUT', 'PATCH'])]
    public function update(Request $request, Comment $comment): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (isset($data['content'])) {
            $comment->setContent($data['content']);
        }

        $this->em->flush();

        return $this->json($comment);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(Comment $comment): JsonResponse
    {
        $this->em->remove($comment);
        $this->em->flush();

        return $this->json(['message' => 'Comment deleted']);
    }
}
