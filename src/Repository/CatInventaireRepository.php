<?php

namespace App\Repository;

use App\Entity\CatInventaire;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method CatInventaire|null find($id, $lockMode = null, $lockVersion = null)
 * @method CatInventaire|null findOneBy(array $criteria, array $orderBy = null)
 * @method CatInventaire[]    findAll()
 * @method CatInventaire[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CatInventaireRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CatInventaire::class);
    }

//    /**
//     * @return CatInventaire[] Returns an array of CatInventaire objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */
    public function findByUpdate(CatInventaire $cat) {
        return $this->createQueryBuilder('c')
            ->andWhere('c.name = :val AND c.id <>'.$cat->getId().'')
            ->setParameter('val', $cat->getName())
            ->getQuery()
            ->getResult();
    }

    /*
    public function findOneBySomeField($value): ?CatInventaire
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
