<?php

namespace App\Repository;

use App\Entity\FormPost;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FormPost>
 */
class FormPostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FormPost::class);
    }
// src/Repository/FormPostRepository.php
   public function findAllWithReponses(): array
   {
    return $this->createQueryBuilder('f')
        ->leftJoin('f.reponses', 'r') // Join with responses
        ->addSelect('r') // Fetch responses
        ->getQuery()
        ->getResult();
  }
  // src/Repository/FormPostRepository.php

  public function searchByName(string $query): array
{
    return $this->createQueryBuilder('p')
        ->andWhere('p.nom LIKE :query') // Recherche uniquement par nom
        ->setParameter('query', '%' . $query . '%')
        ->orderBy('p.date', 'DESC') // Trier par date
        ->getQuery()
        ->getResult();
}

  


    //    /**
    //     * @return FormPost[] Returns an array of FormPost objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('f.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?FormPost
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
