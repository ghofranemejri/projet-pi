<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function findByNom(string $nom): ?Product
    {
        return $this->findOneBy(['nom' => $nom]);
    }
    public function findByQuantiteInf(int $seuil): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.quantite < :seuil')
            ->setParameter('seuil', $seuil)
            ->getQuery()
            ->getResult();
    }
}
