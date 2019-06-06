<?php

namespace App\Repository;

use App\Entity\WallCoordinate;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method WallCoordinate|null find($id, $lockMode = null, $lockVersion = null)
 * @method WallCoordinate|null findOneBy(array $criteria, array $orderBy = null)
 * @method WallCoordinate[]    findAll()
 * @method WallCoordinate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WallCoordinateRepository extends BaseRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, WallCoordinate::class);
    }

    // /**
    //  * @return WallCoordinate[] Returns an array of WallCoordinate objects
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
    public function findOneBySomeField($value): ?WallCoordinate
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
