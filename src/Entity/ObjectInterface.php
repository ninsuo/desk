<?php

namespace App\Entity;

use Doctrine\Common\Collections\Collection;

interface ObjectInterface
{
    public function getCoordinates() : Collection;

    public function addCoordinate($coordinate) : ObjectInterface;

    public function removeCoordinate($coordinate) : ObjectInterface;

    public function getId() : ?int;

    public function getColor() : ?string;

    public function at(int $x, int $y);

    public function getType();

    public function isEqualTo(?ObjectInterface $object) : bool;
}
