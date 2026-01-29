<?php

namespace App\Repository;

use App\Entity\Comment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

class CommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    public function getAllCommentsByFilter(array $data, int $itemsPerPage, int $page): array
    {
        $qb = $this->createQueryBuilder('c');

        if (isset($data['content']) && is_string($data['content']) && $data['content'] !== '') {
            $qb->andWhere('c.content LIKE :content')
               ->setParameter('content', '%' . $data['content'] . '%');
        }

        if (isset($data['authorId']) && $data['authorId'] !== '') {
            $qb->andWhere('c.author = :authorId')
               ->setParameter('authorId', (int) $data['authorId']);
        }

        if (isset($data['postId']) && $data['postId'] !== '') {
            $qb->andWhere('c.post = :postId')
               ->setParameter('postId', (int) $data['postId']);
        }

        $qb->orderBy('c.createdAt', 'DESC');

        $itemsPerPage = max(1, min(100, $itemsPerPage));
        $page = max(1, $page);

        $paginator = new Paginator($qb);
        $totalItems = count($paginator);
        $totalPageCount = (int) ceil($totalItems / $itemsPerPage);

        $qb->setFirstResult($itemsPerPage * ($page - 1))
           ->setMaxResults($itemsPerPage);

        return [
            'comments' => iterator_to_array($paginator->getIterator()),
            'totalPageCount' => $totalPageCount,
            'totalItems' => $totalItems,
        ];
    }
}
