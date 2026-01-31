<?php

namespace App\Controller;

use App\Entity\Notification;
use App\Repository\NotificationRepository;
use App\Repository\UserRepository;
use App\Service\Notification\NotificationService;
use App\Service\RequestCheckerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/notification')]
class NotificationController extends AbstractController
{
    private const REQUIRED_FIELDS_FOR_CREATE_NOTIFICATION = ['recipientId', 'type', 'message'];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly NotificationService $notificationService,
        private readonly RequestCheckerService $requestCheckerService,
        private readonly UserRepository $userRepository,
        private readonly NotificationRepository $notificationRepository,
    ) {}

    #[Route('', methods: ['GET'])]
    public function getCollection(Request $request): JsonResponse
    {
        $q = $request->query->all();

        $itemsPerPage = isset($q['itemsPerPage']) ? max(1, (int) $q['itemsPerPage']) : 10;
        $page = isset($q['page']) ? max(1, (int) $q['page']) : 1;

        $data = $this->notificationRepository->getAllNotificationsByFilter($q, $itemsPerPage, $page);

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = $this->safeJson($request);
        if ($data instanceof JsonResponse) {
            return $data;
        }

        $this->requestCheckerService->check($data, self::REQUIRED_FIELDS_FOR_CREATE_NOTIFICATION);

        $recipientId = (int) $data['recipientId'];
        $recipient = $this->userRepository->find($recipientId);
        if (!$recipient) {
            return $this->json(['error' => 'Recipient not found'], Response::HTTP_NOT_FOUND);
        }

        $isRead = array_key_exists('isRead', $data) ? (bool) $data['isRead'] : false;

        $notification = $this->notificationService->createNotification(
            $recipient,
            (string) $data['type'],
            (string) $data['message'],
            $isRead
        );

        $this->entityManager->flush();

        return $this->json($notification, Response::HTTP_CREATED);
    }

    #[Route('/{id<\d+>}', methods: ['GET'])]
    public function show(Notification $notification): JsonResponse
    {
        return $this->json($notification, Response::HTTP_OK);
    }

    #[Route('/{id<\d+>}', methods: ['PUT', 'PATCH'])]
    public function update(Request $request, Notification $notification): JsonResponse
    {
        $data = $this->safeJson($request);
        if ($data instanceof JsonResponse) {
            return $data;
        }

        $this->notificationService->updateNotification($notification, $data);

        $this->entityManager->flush();

        return $this->json($notification, Response::HTTP_OK);
    }

    #[Route('/{id<\d+>}', methods: ['DELETE'])]
    public function delete(Notification $notification): JsonResponse
    {
        $this->notificationService->removeNotification($notification);
        $this->entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    private function safeJson(Request $request): array|JsonResponse
    {
        try {
            $data = $request->toArray();
        } catch (\Throwable) {
            return $this->json(['error' => 'Invalid JSON body'], Response::HTTP_BAD_REQUEST);
        }

        if (!is_array($data) || $data === []) {
            return $this->json(['error' => 'Empty request body'], Response::HTTP_BAD_REQUEST);
        }

        return $data;
    }
}
