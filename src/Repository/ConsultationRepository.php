<?php

namespace App\Repository;

use App\Entity\Consultation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Consultation>
 *
 * @method Consultation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Consultation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Consultation[]    findAll()
 * @method Consultation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConsultationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Consultation::class);
    }

    /**
     * Find consultations by date range
     */
    public function findByDateRange(?string $startDate, ?string $endDate): array
    {
        $qb = $this->createQueryBuilder('c')
            ->orderBy('c.dateConsultation', 'DESC');

        if ($startDate) {
            $qb->andWhere('c.dateConsultation >= :startDate')
               ->setParameter('startDate', new \DateTime($startDate . ' 00:00:00'));
        }

        if ($endDate) {
            $qb->andWhere('c.dateConsultation <= :endDate')
               ->setParameter('endDate', new \DateTime($endDate . ' 23:59:59'));
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Find consultations by doctor
     */
    public function findByDoctor(string $doctorName): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.nomMedecin = :doctorName')
            ->setParameter('doctorName', $doctorName)
            ->orderBy('c.dateConsultation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find consultations by patient
     */
    public function findByPatient(string $patientName): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.nomPatient = :patientName')
            ->setParameter('patientName', $patientName)
            ->orderBy('c.dateConsultation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function searchByPatientName(string $query)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.nomPatient LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('c.dateConsultation', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }

    public function findByFilters(?string $search, ?string $startDate, ?string $endDate): array
    {
        $qb = $this->createQueryBuilder('c')
            ->orderBy('c.dateConsultation', 'DESC');

        if ($search) {
            $qb->andWhere('LOWER(c.nomPatient) LIKE LOWER(:search) OR LOWER(c.nomMedecin) LIKE LOWER(:search)')
               ->setParameter('search', '%' . $search . '%');
        }

        if ($startDate) {
            $qb->andWhere('c.dateConsultation >= :startDate')
               ->setParameter('startDate', new \DateTime($startDate . ' 00:00:00'));
        }

        if ($endDate) {
            $qb->andWhere('c.dateConsultation <= :endDate')
               ->setParameter('endDate', new \DateTime($endDate . ' 23:59:59'));
        }

        return $qb->getQuery()->getResult();
    }

    public function findMostFrequentPatients(int $limit =2): array
    {
        $qb = $this->createQueryBuilder('c')
            ->select('f.id, f.nomPatient as name, COUNT(c.id) as consultation_count')
            ->join('c.fiches', 'f')
            ->groupBy('f.id', 'f.nomPatient')
            ->orderBy('consultation_count', 'DESC')
            ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    public function getTodayConsultations(\DateTime $today): int
    {
        $qb = $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.dateConsultation >= :today')
            ->andWhere('c.dateConsultation < :tomorrow')
            ->setParameter('today', $today, Types::DATETIME_MUTABLE)
            ->setParameter('tomorrow', $today->modify('+1 day'), Types::DATETIME_MUTABLE);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function getConsultationsCountByPeriod(string $start, string $end): int
    {
        $conn = $this->getEntityManager()->getConnection();
        
        $sql = '
            SELECT COUNT(id) as count 
            FROM consultation 
            WHERE date_consultation >= :start 
            AND date_consultation < :end
        ';
        
        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery([
            'start' => $start,
            'end' => $end,
        ]);
        
        return (int) $result->fetchOne();
    }
}
