<?php

namespace App\Repository;

use App\Entity\Arbre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Arbre|null find($id, $lockMode = null, $lockVersion = null)
 * @method Arbre|null findOneBy(array $criteria, array $orderBy = null)
 * @method Arbre[]    findAll()
 * @method Arbre[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArbreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Arbre::class);
    }

    // /**
    //  * @return Arbre[] Returns an array of Arbre objects
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
    public function findOneBySomeField($value): ?Arbre
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    public function getNotProxyArbre($arbre)
    {
        return $this->createQueryBuilder('a')
                    ->where('a.id = :id')
                    ->setParameter('id', $arbre->getId())
                    ->getQuery()
                    ->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
    }
}
