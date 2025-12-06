<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class ItemController extends AbstractController
{
    private array $items = [
        1 => ['id' => 1, 'name' => 'Item 1'],
        2 => ['id' => 2, 'name' => 'Item 2'],
    ];

    #[Route('/items', name: 'items_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        return $this->json(array_values($this->items));
    }

    #[Route('/items/{id}', name: 'items_show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        if (!isset($this->items[$id])) {
            return $this->json(['error' => 'Item not found'], 404);
        }

        return $this->json($this->items[$id]);
    }

    #[Route('/items', name: 'items_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['name'])) {
            return $this->json(['error' => 'Name is required'], 400);
        }

        $id = count($this->items) + 1;

        $item = [
            'id' => $id,
            'name' => $data['name']
        ];

        $this->items[$id] = $item;

        return $this->json($item, 201);
    }

    #[Route('/items/{id}', name: 'items_update', methods: ['PUT'])]
    public function update(Request $request, int $id): JsonResponse
    {
        if (!isset($this->items[$id])) {
            return $this->json(['error' => 'Item not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['name'])) {
            return $this->json(['error' => 'Name is required'], 400);
        }

        $this->items[$id]['name'] = $data['name'];

        return $this->json($this->items[$id]);
    }

    #[Route('/items/{id}', name: 'items_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        if (!isset($this->items[$id])) {
            return $this->json(['error' => 'Item not found'], 404);
        }

        unset($this->items[$id]);

        return $this->json(['message' => "Item $id deleted"]);
    }
}

