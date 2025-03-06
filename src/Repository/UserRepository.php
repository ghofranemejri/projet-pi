<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Doctrine\ORM\QueryBuilder;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * Récupère et trie les utilisateurs par rôle et par un autre critère (nom, date_inscription, etc.)
     *
     * @param string $role Le rôle à filtrer (par exemple "ROLE_USER" ou "ROLE_ADMIN")
     * @param string $sortBy Le champ de tri (nom, date_inscription, etc.)
     * @param string $order L'ordre de tri (ASC ou DESC)
     * @return QueryBuilder Retourne un QueryBuilder pour paginer les résultats
     */
    public function findUsersByRoleSortedQuery(string $role, string $sortBy = 'nom', string $order = 'ASC'): QueryBuilder
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.roles LIKE :role') // Filtre les utilisateurs par rôle
            ->setParameter('role', '%'.$role.'%') // Recherche de rôle dans la chaîne des rôles
            ->orderBy("u.$sortBy", $order); // Trie par le champ demandé (nom, date_inscription, etc.)
    }

    /**
     * Récupère et trie les utilisateurs par rôle (version retournant un tableau)
     *
     * @param string $role Le rôle à filtrer (par exemple "ROLE_USER" ou "ROLE_ADMIN")
     * @param string $sortBy Le champ de tri (nom, date_inscription)
     * @param string $order L'ordre de tri (ASC ou DESC)
     * @return User[] Retourne une liste d'objets User
     */
    public function findUsersByRoleSorted(string $role, string $sortBy = 'nom', string $order = 'ASC'): array
    {
        return $this->findUsersByRoleSortedQuery($role, $sortBy, $order)
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère et trie les patients par rôle
     *
     * @param string $sortBy Le champ de tri (nom, date_inscription)
     * @param string $order L'ordre de tri (ASC ou DESC)
     * @return QueryBuilder Retourne un QueryBuilder pour paginer les résultats
     */
    public function findPatientsSortedQuery(string $sortBy = 'nom', string $order = 'ASC'): QueryBuilder
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.roles LIKE :role') // Filtre les utilisateurs avec le rôle ROLE_PATIENT
            ->setParameter('role', '%ROLE_PATIENT%') // Recherche de ROLE_PATIENT dans la chaîne des rôles
            ->orderBy("u.$sortBy", $order); // Trie par le champ demandé (nom, date_inscription, etc.)
    }

    /**
     * Récupère et trie les patients (version retournant un tableau)
     *
     * @param string $sortBy Le champ de tri (nom, date_inscription)
     * @param string $order L'ordre de tri (ASC ou DESC)
     * @return User[] Retourne une liste d'objets User (patients)
     */
    public function findPatientsSorted(string $sortBy = 'nom', string $order = 'ASC'): array
    {
        return $this->findPatientsSortedQuery($sortBy, $order)
            ->getQuery()
            ->getResult();
    }
    public function findOneByRole(string $role): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.roles LIKE :role') // Recherche dans le tableau des rôles
            ->setParameter('role', '%"' . $role . '"%') // Le rôle est stocké sous forme de chaîne JSON
            ->setMaxResults(1) // Limite à un seul résultat
            ->getQuery()
            ->getOneOrNullResult();
    }
}
