<?php

namespace App\Controller;

use App\Entity\SavedPost;
use App\Repository\PostRepository;
use App\Repository\SavedPostRepository;
use App\Repository\UserRepository;
use App\Service\RequestCheckerService;
use App\Service\SavedPost\SavedPostService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/saved-post')]
class SavedPostController extends AbstractController
{
    private const REQUIRED_FIELDS_FOR_CREATE_SAVED_POST = ['authorId', 'postId'];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SavedPostService $savedPostService,
        private readonly RequestCheckerService $requestCheckerService,
        private readonly UserRepository $userRepository,
        private readonly PostRepository $postRepository
    ) {}

    #[Route('/', methods: ['GET'])]
    public function getCollection(Request $request, SavedPostRepository $repo): JsonResponse
    {
        $q = $request->query->all();

        $itemsPerPage = isset($q['itemsPerPage']) ? (int) $q['itemsPerPage'] : 10;
        $page = isset($q['page']) ? (int) $q['page'] : 1;

        $data = $repo->getAllSavedPostsByFilter($q, $itemsPerPage, $page);

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $this->requestCheckerService->check($data, self::REQUIRED_FIELDS_FOR_CREATE_SAVED_POST);

        $author = $this->userRepository->find($data['authorId']);
        if (!$author) {
            return new JsonResponse(['error' => 'Author not found'], Response::HTTP_NOT_FOUND);
        }

        $post = $this->postRepository->find($data['postId']);
        if (!$post) {
            return new JsonResponse(['error' => 'Post not found'], Response::HTTP_NOT_FOUND);
        }

        $saved = $this->savedPostService->createSavedPost($author, $post);

        $this->entityManager->flush();

        return $this->json($saved, Response::HTTP_CREATED);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(SavedPost $saved): JsonResponse
    {
        return $this->json($saved, Response::HTTP_OK);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(SavedPost $saved): JsonResponse
    {
        $this->savedPostService->removeSavedPost($saved);

        $this->entityManager->flush();

        return $this->json(['status' => 'deleted'], Response::HTTP_OK);
    }
}
