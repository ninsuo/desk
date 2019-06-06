<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(indexes={
 *     @ORM\Index(name="uuidx", columns={"uuid"})
 * })
 * @ORM\Entity(repositoryClass="App\Repository\DeskRepository")
 * @ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 */
class Desk implements ObjectInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Room", inversedBy="desks")
     * @ORM\JoinColumn(nullable=false)
     */
    private $room;

    /**
     * @ORM\Column(type="string", length=3)
     */
    private $number;

    /**
     * @ORM\Column(type="string", length=7)
     */
    private $color;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Booking", mappedBy="desk", orphanRemoval=true)
     */
    private $bookings;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\DeskCoordinate", mappedBy="desk", orphanRemoval=true)
     */
    private $coordinates;

    /**
     * @ORM\Column(type="string", length=36, nullable=true)
     */
    private $uuid;

    public function __construct()
    {
        $this->bookings = new ArrayCollection();
        $this->coordinates = new ArrayCollection();
    }

    public function getId() : ?int
    {
        return $this->id;
    }

    public function getRoom() : ?Room
    {
        return $this->room;
    }

    public function setRoom(?Room $room) : self
    {
        $this->room = $room;

        return $this;
    }

    public function getNumber() : ?string
    {
        return $this->number;
    }

    public function setNumber(string $number) : self
    {
        $this->number = $number;

        return $this;
    }

    public function getColor() : ?string
    {
        return $this->color;
    }

    public function setColor(string $color) : self
    {
        $this->color = $color;

        return $this;
    }

    /**
     * @return Collection|Booking[]
     */
    public function getBookings() : Collection
    {
        return $this->bookings;
    }

    public function addBooking(Booking $booking) : self
    {
        if (!$this->bookings->contains($booking)) {
            $this->bookings[] = $booking;
            $booking->setDesk($this);
        }

        return $this;
    }

    public function removeBooking(Booking $booking) : self
    {
        if ($this->bookings->contains($booking)) {
            $this->bookings->removeElement($booking);
            // set the owning side to null (unless already changed)
            if ($booking->getDesk() === $this) {
                $booking->setDesk(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|DeskCoordinate[]
     */
    public function getCoordinates() : Collection
    {
        return $this->coordinates;
    }

    public function addCoordinate($coordinate) : ObjectInterface
    {
        if (!$this->coordinates->contains($coordinate)) {
            $this->coordinates[] = $coordinate;
            $coordinate->setDesk($this);
        }

        return $this;
    }

    public function removeCoordinate($coordinate) : ObjectInterface
    {
        if ($this->coordinates->contains($coordinate)) {
            $this->coordinates->removeElement($coordinate);
            // set the owning side to null (unless already changed)
            if ($coordinate->getDesk() === $this) {
                $coordinate->setDesk(null);
            }
        }

        return $this;
    }

    public function at(int $x, int $y)
    {
        $this->cache = null;

        if (!$this->cache) {
            foreach ($this->coordinates as $coordinate) {
                $this->cache[$coordinate->getY()][$coordinate->getX()] = $coordinate;
            }
        }

        return $this->cache[$y][$x] ?? null;
    }

    public function getType()
    {
        return 'desk';
    }

    public function isEqualTo(?ObjectInterface $object) : bool
    {
        return $object && $this->getType() == $object->getType() && $this->getId() == $object->getId();
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(?string $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }
}
