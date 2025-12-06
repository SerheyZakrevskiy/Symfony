<?php

namespace App\Controller;

use App\Entity\SavedPost;
use App\Repository\SavedPostRepository;
use App\Service\SocialService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/saved-post')]
class SavedPostController extends AbstractController
{
    #[Route('/', methods: ['GET'])]
    public function index(SavedPostRepository $repo): JsonResponse
    {
        return $this->json($repo->findAll());
    }

    #[Route('/', methods: ['POST'])]
    public function create(Request $request, SocialService $service): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $saved = $service->createSavedPost($data);

        return $this->json($saved, 201);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(SavedPost $saved): JsonResponse
    {
        return $this->json($saved);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(SavedPost $saved, SavedPostRepository $repo): JsonResponse
    {
        $repo->remove($saved, true);

        return $this->json(['status' => 'deleted']);
    }
}
