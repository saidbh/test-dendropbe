<?php

namespace App\Repository;

use App\Entity\Inventaire;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Inventaire|null find($id, $lockMode = null, $lockVersion = null)
 * @method Inventaire|null findOneBy(array $criteria, array $orderBy = null)
 * @method Inventaire[]    findAll()
 * @method Inventaire[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InventaireRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Inventaire::class);
    }

    public function findInventaireByEspece($object)
    {
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
            'SELECT i
            FROM App\Entity\Inventaire i, App\Entity\Arbre a, App\Entity\Espece e
            WHERE i.arbre = a.id AND a.id = a.espece AND a.espece = e.id AND e.id = :id
            ORDER BY e.id ASC'
        )->setParameter('id', $object->getId());

        // returns an array of Product objects
        return $query->execute();

    }

    public function findInventoryTreeByAdrOrTown(string $infos, User $user)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT i FROM App\Entity\Inventaire i, App\Entity\Arbre a, App\Entity\User u, App\Entity\Groupe g, App\Entity\Epaysage e
                WHERE ((i.user=u.id AND 
                u.id=:id AND 
                u.groupe=g.id AND 
                g.id=:groupeId) OR g.groupeType=:type) 
                AND (
                (i.arbre = a.id AND (a.address LIKE :val OR a.ville LIKE :val) ) OR (i.epaysage = e.id AND (e.address LIKE :val OR e.ville LIKE :val)))
                ORDER BY i.id ASC')
            ->setParameter('id', $user->getId())->setParameter('groupeId', $user->getGroupe()->getId())
            ->setParameter('type', 'DENDROMAP')
            ->setParameter('val', $infos . '%')
            ->execute();
    }

    /**
     * @param $object
     * @return int|mixed|string
     */
    public function findInventaireByArbre($object)
    {
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
            'SELECT i
            FROM App\Entity\Inventaire i, App\Entity\Arbre a
            WHERE i.arbre = a.id AND a.id =:id
            ORDER BY e.id ASC'
        )->setParameter('id', $object->getId());
        // returns an array of Product objects
        return $query->execute();
    }

    /**
     * @param User $user
     * @return array
     */
    public function queryInventoryByGroupe(User $user): array
    {

        if (strtoupper($user->getGroupe()->getGroupeType() !== 'DENDROMAP')) {
            return $this->getEntityManager()
                ->createQuery(
                    'SELECT i FROM App\Entity\Inventaire i
                     where i.user in (select u.id FROM App\Entity\User u where u.groupe=:groupeId) ORDER BY i.id DESC')
                ->setParameter('groupeId', $user->getGroupe()->getId())
                ->execute();

        } else {
            return $this->findBy([], ['id' => 'DESC']);
        }
    }

    /**
     * @param User $user
     * @param bool $isFinished
     * @return array
     */
    public function queryInventoryByGroupeIsFinished(User $user, bool $isFinished): array
    {
        if (strtoupper($user->getGroupe()->getGroupeType() !== 'DENDROMAP')) {
            return $this->getEntityManager()
                ->createQuery(
                    'SELECT i FROM App\Entity\Inventaire i
                     where i.user in (select u.id FROM App\Entity\User u where u.groupe=:groupeId) AND i.isFinished = :isFinished ORDER BY i.id DESC')
                ->setParameter('groupeId', $user->getGroupe()->getId())
                ->setParameter('isFinished', $isFinished)
                ->execute();

        } else {
            return $this->findBy(["isFinished" => $isFinished], ['id' => 'DESC']);
        }
    }

    /**
     * @param User $user
     * @param bool $isFinished
     * @param string $filter
     * @return array
     */
    public function queryInventoryByGroupeIsFinishedFilter(User $user, bool $isFinished, string $filter): array
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT i FROM App\Entity\Inventaire i, App\Entity\User u, App\Entity\Groupe g, App\Entity\Arbre a, App\Entity\Epaysage e
                WHERE ((i.user=u.id AND 
                u.id=:id AND 
                u.groupe=g.id AND 
                g.id=:groupeId) OR g.groupeType=:type) AND i.isFinished =:finished  AND (
                (i.arbre = a.id AND (a.address LIKE :val OR a.ville LIKE :val) ) OR (i.epaysage = e.id AND (e.address LIKE :val OR e.ville LIKE :val)))
            ')->setParameter('id', $user->getId())->setParameter('groupeId', $user->getGroupe()->getId())
            ->setParameter('type', 'DENDROMAP')
            ->setParameter('finished', $isFinished)
            ->setParameter('val', $filter . '%')
            ->execute();
    }

}
