<?php

namespace App\Repository;

use App\Entity\SavedPost;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

class SavedPostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SavedPost::class);
    }

    public function getAllSavedPostsByFilter(array $data, int $itemsPerPage, int $page): array
    {
        $qb = $this->createQueryBuilder('sp');

        if (isset($data['authorId']) && $data['authorId'] !== '') {
            $qb->andWhere('sp.author = :authorId')
               ->setParameter('authorId', (int) $data['authorId']);
        }

        if (isset($data['postId']) && $data['postId'] !== '') {
            $qb->andWhere('sp.post = :postId')
               ->setParameter('postId', (int) $data['postId']);
        }

        if (isset($data['savedFrom']) && is_string($data['savedFrom']) && $data['savedFrom'] !== '') {
            try {
                $from = new \DateTimeImmutable($data['savedFrom']);
                $qb->andWhere('sp.savedAt >= :savedFrom')
                   ->setParameter('savedFrom', $from);
            } catch (\Throwable) {
            }
        }

        if (isset($data['savedTo']) && is_string($data['savedTo']) && $data['savedTo'] !== '') {
            try {
                $to = new \DateTimeImmutable($data['savedTo']);
                $qb->andWhere('sp.savedAt <= :savedTo')
                   ->setParameter('savedTo', $to);
            } catch (\Throwable) {
            }
        }

        $qb->orderBy('sp.savedAt', 'DESC');

        $itemsPerPage = max(1, min(100, $itemsPerPage));
        $page = max(1, $page);

        $paginator = new Paginator($qb);
        $totalItems = count($paginator);
        $totalPageCount = (int) ceil($totalItems / $itemsPerPage);

        $qb->setFirstResult($itemsPerPage * ($page - 1))
           ->setMaxResults($itemsPerPage);

        return [
            'savedPosts' => iterator_to_array($paginator->getIterator()),
            'totalPageCount' => $totalPageCount,
            'totalItems' => $totalItems,
        ];
    }
}
