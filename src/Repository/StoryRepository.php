<?php

namespace App\Repository;

use App\Entity\Story;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

class StoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Story::class);
    }

    public function getAllStoriesByFilter(array $data, int $itemsPerPage, int $page): array
    {
        $qb = $this->createQueryBuilder('s');

        if (isset($data['authorId']) && $data['authorId'] !== '') {
            $qb->andWhere('s.author = :authorId')
               ->setParameter('authorId', (int) $data['authorId']);
        }

        if (isset($data['caption']) && is_string($data['caption']) && $data['caption'] !== '') {
            $qb->andWhere('s.caption LIKE :caption')
               ->setParameter('caption', '%' . $data['caption'] . '%');
        }

        if (isset($data['mediaUrl']) && is_string($data['mediaUrl']) && $data['mediaUrl'] !== '') {
            $qb->andWhere('s.mediaUrl LIKE :mediaUrl')
               ->setParameter('mediaUrl', '%' . $data['mediaUrl'] . '%');
        }

        if (isset($data['active']) && $data['active'] !== '') {
            $activeRaw = $data['active'];
            $active = filter_var($activeRaw, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
            if ($active === null) {
                $active = ((string)$activeRaw === '1');
            }

            if ($active) {
                $qb->andWhere('s.expiresAt > :now')
                   ->setParameter('now', new \DateTimeImmutable());
            } else {
                $qb->andWhere('s.expiresAt <= :now')
                   ->setParameter('now', new \DateTimeImmutable());
            }
        }

        if (isset($data['createdFrom']) && is_string($data['createdFrom']) && $data['createdFrom'] !== '') {
            try {
                $from = new \DateTimeImmutable($data['createdFrom']);
                $qb->andWhere('s.createdAt >= :createdFrom')
                   ->setParameter('createdFrom', $from);
            } catch (\Throwable) {
            }
        }

        if (isset($data['createdTo']) && is_string($data['createdTo']) && $data['createdTo'] !== '') {
            try {
                $to = new \DateTimeImmutable($data['createdTo']);
                $qb->andWhere('s.createdAt <= :createdTo')
                   ->setParameter('createdTo', $to);
            } catch (\Throwable) {
            }
        }

        $qb->orderBy('s.createdAt', 'DESC');

        $itemsPerPage = max(1, min(100, $itemsPerPage));
        $page = max(1, $page);

        $paginator = new Paginator($qb);
        $totalItems = count($paginator);
        $totalPageCount = (int) ceil($totalItems / $itemsPerPage);

        $qb->setFirstResult($itemsPerPage * ($page - 1))
           ->setMaxResults($itemsPerPage);

        return [
            'stories' => iterator_to_array($paginator->getIterator()),
            'totalPageCount' => $totalPageCount,
            'totalItems' => $totalItems,
        ];
    }
}
