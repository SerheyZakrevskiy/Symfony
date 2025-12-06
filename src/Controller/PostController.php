<?php

namespace App\Controller;

use App\Entity\Post;
use App\Repository\PostRepository;
use App\Service\SocialService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/post')]
class PostController extends AbstractController
{
    #[Route('/', methods: ['GET'])]
    public function index(PostRepository $repo): JsonResponse
    {
        return $this->json($repo->findAll());
    }

    #[Route('/', methods: ['POST'])]
    public function create(Request $request, SocialService $service): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $post = $service->createPost($data);

        return $this->json($post, 201);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(Post $post): JsonResponse
    {
        return $this->json($post);
    }

    #[Route('/{id}', methods: ['PUT', 'PATCH'])]
    public function update(Request $request, Post $post, PostRepository $repo): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (isset($data['title'])) {
            $post->setTitle($data['title']);
        }

        if (isset($data['content'])) {
            $post->setContent($data['content']);
        }

        $repo->save($post, true);

        return $this->json($post);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(Post $post, PostRepository $repo): JsonResponse
    {
        $repo->remove($post, true);

        return $this->json(['status' => 'deleted']);
    }
}
