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

    /**
     * Récupère tous les posts avec leurs réponses associées.
     */
    public function findAllWithReponses(): array
    {
        return $this->createQueryBuilder('f')
            ->leftJoin('f.reponses', 'r') // Jointure avec la table des réponses
            ->addSelect('r') // Sélectionne les réponses aussi
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche les posts par nom.
     */
    public function searchByName(string $query): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.nom LIKE :query') // Recherche uniquement par nom
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('p.date', 'DESC') // Trie par date descendante
            ->getQuery()
            ->getResult();
    }
}
