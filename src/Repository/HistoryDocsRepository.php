<?php

namespace App\Repository;

use App\Entity\HistoryDocs;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 *
 * @method HistoryDocs|null find($id, $lockMode = null, $lockVersion = null)
 * @method HistoryDocs|null findOneBy(array $criteria, array $orderBy = null)
 * @method HistoryDocs[]    findAll()
 * @method HistoryDocs[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HistoryDocsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HistoryDocs::class);
    }

    // /**
    //  * @return HistoryDocs[] Returns an array of HistoryDocs objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('h.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?HistoryDocs
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
