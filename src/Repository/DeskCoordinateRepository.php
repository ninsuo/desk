<?php

namespace App\Repository;

use App\Entity\DeskCoordinate;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method DeskCoordinate|null find($id, $lockMode = null, $lockVersion = null)
 * @method DeskCoordinate|null findOneBy(array $criteria, array $orderBy = null)
 * @method DeskCoordinate[]    findAll()
 * @method DeskCoordinate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DeskCoordinateRepository extends BaseRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, DeskCoordinate::class);
    }

    // /**
    //  * @return DeskCoordinate[] Returns an array of DeskCoordinate objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?DeskCoordinate
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
