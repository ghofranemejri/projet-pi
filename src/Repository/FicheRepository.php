<?php

namespace App\Repository;

use App\Entity\Fiche;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Fiche>
 *
 * @method Fiche|null find($id, $lockMode = null, $lockVersion = null)
 * @method Fiche|null findOneBy(array $criteria, array $orderBy = null)
 * @method Fiche[]    findAll()
 * @method Fiche[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FicheRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Fiche::class);
    }

    /**
     * Find fiches by status
     */
    public function findByStatus(string $status): array
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.status = :status')
            ->setParameter('status', $status)
            ->orderBy('f.date', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find fiches by consultation
     */
    public function findByConsultation(int $consultationId): array
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.consultation = :consultationId')
            ->setParameter('consultationId', $consultationId)
            ->orderBy('f.date', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find fiches by doctor
     */
    public function findByDoctor(string $doctorName): array
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.nomMedecin = :doctorName')
            ->setParameter('doctorName', $doctorName)
            ->orderBy('f.date', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find fiches by patient
     */
    public function findByPatient(string $patientName): array
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.nomPatient = :patientName')
            ->setParameter('patientName', $patientName)
            ->orderBy('f.date', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getTodayFiches(\DateTime $today): int
    {
        return $this->createQueryBuilder('f')
            ->select('COUNT(f.id)')
            ->where('f.date >= :today')
            ->andWhere('f.date < :tomorrow')
            ->setParameter('today', $today, Types::DATETIME_MUTABLE)
            ->setParameter('tomorrow', $today->modify('+1 day'), Types::DATETIME_MUTABLE)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
