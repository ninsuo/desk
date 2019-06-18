<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RoomRepository")
 * @ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 */
class Room
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $label;

    /**
     * @ORM\Column(type="integer")
     */
    private $width;

    /**
     * @ORM\Column(type="integer")
     */
    private $height;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Wall", mappedBy="room", orphanRemoval=true)
     * @ORM\OrderBy({"id" = "DESC"})
     */
    private $walls;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Desk", mappedBy="room", orphanRemoval=true)
     * @ORM\OrderBy({"id" = "DESC"})
     */
    private $desks;

    /**
     * @ORM\Column(type="integer")
     */
    private $size;

    public function __construct()
    {
        $this->walls = new ArrayCollection();
        $this->desks = new ArrayCollection();
    }

    public function getId() : ?int
    {
        return $this->id;
    }

    public function getLabel() : ?string
    {
        return $this->label;
    }

    public function setLabel(string $label) : self
    {
        $this->label = $label;

        return $this;
    }

    public function getWidth() : ?int
    {
        return $this->width;
    }

    public function setWidth(int $width) : self
    {
        $this->width = $width;

        return $this;
    }

    public function getHeight() : ?int
    {
        return $this->height;
    }

    public function setHeight(int $height) : self
    {
        $this->height = $height;

        return $this;
    }

    /**
     * @return Collection|Wall[]
     */
    public function getWalls() : Collection
    {
        return $this->walls;
    }

    public function addWall(Wall $wall) : self
    {
        if (!$this->walls->contains($wall)) {
            $this->walls[] = $wall;
            $wall->setRoom($this);
        }

        return $this;
    }

    public function removeWall(Wall $wall) : self
    {
        if ($this->walls->contains($wall)) {
            $this->walls->removeElement($wall);
            // set the owning side to null (unless already changed)
            if ($wall->getRoom() === $this) {
                $wall->setRoom(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Desk[]
     */
    public function getDesks() : Collection
    {
        return $this->desks;
    }

    public function addDesk(Desk $desk) : self
    {
        if (!$this->desks->contains($desk)) {
            $this->desks[] = $desk;
            $desk->setRoom($this);
        }

        return $this;
    }

    public function removeDesk(Desk $desk) : self
    {
        if ($this->desks->contains($desk)) {
            $this->desks->removeElement($desk);
            // set the owning side to null (unless already changed)
            if ($desk->getRoom() === $this) {
                $desk->setRoom(null);
            }
        }

        return $this;
    }

    public function getSize() : ?int
    {
        return $this->size;
    }

    public function setSize(int $size) : self
    {
        $this->size = $size;

        return $this;
    }

    public function at(int $x, int $y) : ?ObjectInterface
    {
        foreach ($this->desks as $desk) {
            if ($desk->at($x, $y)) {
                return $desk;
            }
        }

        foreach ($this->walls as $wall) {
            if ($wall->at($x, $y)) {
                return $wall;
            }
        }

        return null;
    }

    public function border(int $x, int $y) : string
    {
        /* @var ObjectInterface $at */
        $at = $this->at($x, $y);
        if (null === $at) {
            return '0px';
        }

        // border-width: top right bototm left;
        $style = '';

        // top
        $style .= sprintf('%dpx ', 5 * intval($y == 0 || !$at->isEqualTo($this->at($x, $y - 1))));

        // right
        $style .= sprintf('%dpx ', 5 * intval($x == $this->width || !$at->isEqualTo($this->at($x + 1, $y))));

        // bottom
        $style .= sprintf('%dpx ', 5 * intval($y == $this->height || !$at->isEqualTo($this->at($x, $y + 1))));

        // left
        $style .= sprintf('%dpx ', 5 * intval($x == 0 || !$at->isEqualTo($this->at($x - 1, $y))));

        return $style;
    }

    public function getSortedDesks()
    {
        $desks = $this->desks->toArray();

        usort($desks, function (Desk $deskA, Desk $deskB) {
            return $deskA->getNumber() <=> $deskB->getNumber();
        });

        return $desks;
    }
}
