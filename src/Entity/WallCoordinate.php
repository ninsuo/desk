<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\WallCoordinateRepository")
 */
class WallCoordinate
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Wall", inversedBy="coordinates")
     * @ORM\JoinColumn(nullable=false)
     */
    private $wall;

    /**
     * @ORM\Column(type="integer")
     */
    private $x;

    /**
     * @ORM\Column(type="integer")
     */
    private $y;

    public function getId() : ?int
    {
        return $this->id;
    }

    public function getWall() : ?Wall
    {
        return $this->wall;
    }

    public function setWall(?Wall $wall) : self
    {
        $this->wall = $wall;

        return $this;
    }

    public function getX() : ?int
    {
        return $this->x;
    }

    public function setX(int $x) : self
    {
        $this->x = $x;

        return $this;
    }

    public function getY() : ?int
    {
        return $this->y;
    }

    public function setY(int $y) : self
    {
        $this->y = $y;

        return $this;
    }
}
