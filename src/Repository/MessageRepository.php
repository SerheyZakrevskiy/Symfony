<?php

namespace App\Repository;

use App\Entity\Message;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    public function getAllMessagesByFilter(array $data, int $itemsPerPage, int $page): array
    {
        $qb = $this->createQueryBuilder('m');

        if (isset($data['senderId']) && $data['senderId'] !== '') {
            $qb->andWhere('m.sender = :senderId')
               ->setParameter('senderId', (int) $data['senderId']);
        }

        if (isset($data['receiverId']) && $data['receiverId'] !== '') {
            $qb->andWhere('m.receiver = :receiverId')
               ->setParameter('receiverId', (int) $data['receiverId']);
        }

        if (isset($data['participantId']) && $data['participantId'] !== '') {
            $qb->andWhere('m.sender = :participantId OR m.receiver = :participantId')
               ->setParameter('participantId', (int) $data['participantId']);
        }

        if (isset($data['content']) && is_string($data['content']) && $data['content'] !== '') {
            $qb->andWhere('m.content LIKE :content')
               ->setParameter('content', '%' . $data['content'] . '%');
        }

        if (isset($data['createdFrom']) && is_string($data['createdFrom']) && $data['createdFrom'] !== '') {
            try {
                $from = new \DateTimeImmutable($data['createdFrom']);
                $qb->andWhere('m.createdAt >= :createdFrom')
                   ->setParameter('createdFrom', $from);
            } catch (\Throwable) {
            }
        }

        if (isset($data['createdTo']) && is_string($data['createdTo']) && $data['createdTo'] !== '') {
            try {
                $to = new \DateTimeImmutable($data['createdTo']);
                $qb->andWhere('m.createdAt <= :createdTo')
                   ->setParameter('createdTo', $to);
            } catch (\Throwable) {
            }
        }

        $qb->orderBy('m.createdAt', 'DESC');

        $itemsPerPage = max(1, min(100, $itemsPerPage));
        $page = max(1, $page);

        $paginator = new Paginator($qb);
        $totalItems = count($paginator);
        $totalPageCount = (int) ceil($totalItems / $itemsPerPage);

        $qb->setFirstResult($itemsPerPage * ($page - 1))
           ->setMaxResults($itemsPerPage);

        return [
            'messages' => iterator_to_array($paginator->getIterator()),
            'totalPageCount' => $totalPageCount,
            'totalItems' => $totalItems,
        ];
    }
}
