<?php

namespace App\Controller;

use App\Entity\Notification;
use App\Repository\NotificationRepository;
use App\Service\SocialService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/notification')]
class NotificationController extends AbstractController
{
    #[Route('/', methods: ['GET'])]
    public function index(NotificationRepository $repo): JsonResponse
    {
        return $this->json($repo->findAll());
    }

    #[Route('/', methods: ['POST'])]
    public function create(Request $request, SocialService $service): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $notification = $service->createNotification($data);

        return $this->json($notification, 201);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(Notification $notification): JsonResponse
    {
        return $this->json($notification);
    }

    #[Route('/{id}', methods: ['PATCH', 'PUT'])]
    public function update(Request $request, Notification $notification, NotificationRepository $repo): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (isset($data['type'])) {
            $notification->setType($data['type']);
        }

        if (isset($data['message'])) {
            $notification->setMessage($data['message']);
        }

        if (isset($data['is_read'])) {
            $notification->setIsRead($data['is_read']);
        }

        $repo->save($notification, true);

        return $this->json($notification);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(Notification $notification, NotificationRepository $repo): JsonResponse
    {
        $repo->remove($notification, true);

        return $this->json(['status' => 'deleted']);
    }
}
