<?php

namespace App\Repository;

use App\Entity\HistoryDocs;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<HistoryDocs>
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

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(HistoryDocs $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(HistoryDocs $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
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
