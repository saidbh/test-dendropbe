<?php

namespace App\Repository;

use App\Entity\Epaysage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Epaysage|null find($id, $lockMode = null, $lockVersion = null)
 * @method Epaysage|null findOneBy(array $criteria, array $orderBy = null)
 * @method Epaysage[]    findAll()
 * @method Epaysage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EpaysageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Epaysage::class);
    }

    public function getTotalWoodedSpaceByTime($todayDate)
    {
        return $this->createQueryBuilder('e')
                    ->where('e.createdAt >= :todayDate')
                    ->setParameter('todayDate', $todayDate)
                    ->getQuery()
                    ->getResult();
    }

    public function findByDateRange(\DateTimeInterface $startDate, \DateTimeInterface $endDate)
    {
        $qb = $this->createQueryBuilder('e');
        $qb->select('SUM(e.area) AS area')
            ->where($qb->expr()->between('e.createdAt', ':startDate', ':endDate'))
            ->orWhere($qb->expr()->between('e.updatedAt', ':startDate', ':endDate'))
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate);

        return $qb->getQuery()->getResult();
    }
    public function findSumWoodedSpace()
    {
        return $this->createQueryBuilder('e')
                    ->select('SUM(e.area) AS area')
                    ->getQuery()->getResult();
    }


    // /**
    //  * @return Epaysage[] Returns an array of Epaysage objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Epaysage
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
