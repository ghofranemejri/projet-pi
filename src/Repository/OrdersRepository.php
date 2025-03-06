<?php

namespace App\Repository;

use App\Entity\Orders;
use App\Entity\Product;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\ResultSetMapping;
class OrdersRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Orders::class);
    }

    public function findByUser(int $userId): array
    {
        return $this->findBy(['user' => $userId]);
    }

    public function findBestSeller(): ?Product
    {
        $rsm = new ResultSetMapping();
        $rsm->addEntityResult(Product::class, 'p');
        $rsm->addFieldResult('p', 'id', 'id');
        $rsm->addFieldResult('p', 'prix', 'prix');
        $rsm->addFieldResult('p', 'quantite', 'quantite');
        $rsm->addFieldResult('p', 'image', 'image');

        $sql = "
            SELECT p.id, p.prix, p.quantite, p.image
            FROM orders o
            JOIN product p ON o.product_id = p.id
            WHERE o.statut = 'traite'
            GROUP BY p.id
            ORDER BY SUM(o.quantite) DESC
            LIMIT 1
        ";

        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
        return $query->getOneOrNullResult();
    }
}
