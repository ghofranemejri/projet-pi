<?php

namespace App\Repository;

use App\Entity\Review;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Review>
 */
class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
    }

    public function getAverageRatingForDoctor(string $doctorName): float
    {
        $result = $this->createQueryBuilder('r')
            ->select('AVG(r.rating) as avgRating')
            ->where('r.doctorName = :doctorName')
            ->setParameter('doctorName', $doctorName)
            ->getQuery()
            ->getSingleScalarResult();

        return $result ? round($result, 1) : 0;
    }

    public function findByDoctor(string $doctorName, int $limit = 5): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.doctorName = :doctorName')
            ->setParameter('doctorName', $doctorName)
            ->orderBy('r.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
