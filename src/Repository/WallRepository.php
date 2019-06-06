<?php

namespace App\Repository;

use App\Entity\Wall;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Wall|null find($id, $lockMode = null, $lockVersion = null)
 * @method Wall|null findOneBy(array $criteria, array $orderBy = null)
 * @method Wall[]    findAll()
 * @method Wall[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WallRepository extends BaseRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Wall::class);
    }

    // /**
    //  * @return Wall[] Returns an array of Wall objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('w.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Wall
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
