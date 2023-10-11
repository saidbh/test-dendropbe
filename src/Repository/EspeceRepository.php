<?php

namespace App\Repository;

use App\Entity\Espece;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Espece|null find($id, $lockMode = null, $lockVersion = null)
 * @method Espece|null findOneBy(array $criteria, array $orderBy = null)
 * @method Espece[]    findAll()
 * @method Espece[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EspeceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Espece::class);
    }

    // /**
    //  * @return Espece[] Returns an array of Espece objects
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
    public function findByUpdate(Espece $espece)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.name =:val AND e.genre =:valgenre AND e.cultivar =:valcultivar AND e.id <>' . $espece->getId() . '')
            ->setParameter('val', $espece->getName())
            ->setParameter('valgenre', $espece->getGenre())
            ->setParameter('valcultivar', $espece->getCultivar())
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Espece[] Returns an array of Espece objects
     */
    /*public function findByCustomStatic($info)
    {
        $criteria = Criteria::create()
            ->orderBy(['genre', 'DESC']);
        return $this->findByCustom($info)->matching($criteria);
        /*$criteria = Criteria::create()
            ->andWhere(Criteria::expr()->gt('genre', 20))
            ->orderBy(array(
                "genre" => "ASC",
                "name" => "ASC",
                "cultivar" => "ASC"
            ));

    }*/
    /**
     * @return int|mixed|string
     */
    public function findByCriteria()
    {
        $data = false;
        return $this->createQueryBuilder('e')
            ->andWhere('e.isDeleted = :val')
            ->setParameter('val', 0)
            ->addOrderBy("e.genre", "ASC")
            ->addOrderBy("e.name", "ASC")
            ->addOrderBy("e.cultivar", "ASC")
            ->getQuery()
            ->getResult();
    }

    public function findByCustom($info)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.name LIKE :val OR e.cultivar LIKE :val OR e.genre LIKE :val')
            ->andWhere('e.isDeleted = :deleted')
            ->setParameter('deleted', 0)
            ->setParameter('val', $info . '%')
            ->setMaxResults(30)
            ->addOrderBy("e.genre", "ASC")
            ->addOrderBy("e.name", "ASC")
            ->addOrderBy("e.cultivar", "ASC")
            ->getQuery()
            ->getResult();
    }

    public function findByGenre()
    {
        return $this->createQueryBuilder('e')
            ->select('DISTINCT e.genre')
            ->orderBy('e.genre', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /*
    public function findOneBySomeField($value): ?Espece
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
