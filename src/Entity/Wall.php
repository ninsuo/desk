<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\WallRepository")
 * @ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 */
class Wall implements ObjectInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Room", inversedBy="walls")
     * @ORM\JoinColumn(nullable=false)
     */
    private $room;

    /**
     * @ORM\Column(type="string", length=7)
     */
    private $color;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\WallCoordinate", mappedBy="wall", orphanRemoval=true)
     */
    private $coordinates;

    public function __construct()
    {
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
     * @return Collection|WallCoordinate[]
     */
    public function getCoordinates() : Collection
    {
        return $this->coordinates;
    }

    public function addCoordinate($coordinate) : ObjectInterface
    {
        if (!$this->coordinates->contains($coordinate)) {
            $this->coordinates[] = $coordinate;
            $coordinate->setWall($this);
        }

        return $this;
    }

    public function removeCoordinate($coordinate) : ObjectInterface
    {
        if ($this->coordinates->contains($coordinate)) {
            $this->coordinates->removeElement($coordinate);
            // set the owning side to null (unless already changed)
            if ($coordinate->getWall() === $this) {
                $coordinate->setWall(null);
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
        return 'wall';
    }

    public function isEqualTo(?ObjectInterface $object) : bool
    {
        return $object && $this->getType() == $object->getType() && $this->getId() == $object->getId();
    }
}
