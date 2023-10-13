<?php

namespace App\Repository;

use App\Entity\Nuisible;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Nuisible|null find($id, $lockMode = null, $lockVersion = null)
 * @method Nuisible|null findOneBy(array $criteria, array $orderBy = null)
 * @method Nuisible[]    findAll()
 * @method Nuisible[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NuisibleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Nuisible::class);
    }

    // /**
    //  * @return Nuisible[] Returns an array of Nuisible objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('n.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */
    public function findOneByUpdate(Nuisible $nuisible) {
        // nuisible UPDATE
        return $this->createQueryBuilder('n')
            ->andWhere('n.name = :val AND n.id <>'.$nuisible->getId().'')
            ->setParameter('val', $nuisible->getName())
            ->getQuery()
            ->getResult();
    }
    /*
    public function findOneBySomeField($value): ?Nuisible
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
