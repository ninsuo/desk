<?php

namespace App\Repository;

use App\Entity\Booking;
use App\Entity\Desk;
use App\Entity\Room;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Booking|null find($id, $lockMode = null, $lockVersion = null)
 * @method Booking|null findOneBy(array $criteria, array $orderBy = null)
 * @method Booking[]    findAll()
 * @method Booking[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BookingRepository extends BaseRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Booking::class);
    }

    public function getRoomBookingsBetween(Room $room, \DateTime $start, \DateTime $end)
    {
        return $this->createQueryBuilder('b')
            ->join('b.desk', 'd')
            ->where('d.room = :room')
            ->setParameter('room', $room)
            ->andWhere('
                   b.start BETWEEN :start  AND :end 
                OR b.end   BETWEEN :start  AND :end
                OR :start  BETWEEN b.start AND b.end                
                OR :end    BETWEEN b.start AND b.end                
             ')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getResult();
    }

    public function getDeskBookingsBetween(Desk $desk, \DateTime $start, \DateTime $end)
    {
        return $this->createQueryBuilder('b')
            ->where('b.desk = :desk')
            ->setParameter('desk', $desk)
            ->andWhere('
                   b.start BETWEEN :start  AND :end 
                OR b.end   BETWEEN :start  AND :end
                OR :start  BETWEEN b.start AND b.end                
                OR :end    BETWEEN b.start AND b.end                
             ')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getResult();
    }

    public function getNonConfirmedBookingsBefore(\DateTime $limit) : array
    {
        return $this->createQueryBuilder('b')
            ->where('b.start < :limit')
            ->setParameter('limit', $limit)
            ->andWhere('b.confirmed = false')
            ->getQuery()
            ->getResult();
    }
}
