<?php

namespace App\Repository;

use App\Entity\Travaux;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Travaux|null find($id, $lockMode = null, $lockVersion = null)
 * @method Travaux|null findOneBy(array $criteria, array $orderBy = null)
 * @method Travaux[]    findAll()
 * @method Travaux[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TravauxRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Travaux::class);
    }

    // /**
    //  * @return Travaux[] Returns an array of Travaux objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */
    public function add(Travaux $travaux)
    {
        $this->getEntityManager()->persist($travaux);
        $this->getEntityManager()->flush();
    }
    /*
    public function findOneBySomeField($value): ?Travaux
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
