<?php

namespace App\Controller;

use App\Entity\Story;
use App\Repository\StoryRepository;
use App\Service\SocialService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/story')]
class StoryController extends AbstractController
{
    #[Route('/', methods: ['GET'])]
    public function index(StoryRepository $repo): JsonResponse
    {
        return $this->json($repo->findAll());
    }

    #[Route('/', methods: ['POST'])]
    public function create(Request $request, SocialService $service): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $story = $service->createStory($data);

        return $this->json($story, 201);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(Story $story): JsonResponse
    {
        return $this->json($story);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(Story $story, StoryRepository $repo): JsonResponse
    {
        $repo->remove($story, true);
        return $this->json(['status' => 'deleted']);
    }
}
