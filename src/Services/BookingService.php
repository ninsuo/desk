<?php

namespace App\Services;

use App\Entity\Booking;
use App\Entity\Desk;
use App\Entity\Room;
use App\Repository\BookingRepository;

class BookingService
{
    protected $bookingRepository;

    public function __construct(BookingRepository $bookingRepository)
    {
        $this->bookingRepository = $bookingRepository;
    }

    public function getRoomTimetable(Room $room, \DateTime $date)
    {
        $monday = $this->getMonday($date);
        $friday = $this->getFriday($date);

        $bookings = $this->bookingRepository->getRoomBookingsBetween($room, $monday, $friday);

        return $this->generateTimetableFromBookings($bookings, $monday);
    }

    public function getDeskTimetable(Desk $desk, \DateTime $date)
    {
        $monday = $this->getMonday($date);
        $friday = $this->getFriday($date);

        $bookings = $this->bookingRepository->getDeskBookingsBetween($desk, $monday, $friday);

        return $this->generateTimetableFromBookings($bookings, $monday);
    }

    public function getNextBookingsOfTheDay(Desk $desk, \DateTime $time, bool $withEmptySpots = false) : array
    {
        if (in_array($time->format('w'), [0, 6])) {
            return [];
        }

        $timetable = $this->getDeskTimetable($desk, $time);
        $dayString = $time->format('D d');
        $bookings = [];

        $current = clone $time;
        foreach ($timetable as $hours => $days) {
            $current->setTime(substr($hours, 0, 2), substr($hours, 3, 2), 1);
            if (count($days[$dayString]) > 0) {
                $booking = reset($days[$dayString]);

                if ($current->getTimestamp() < $time->getTimestamp()
                    && !Booking::betweenDates($current, $booking->getStart(), $booking->getEnd())) {
                    continue;
                }

                $bookings[$hours] = reset($days[$dayString]);
            } else if ($withEmptySpots && $current->getTimestamp() >= $time->getTimestamp()) {
                $bookings[$hours] = null;
            }
        }

        return $bookings;
    }

    public function getDeskStatus(Desk $desk, \DateTime $time)
    {
        $nextBookings = $this->getNextBookingsOfTheDay($desk, $time);

        // Free today
        if (0 == count($nextBookings)) {
            return [
                'status' => 'free_all_day',
                'label' => 'No bookings today',
            ];
        }

        $hours = key($nextBookings);
        $nextBooking = reset($nextBookings);

        $current = clone $time;
        $current->setTime(substr($hours, 0, 2), substr($hours, 3, 2), 0);

        $diff = $current->getTimestamp() - $time->getTimestamp();

        // Busy
        if ($diff <= 0) {
            $booking = null;
            for ($i = substr($hours, 0, 2); $i < 20; $i++) {
                $nextHours = sprintf('%02d:00 - %02d:00', $i, $i + 1);
                if (isset($nextBookings[$nextHours])) {
                    $booking = $nextBookings[$nextHours];
                } else {
                    return [
                        'status' => 'busy',
                        'label' => sprintf(
                            'Busy until %02dh <br/> %s',
                            $i,
                            Booking::getDurationDates($booking->getEnd(), $time)
                        ),
                    ];
                }
            }

            return [
                'status' => 'busy',
                'label' => 'Busy all day',
            ];
        }

        // Busy soon
        if ($diff < 3600) {
            return [
                'status' => 'busy_soon',
                'label' => sprintf(
                    'Next meeting at %s <br/> %s',
                    $nextBooking->getStart()->format('H:i'),
                    Booking::getDurationDates($nextBooking->getStart())
                ),
            ];
        }

        // Free now
        return [
            'status' => 'free_now',
            'label' => sprintf(
                'Next meeting at %s <br/> %s',
                $nextBooking->getStart()->format('H:i'),
                Booking::getDurationDates($nextBooking->getStart())
            ),
        ];
    }

    public function getCurrentBooking(Desk $desk, \DateTime $time) : ?Booking
    {
        $nextBookings = $this->getNextBookingsOfTheDay($desk, $time);

        if (count($nextBookings) == 0) {
            return null;
        }

        $nextBooking = reset($nextBookings);
        if ($nextBooking->getStart()->getTimestamp() > $time->getTimestamp()) {
            return null;
        }

        return $nextBooking;
    }

    public function getPreviousWeek(\DateTime $date)
    {
        return $this->getMonday($date)->sub(new \DateInterval('P7D'));
    }

    public function getNextWeek(\DateTime $date)
    {
        return $this->getMonday($date)->add(new \DateInterval('P7D'));
    }

    public function getMonthsInWeek(\DateTime $date) : array
    {
        $months = [];
        $day = $this->getMonday($date);
        for ($i = 0; $i < 5; $i++) {
            $months[] = $day->format('F');
            $day->add(new \DateInterval('P1D'));
        }

        return array_unique($months);
    }

    public function getDaysToBookChoices() : array
    {
        $days = [];
        $day = new \DateTime();
        while (count($days) < 12) {
            if (!in_array($day->format('w'), [0, 6])) {
                $days[] = clone $day;
            }
            $day->add(new \DateInterval('P1D'));
        }

        return $days;
    }

    public function getAvailableStartHours(Room $room, \DateTime $date) : array
    {
        $timetable = $this->getRoomTimetable($room, $date);
        $hours = [];

        foreach ($timetable as $hour => $days) {
            $hours[substr($hour, 0, 5)] = $days[$date->format('D d')];
        }

        return $hours;
    }

    public function getAvailableDesksBetween(Room $room, \DateTime $start, \DateTime $end)
    {
        $desks = [];
        foreach ($room->getDesks()->toArray() as $desk) {
            $desks[$desk->getNumber()] = $desk;
        }

        $timetable = $this->getRoomTimetable($room, $start);
        foreach ($timetable as $hours => $days) {
            foreach ($days[$start->format('D d')] as $booking) {
                if (array_key_exists($booking->getDesk()->getNumber(), $desks)
                    && $booking->intersectDates($start, $end, $booking->getStart(), $booking->getEnd())) {
                    unset($desks[$booking->getDesk()->getNumber()]);
                }
            }
        }

        uasort($desks, function(Desk $deskA, Desk $deskB) {
            return $deskA->getNumber() <=> $deskB->getNumber();
        });

        return $desks;
    }

    public function getAvailableEndHours(Room $room, \DateTime $start)
    {
        $starts = $this->getAvailableStartHours($room, $start);
        $ends = [];

        $endOfStart = (clone $start)->setTimestamp($start->getTimestamp() + 3600);

        foreach ($starts as $time => $bookings) {
            $end = (clone $start)->setTime(substr($time, 0, 2) + 1, substr($time, 3, 2), 0);
            if ($endOfStart->getTimestamp() > $end->getTimestamp()) {
                continue;
            }

            $ends[$end->format('H:i')] = $this->getAvailableDesksBetween($room, $start, $end);
        }

        return $ends;
    }

    private function getMonday(\DateTime $date) : \DateTime
    {
        $date = clone $date;

        if ($date->format('w') == 0) {
            $monday = $date->add(new \DateInterval('P1D'));
        } elseif ($date->format('w') > 1) {
            $monday = $date->sub(new \DateInterval(sprintf('P%dD', $date->format('w') - 1)));
        } else {
            $monday = $date;
        }
        $monday->setTime(8, 0, 0);

        return $monday;
    }

    private function getFriday(\DateTime $date) : \DateTime
    {
        $monday = $this->getMonday($date);

        $friday = (clone $monday)->add(new \DateInterval('P4D'));
        $friday->setTime(20, 0, 0);

        return $friday;
    }

    private function generateTimetableFromBookings(array $bookings, \DateTime $monday) : array
    {
        $timetable = [];
        for ($day = 0; $day < 5; $day++) {
            for ($hour = 8; $hour < 20; $hour++) {
                $from = (clone $monday);
                if ($day) {
                    $from->add(new \DateInterval(sprintf('P%dD', $day)));
                }
                $from->setTime($hour, 0, 0);
                $to = (clone $from)->add(new \DateInterval('PT1H'));

                $dayString = $from->format('D d');
                $hourString = sprintf('%s - %s', $from->format('H:i'), $to->format('H:i'));

                $timetable[$hourString][$dayString] = [];
                foreach ($bookings as $booking) {
                    /* @var \App\Entity\Booking $booking */
                    if (Booking::intersectDates($booking->getStart(), $booking->getEnd(), $from, $to)) {
                        $timetable[$hourString][$dayString][] = $booking;
                    }
                }
            }
        }

        return $timetable;
    }
}