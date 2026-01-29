<?php

namespace App\Repository;

use App\Entity\Follow;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

class FollowRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Follow::class);
    }

    public function getAllFollowsByFilter(array $data, int $itemsPerPage, int $page): array
    {
        $qb = $this->createQueryBuilder('f');

        if (isset($data['followerId']) && $data['followerId'] !== '') {
            $qb->andWhere('f.follower = :followerId')
               ->setParameter('followerId', (int) $data['followerId']);
        }

        if (isset($data['followingId']) && $data['followingId'] !== '') {
            $qb->andWhere('f.following = :followingId')
               ->setParameter('followingId', (int) $data['followingId']);
        }

        if (isset($data['createdFrom']) && is_string($data['createdFrom']) && $data['createdFrom'] !== '') {
            try {
                $from = new \DateTimeImmutable($data['createdFrom']);
                $qb->andWhere('f.createdAt >= :createdFrom')
                   ->setParameter('createdFrom', $from);
            } catch (\Throwable) {
            }
        }

        if (isset($data['createdTo']) && is_string($data['createdTo']) && $data['createdTo'] !== '') {
            try {
                $to = new \DateTimeImmutable($data['createdTo']);
                $qb->andWhere('f.createdAt <= :createdTo')
                   ->setParameter('createdTo', $to);
            } catch (\Throwable) {
            }
        }

        $qb->orderBy('f.createdAt', 'DESC');

        $itemsPerPage = max(1, min(100, $itemsPerPage));
        $page = max(1, $page);

        $paginator = new Paginator($qb);
        $totalItems = count($paginator);
        $totalPageCount = (int) ceil($totalItems / $itemsPerPage);

        $qb->setFirstResult($itemsPerPage * ($page - 1))
           ->setMaxResults($itemsPerPage);

        return [
            'follows' => iterator_to_array($paginator->getIterator()),
            'totalPageCount' => $totalPageCount,
            'totalItems' => $totalItems,
        ];
    }
}
