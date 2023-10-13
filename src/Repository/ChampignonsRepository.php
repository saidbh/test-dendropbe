<?php

namespace App\Repository;

use App\Entity\Champignons;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Champignons|null find($id, $lockMode = null, $lockVersion = null)
 * @method Champignons|null findOneBy(array $criteria, array $orderBy = null)
 * @method Champignons[]    findAll()
 * @method Champignons[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChampignonsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Champignons::class);
    }

    // /**
    //  * @return Champignons[] Returns an array of Champignons objects
    //  */


    public function findByUpdate(Champignons $objet)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.name =:val AND c.id <>' . $objet->getId() . '')
            ->setParameter('val', $objet->getName())
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array
     */
    public function getAllByResineux(): array
    {
        return $this->getEntityManager()
            ->createQuery(
                "SELECT c FROM App\Entity\Champignons c 
                where (c.attaqueR <> '') AND (c.isDeleted = 0) ORDER BY c.name ASC"
            )->execute();
    }

    /**
     * @return array
     */
    public function getAllByFeuillux(): array
    {
        return $this->getEntityManager()
            ->createQuery(
                "SELECT c FROM App\Entity\Champignons c 
                where (c.attaqueF <> '') AND (c.isDeleted = 0) ORDER BY c.name ASC"
            )->execute();
    }

    /**
     * @param $info
     * @return int|mixed|string
     */
    public function search($info)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.name LIKE :val OR c.attaqueF LIKE :val OR c.attaqueR LIKE :val')
            ->andWhere('c.isDeleted = :param')
            ->setParameter('param', 0)
            ->setParameter('val', $info . '%')
            ->setMaxResults(30)
            ->addOrderBy("c.name", "ASC")
            ->getQuery()
            ->getResult();
    }
    /*public function findByExampleField($value)
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

    /*
    public function findOneBySomeField($value): ?Champignons
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
