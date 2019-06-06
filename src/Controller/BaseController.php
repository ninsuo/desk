<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class BaseController extends AbstractController
{
    protected function getDate(?string $date) : \DateTime
    {
        if (null === $date) {
            $date = date('Y-m-d-H-i');
        }

        return \DateTime::createFromFormat('Y-m-d-H-i', $date);
    }

    protected function getDateTime(string $date, string $time) : \DateTime
    {
        return $this
            ->getDate($date)
            ->setTime(substr($time, 0, 2), substr($time, 3, 2), 0);
    }

    protected function validateCsrfOrThrowNotFoundException(string $id, ?string $token): void
    {
        if (!$token || !is_scalar($token) || !$this->isCsrfTokenValid($id, $token)) {
            throw $this->createNotFoundException();
        }
    }
}