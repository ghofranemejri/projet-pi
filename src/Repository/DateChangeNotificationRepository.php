<?php

namespace App\Repository;

use App\Entity\DateChangeNotification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DateChangeNotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DateChangeNotification::class);
    }

    public function findUnreadByFiche($fiche)
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.fiche = :fiche')
            ->andWhere('n.isRead = :isRead')
            ->setParameter('fiche', $fiche)
            ->setParameter('isRead', false)
            ->orderBy('n.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
