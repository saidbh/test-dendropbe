<?php

namespace App\Repository;

use App\Entity\AttaqueF;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method AttaqueF|null find($id, $lockMode = null, $lockVersion = null)
 * @method AttaqueF|null findOneBy(array $criteria, array $orderBy = null)
 * @method AttaqueF[]    findAll()
 * @method AttaqueF[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AttaqueFRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AttaqueF::class);
    }

    // /**
    //  * @return AttaqueF[] Returns an array of AttaqueF objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?AttaqueF
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
