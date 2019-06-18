<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Entity\Desk;
use App\Entity\Room;
use App\Repository\BookingRepository;
use App\Repository\RoomRepository;
use App\Services\BookingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class BoardController extends BaseController
{
    protected $roomRepository;
    protected $bookingRepository;
    protected $bookingService;

    public function __construct(RoomRepository $roomRepository, BookingRepository $bookingRepository, BookingService $bookingService)
    {
        $this->roomRepository = $roomRepository;
        $this->bookingRepository = $bookingRepository;
        $this->bookingService = $bookingService;
    }

    /**
     * @Route("/room/{id}/{date}", name="room", defaults={"date" = null})
     */
    public function room(Room $room, ?string $date)
    {
        return $this->render('board/room.html.twig', [
            'room' => $room,
            'date' => $this->getDate($date)->format('Y-m-d-H-i'),
        ]);
    }

    /**
     * @Route("/status/{id}/{date}", name="status", defaults={"date" = null})
     */
    public function status(Room $room, ?string $date)
    {
        $date = $this->getDate($date);

        $statuses = [];
        foreach ($room->getDesks() as $desk) {
            $statuses[$desk->getId()] = $this->bookingService->getDeskStatus($desk, $date);
        }

        return new JsonResponse($statuses);
    }

    /**
     * @Route("/bookings/{id}/{date}", name="bookings", defaults={"date" = null})
     */
    public function getBookings(Desk $desk, ?string $date)
    {
        $date = $this->getDate($date);

        return $this->render('board/bookings.html.twig', [
            'desk' => $desk,
            'slots' => $this->bookingService->getNextBookingsOfTheDay($desk, $date, true),
            'date' => $date->format('Y-m-d-H-i'),
        ]);
    }

    /**
     * @Route("/book-now/{id}/{date}/{slot}", name="book_now")
     */
    public function bookNow(Desk $desk, string $date, string $slot)
    {
        $date = $this->getDate($date);

        $startDate = (clone $date)->setTime(substr($slot, 0, 2), substr($slot, 3, 2), 1);
        $endDate = (clone $date)->setTime(substr($slot, 8, 2), substr($slot, 11, 2), 0);

        if (!count($this->bookingRepository->getDeskBookingsBetween($desk, $startDate, $endDate))) {
            $booking = new Booking();
            $booking->setDesk($desk);
            $booking->setStart($startDate);
            $booking->setEnd($endDate);
            $booking->setPerson('Board');
            $booking->setConfirmed(false);

            $this->bookingRepository->save($booking);

            $this->addFlash('success', sprintf(
                'Booking of desk #%s between %s and %s done!',
                $desk->getNumber(),
                $startDate->format('H:i'),
                $endDate->format('H:i')
            ));
        }

        return $this->redirectToRoute('room', [
            'id' => $desk->getRoom()->getId(),
            'date' => $date->format('Y-m-d-H-i'),
        ]);
    }
}