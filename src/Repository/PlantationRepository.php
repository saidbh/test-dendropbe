<?php

namespace App\Repository;

use App\Entity\Plantation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Plantation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Plantation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Plantation[]    findAll()
 * @method Plantation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlantationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Plantation::class);
    }


    public function findAllPlantation($page, $limit,$user)
    {
        $sql= $this->createQueryBuilder('p')
            ->select('p')
            ->where('p.userAdded = :id')
            ->setParameter('id', $user->getId())
            ->orderBy('p.id', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
        $count = $this->createQueryBuilder('p')
            ->select('COUNT(p)')
            ->where('p.userAdded = :id')
            ->setParameter('id', $user->getId())
            ->getQuery()
            ->getResult()[0][1];

        return ["data" => $sql, "count" => $count];
        
    }

    // /**
    //  * @return Plantation[] Returns an array of Plantation objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Plantation
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}