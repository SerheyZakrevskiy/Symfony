<?php

namespace App\Controller;

use App\Entity\Story;
use App\Repository\StoryRepository;
use App\Repository\UserRepository;
use App\Service\RequestCheckerService;
use App\Service\Story\StoryService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/story')]
class StoryController extends AbstractController
{
    private const REQUIRED_FIELDS_FOR_CREATE_STORY = ['authorId', 'mediaUrl'];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly StoryService $storyService,
        private readonly RequestCheckerService $requestCheckerService,
        private readonly UserRepository $userRepository
    ) {}

    #[Route('/', methods: ['GET'])]
    public function getCollection(Request $request, StoryRepository $repo): JsonResponse
    {
        $q = $request->query->all();

        $itemsPerPage = isset($q['itemsPerPage']) ? (int) $q['itemsPerPage'] : 10;
        $page = isset($q['page']) ? (int) $q['page'] : 1;

        $data = $repo->getAllStoriesByFilter($q, $itemsPerPage, $page);

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $this->requestCheckerService->check($data, self::REQUIRED_FIELDS_FOR_CREATE_STORY);

        $author = $this->userRepository->find($data['authorId']);
        if (!$author) {
            return new JsonResponse(['error' => 'Author not found'], Response::HTTP_NOT_FOUND);
        }

        $caption = array_key_exists('caption', $data)
            ? ($data['caption'] === null ? null : (string) $data['caption'])
            : null;

        $expiresAt = null;
        if (array_key_exists('expiresAt', $data) && is_string($data['expiresAt']) && $data['expiresAt'] !== '') {
            try {
                $expiresAt = new \DateTimeImmutable($data['expiresAt']);
            } catch (\Throwable) {
                return new JsonResponse(['error' => 'Invalid expiresAt format'], Response::HTTP_BAD_REQUEST);
            }
        }

        $story = $this->storyService->createStory(
            $author,
            (string) $data['mediaUrl'],
            $caption,
            null,
            $expiresAt
        );

        $this->entityManager->flush();

        return $this->json($story, Response::HTTP_CREATED);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(Story $story): JsonResponse
    {
        return $this->json($story, Response::HTTP_OK);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(Story $story): JsonResponse
    {
        $this->storyService->removeStory($story);

        $this->entityManager->flush();

        return $this->json(['status' => 'deleted'], Response::HTTP_OK);
    }
}
