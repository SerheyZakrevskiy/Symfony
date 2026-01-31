<?php

namespace App\Controller;

use App\Entity\Message;
use App\Repository\MessageRepository;
use App\Repository\UserRepository;
use App\Service\Message\MessageService;
use App\Service\RequestCheckerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/message')]
class MessageController extends AbstractController
{
    private const REQUIRED_FIELDS_FOR_CREATE_MESSAGE = ['senderId', 'receiverId', 'content'];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MessageService $messageService,
        private readonly RequestCheckerService $requestCheckerService,
        private readonly UserRepository $userRepository,
        private readonly MessageRepository $messageRepository
    ) {}

    #[Route('', name: 'message_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $this->requestCheckerService->check($data, self::REQUIRED_FIELDS_FOR_CREATE_MESSAGE);

        $sender = $this->userRepository->find($data['senderId']);
        if (!$sender) {
            return new JsonResponse(['error' => 'Sender not found'], Response::HTTP_NOT_FOUND);
        }

        $receiver = $this->userRepository->find($data['receiverId']);
        if (!$receiver) {
            return new JsonResponse(['error' => 'Receiver not found'], Response::HTTP_NOT_FOUND);
        }

        $message = $this->messageService->createMessage(
            $sender,
            $receiver,
            (string) $data['content']
        );

        $this->entityManager->flush();

        return new JsonResponse(['id' => $message->getId()], Response::HTTP_CREATED);
    }

    #[Route('', name: 'message_list', methods: ['GET'])]
    public function getCollection(Request $request, MessageRepository $repo): JsonResponse
    {
        $q = $request->query->all();

        $itemsPerPage = isset($q['itemsPerPage']) ? (int) $q['itemsPerPage'] : 10;
        $page = isset($q['page']) ? (int) $q['page'] : 1;

        $data = $repo->getAllMessagesByFilter($q, $itemsPerPage, $page);

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'message_get', methods: ['GET'])]
    public function getOne(int $id): JsonResponse
    {
        $msg = $this->messageRepository->find($id);

        if (!$msg) {
            return new JsonResponse(['error' => 'Message not found'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse([
            'id' => $msg->getId(),
            'sender' => $msg->getSender()->getId(),
            'receiver' => $msg->getReceiver()->getId(),
            'content' => $msg->getContent(),
            'createdAt' => $msg->getCreatedAt()->format('Y-m-d H:i:s'),
        ], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'message_update', methods: ['PUT', 'PATCH'])]
    public function update(Request $request, int $id): JsonResponse
    {
        $msg = $this->messageRepository->find($id);
        if (!$msg) {
            return new JsonResponse(['error' => 'Message not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        $this->messageService->updateMessage($msg, $data);

        $this->entityManager->flush();

        return new JsonResponse(['status' => 'updated'], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'message_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $msg = $this->messageRepository->find($id);
        if (!$msg) {
            return new JsonResponse(['error' => 'Message not found'], Response::HTTP_NOT_FOUND);
        }

        $this->messageService->removeMessage($msg);

        $this->entityManager->flush();

        return new JsonResponse(['status' => 'deleted'], Response::HTTP_OK);
    }
}
