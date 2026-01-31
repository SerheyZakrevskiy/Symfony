<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function getAllUsersByFilter(array $data, int $itemsPerPage, int $page): array
    {
        $qb = $this->createQueryBuilder('u');

        if (isset($data['username']) && is_string($data['username']) && $data['username'] !== '') {
            $qb->andWhere('u.username LIKE :username')
               ->setParameter('username', '%' . $data['username'] . '%');
        }

        if (isset($data['email']) && is_string($data['email']) && $data['email'] !== '') {
            $qb->andWhere('u.email LIKE :email')
               ->setParameter('email', '%' . $data['email'] . '%');
        }

        if (isset($data['bio']) && is_string($data['bio']) && $data['bio'] !== '') {
            $qb->andWhere('u.bio LIKE :bio')
               ->setParameter('bio', '%' . $data['bio'] . '%');
        }

        if (isset($data['avatarUrl']) && is_string($data['avatarUrl']) && $data['avatarUrl'] !== '') {
            $qb->andWhere('u.avatarUrl LIKE :avatarUrl')
               ->setParameter('avatarUrl', '%' . $data['avatarUrl'] . '%');
        }

        if (isset($data['createdFrom']) && is_string($data['createdFrom']) && $data['createdFrom'] !== '') {
            try {
                $from = new \DateTimeImmutable($data['createdFrom']);
                $qb->andWhere('u.createdAt >= :createdFrom')
                   ->setParameter('createdFrom', $from);
            } catch (\Throwable) {
            }
        }

        if (isset($data['createdTo']) && is_string($data['createdTo']) && $data['createdTo'] !== '') {
            try {
                $to = new \DateTimeImmutable($data['createdTo']);
                $qb->andWhere('u.createdAt <= :createdTo')
                   ->setParameter('createdTo', $to);
            } catch (\Throwable) {
            }
        }

        $qb->orderBy('u.createdAt', 'DESC');

        $itemsPerPage = max(1, min(100, $itemsPerPage));
        $page = max(1, $page);

        $paginator = new Paginator($qb);
        $totalItems = count($paginator);
        $totalPageCount = (int) ceil($totalItems / $itemsPerPage);

        $qb->setFirstResult($itemsPerPage * ($page - 1))
           ->setMaxResults($itemsPerPage);

        return [
            'users' => iterator_to_array($paginator->getIterator()),
            'totalPageCount' => $totalPageCount,
            'totalItems' => $totalItems,
        ];
    }
}
