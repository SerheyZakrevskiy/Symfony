<?php

namespace App\Repository;

use App\Entity\FriendRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

class FriendRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FriendRequest::class);
    }

    public function getAllFriendRequestsByFilter(array $data, int $itemsPerPage, int $page): array
    {
        $qb = $this->createQueryBuilder('fr');

        if (isset($data['fromUserId']) && $data['fromUserId'] !== '') {
            $qb->andWhere('fr.fromUser = :fromUserId')
               ->setParameter('fromUserId', (int) $data['fromUserId']);
        }

        if (isset($data['toUserId']) && $data['toUserId'] !== '') {
            $qb->andWhere('fr.toUser = :toUserId')
               ->setParameter('toUserId', (int) $data['toUserId']);
        }

        if (isset($data['status']) && is_string($data['status']) && $data['status'] !== '') {
            $qb->andWhere('fr.status = :status')
               ->setParameter('status', $data['status']);
        }

        // Опционально: фильтр по датам
        if (isset($data['createdFrom']) && is_string($data['createdFrom']) && $data['createdFrom'] !== '') {
            try {
                $from = new \DateTimeImmutable($data['createdFrom']);
                $qb->andWhere('fr.createdAt >= :createdFrom')
                   ->setParameter('createdFrom', $from);
            } catch (\Throwable) {
            }
        }

        if (isset($data['createdTo']) && is_string($data['createdTo']) && $data['createdTo'] !== '') {
            try {
                $to = new \DateTimeImmutable($data['createdTo']);
                $qb->andWhere('fr.createdAt <= :createdTo')
                   ->setParameter('createdTo', $to);
            } catch (\Throwable) {
            }
        }

        $qb->orderBy('fr.createdAt', 'DESC');

        $itemsPerPage = max(1, min(100, $itemsPerPage));
        $page = max(1, $page);

        $paginator = new Paginator($qb);
        $totalItems = count($paginator);
        $totalPageCount = (int) ceil($totalItems / $itemsPerPage);

        $qb->setFirstResult($itemsPerPage * ($page - 1))
           ->setMaxResults($itemsPerPage);

        return [
            'friendRequests' => iterator_to_array($paginator->getIterator()),
            'totalPageCount' => $totalPageCount,
            'totalItems' => $totalItems,
        ];
    }
}
