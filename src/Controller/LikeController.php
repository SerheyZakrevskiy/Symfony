<?php

namespace App\Controller;

use App\Entity\Like;
use App\Repository\LikeRepository;
use App\Service\SocialService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/like')]
class LikeController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private SocialService $service
    ) {}

    #[Route('/', methods: ['GET'])]
    public function index(LikeRepository $repo): JsonResponse
    {
        return $this->json($repo->findAll());
    }

    #[Route('/', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $like = $this->service->createLike($data);

        return $this->json($like, 201);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(Like $like): JsonResponse
    {
        return $this->json($like);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(Like $like): JsonResponse
    {
        $this->em->remove($like);
        $this->em->flush();

        return $this->json(['message' => 'Like removed']);
    }
}
