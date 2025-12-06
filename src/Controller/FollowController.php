<?php

namespace App\Controller;

use App\Entity\Follow;
use App\Repository\FollowRepository;
use App\Service\SocialService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/follow')]
class FollowController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private SocialService $socialService
    ) {}

    #[Route('/', methods: ['GET'])]
    public function index(FollowRepository $repo): JsonResponse
    {
        return $this->json($repo->findAll());
    }

    #[Route('/', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $follow = $this->socialService->createFollow($data);

        return $this->json($follow, 201);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(Follow $follow): JsonResponse
    {
        return $this->json($follow);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(Follow $follow): JsonResponse
    {
        $this->em->remove($follow);
        $this->em->flush();

        return $this->json(['message' => 'Follow removed']);
    }
}
