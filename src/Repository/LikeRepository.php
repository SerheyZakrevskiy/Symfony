<?php

namespace App\Repository;

use App\Entity\Like;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

class LikeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Like::class);
    }

    public function getAllLikesByFilter(array $data, int $itemsPerPage, int $page): array
    {
        $qb = $this->createQueryBuilder('l');

        if (isset($data['likedById']) && $data['likedById'] !== '') {
            $qb->andWhere('l.likedBy = :likedById')
               ->setParameter('likedById', (int) $data['likedById']);
        }

        if (isset($data['postId']) && $data['postId'] !== '') {
            $qb->andWhere('l.post = :postId')
               ->setParameter('postId', (int) $data['postId']);
        }

        if (isset($data['createdFrom']) && is_string($data['createdFrom']) && $data['createdFrom'] !== '') {
            try {
                $from = new \DateTimeImmutable($data['createdFrom']);
                $qb->andWhere('l.createdAt >= :createdFrom')
                   ->setParameter('createdFrom', $from);
            } catch (\Throwable) {
            }
        }

        if (isset($data['createdTo']) && is_string($data['createdTo']) && $data['createdTo'] !== '') {
            try {
                $to = new \DateTimeImmutable($data['createdTo']);
                $qb->andWhere('l.createdAt <= :createdTo')
                   ->setParameter('createdTo', $to);
            } catch (\Throwable) {
            }
        }

        $qb->orderBy('l.createdAt', 'DESC');

        $itemsPerPage = max(1, min(100, $itemsPerPage));
        $page = max(1, $page);

        $paginator = new Paginator($qb);
        $totalItems = count($paginator);
        $totalPageCount = (int) ceil($totalItems / $itemsPerPage);

        $qb->setFirstResult($itemsPerPage * ($page - 1))
           ->setMaxResults($itemsPerPage);

        return [
            'likes' => iterator_to_array($paginator->getIterator()),
            'totalPageCount' => $totalPageCount,
            'totalItems' => $totalItems,
        ];
    }
}
