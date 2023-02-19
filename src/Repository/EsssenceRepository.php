<?php

namespace App\Repository;

use App\Entity\Esssence;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Esssence|null find($id, $lockMode = null, $lockVersion = null)
 * @method Esssence|null findOneBy(array $criteria, array $orderBy = null)
 * @method Esssence[]    findAll()
 * @method Esssence[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EsssenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Esssence::class);
    }

    // /**
    //  * @return Esssence[] Returns an array of Esssence objects
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
    public function findOneBySomeField($value): ?Esssence
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
