<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BookingRepository")
 * @ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 */
class Booking
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Desk", inversedBy="bookings")
     * @ORM\JoinColumn(nullable=false)
     */
    private $desk;

    /**
     * @ORM\Column(type="datetime")
     */
    private $start;

    /**
     * @ORM\Column(type="datetime")
     */
    private $end;

    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private $person;

    /**
     * @ORM\Column(type="boolean")
     */
    private $confirmed;

    public function getId() : ?int
    {
        return $this->id;
    }

    public function getDesk() : ?Desk
    {
        return $this->desk;
    }

    public function setDesk(?Desk $desk) : self
    {
        $this->desk = $desk;

        return $this;
    }

    public function getStart() : ?\DateTimeInterface
    {
        return $this->start;
    }

    public function setStart(\DateTimeInterface $start) : self
    {
        $this->start = $start;

        return $this;
    }

    public function getEnd() : ?\DateTimeInterface
    {
        return $this->end;
    }

    public function setEnd(\DateTimeInterface $end) : self
    {
        $this->end = $end;

        return $this;
    }

    static public function between(int $n, int $a, int $b)
    {
        return (($a < $b) ? (($n >= $a) && ($n <= $b)) : (($n >= $b) && ($n <= $a)));
    }

    static public function betweenDates(\DateTime $n, \DateTime $a, \DateTime $b)
    {
        return self::between($n->getTimestamp(), $a->getTimestamp(), $b->getTimestamp());
    }

    static public function intersect(int $fromA, int $fromB, int $toA, int $toB)
    {
        return self::between($fromA, $toA, $toB)
               || self::between($fromB, $toA, $toB)
               || self::between($toA, $fromA, $fromB)
               || self::between($toB, $fromA, $fromB);
    }

    static public function intersectDates(\DateTime $fromA, \DateTime $fromB, \DateTime $toA, \DateTime $toB)
    {
        return self::intersect(
            $fromA->getTimestamp() + 1,
            $fromB->getTimestamp(),
            $toA->getTimestamp() + 1,
            $toB->getTimestamp());
    }

    static public function getDuration(int $to, ?int $from = null)
    {
        if (null === $from) {
            $from = time();
        }

        $duration = abs($to - $from);
        $hours    = intval($duration / 3600);
        $mins     = intval($duration % 3600 / 60);

        return ($hours ? $hours.'h ' : '').$mins.'min'.($mins > 1 ? 's' : '');
    }

    static public function getDurationDates(\DateTime $to, \DateTime $from = null)
    {
        if (null === $from) {
            $from = new \DateTime();
        }

        return self::getDuration($to->getTimestamp(), $from->getTimestamp());
    }

    public function getPerson(): ?string
    {
        return $this->person;
    }

    public function setPerson(?string $person): self
    {
        $this->person = $person;

        return $this;
    }

    public function getConfirmed(): ?bool
    {
        return $this->confirmed;
    }

    public function setConfirmed(bool $confirmed): self
    {
        $this->confirmed = $confirmed;

        return $this;
    }
}
