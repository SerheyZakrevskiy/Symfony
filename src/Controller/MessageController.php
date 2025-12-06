<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\User;
use App\Service\SocialService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/message')]
class MessageController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private SocialService $socialService
    ) {}

    #[Route('', name: 'message_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $message = new Message();

        $sender = $this->em->getRepository(User::class)->find($data['sender_id']);
        if (!$sender) {
            return new JsonResponse(['error' => 'Sender not found'], 404);
        }
        $message->setSender($sender);

        $receiver = $this->em->getRepository(User::class)->find($data['receiver_id']);
        if (!$receiver) {
            return new JsonResponse(['error' => 'Receiver not found'], 404);
        }
        $message->setReceiver($receiver);

        $message->setContent($data['content']);
        $message->setCreatedAt(new \DateTimeImmutable());

        $this->em->persist($message);
        $this->em->flush();

        return new JsonResponse(['id' => $message->getId()], 201);
    }

    #[Route('', name: 'message_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $messages = $this->em->getRepository(Message::class)->findAll();

        $result = [];
        foreach ($messages as $msg) {
            $result[] = [
                'id' => $msg->getId(),
                'sender' => $msg->getSender()->getId(),
                'receiver' => $msg->getReceiver()->getId(),
                'content' => $msg->getContent(),
                'createdAt' => $msg->getCreatedAt()->format('Y-m-d H:i:s'),
            ];
        }

        return new JsonResponse($result);
    }

    #[Route('/{id}', name: 'message_get', methods: ['GET'])]
    public function getOne(int $id): JsonResponse
    {
        $msg = $this->em->getRepository(Message::class)->find($id);

        if (!$msg) {
            return new JsonResponse(['error' => 'Message not found'], 404);
        }

        return new JsonResponse([
            'id' => $msg->getId(),
            'sender' => $msg->getSender()->getId(),
            'receiver' => $msg->getReceiver()->getId(),
            'content' => $msg->getContent(),
            'createdAt' => $msg->getCreatedAt()->format('Y-m-d H:i:s'),
        ]);
    }

    #[Route('/{id}', name: 'message_update', methods: ['PUT'])]
    public function update(Request $request, int $id): JsonResponse
    {
        $msg = $this->em->getRepository(Message::class)->find($id);
        if (!$msg) {
            return new JsonResponse(['error' => 'Message not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['content'])) {
            $msg->setContent($data['content']);
        }

        $this->em->flush();

        return new JsonResponse(['status' => 'updated']);
    }

    #[Route('/{id}', name: 'message_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $msg = $this->em->getRepository(Message::class)->find($id);
        if (!$msg) {
            return new JsonResponse(['error' => 'Message not found'], 404);
        }

        $this->em->remove($msg);
        $this->em->flush();

        return new JsonResponse(['status' => 'deleted']);
    }
}
