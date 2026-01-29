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
        private readonly UserRepository $userRepository
    ) {}

    #[Route('/', methods: ['GET'])]
    public function getCollection(Request $request, NotificationRepository $repo): JsonResponse
    {
        $q = $request->query->all();

        $itemsPerPage = isset($q['itemsPerPage']) ? (int) $q['itemsPerPage'] : 10;
        $page = isset($q['page']) ? (int) $q['page'] : 1;

        $data = $repo->getAllNotificationsByFilter($q, $itemsPerPage, $page);

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $this->requestCheckerService->check($data, self::REQUIRED_FIELDS_FOR_CREATE_NOTIFICATION);

        $recipient = $this->userRepository->find($data['recipientId']);
        if (!$recipient) {
            return new JsonResponse(['error' => 'Recipient not found'], Response::HTTP_NOT_FOUND);
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

    #[Route('/{id}', methods: ['GET'])]
    public function show(Notification $notification): JsonResponse
    {
        return $this->json($notification, Response::HTTP_OK);
    }

    #[Route('/{id}', methods: ['PATCH', 'PUT'])]
    public function update(Request $request, Notification $notification): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $this->notificationService->updateNotification($notification, $data);

        $this->entityManager->flush();

        return $this->json($notification, Response::HTTP_OK);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(Notification $notification): JsonResponse
    {
        $this->notificationService->removeNotification($notification);

        $this->entityManager->flush();

        return $this->json(['status' => 'deleted'], Response::HTTP_OK);
    }
}
