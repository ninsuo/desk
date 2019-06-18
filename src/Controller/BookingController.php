<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Entity\Desk;
use App\Entity\Room;
use App\Repository\BookingRepository;
use App\Repository\DeskRepository;
use App\Repository\RoomRepository;
use App\Services\BookingService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class BookingController extends BaseController
{
    protected $roomRepository;
    protected $bookingRepository;
    protected $deskRepository;
    protected $bookingService;
    protected $session;

    public function __construct(RoomRepository $roomRepository, BookingRepository $bookingRepository, DeskRepository $deskRepository, BookingService $bookingService, SessionInterface $session)
    {
        $this->roomRepository = $roomRepository;
        $this->bookingRepository = $bookingRepository;
        $this->deskRepository = $deskRepository;
        $this->bookingService = $bookingService;
        $this->session = $session;
    }

    /**
     * @Route("/", name="index")
     */
    public function index()
    {
        return $this->render('booking/index.html.twig', [
            'rooms' => $this->roomRepository->findAll(),
        ]);
    }

    /**
     * @Route("/book/choose-day/{id}/{date}", name="book_choose_day", defaults={"date": null})
     */
    public function bookChooseDay(Room $room, ?string $date)
    {
        $date = $this->getDate($date);

        return $this->render('booking/book_choose_day.html.twig', [
            'room' => $room,
            'timetable' => $this->bookingService->getRoomTimetable($room, $date),
            'previousWeek' => $this->bookingService->getPreviousWeek($date)->format('Y-m-d-H-i'),
            'nextWeek' => $this->bookingService->getNextWeek($date)->format('Y-m-d-H-i'),
            'monthsInWeek' => $this->bookingService->getMonthsInWeek($date),
            'daysToBook' => $this->bookingService->getDaysToBookChoices(),
            'date' => $date->format('Y-m-d-H-i'),
        ]);
    }

    /**
     * @Route("/book/choose-start/{id}/{day}", name="book_choose_start")
     */
    public function bookChooseStart(Room $room, string $day)
    {
        $date = $this->getDate($day);

        return $this->render('booking/book_choose_start.html.twig', [
            'room' => $room,
            'day' => $day,
            'starts' => $this->bookingService->getAvailableStartHours($room, $date),
        ]);
    }

    /**
     * @Route("/book/choose-end/{id}/{day}/{start}", name="book_choose_end")
     */
    public function bookChooseEnd(Room $room, string $day, string $start)
    {
        $date = $this->getDateTime($day, $start);

        return $this->render('booking/book_choose_end.html.twig', [
            'room' => $room,
            'day' => $day,
            'start' => $start,
            'ends' => $this->bookingService->getAvailableEndHours($room, $date),
        ]);
    }

    /**
     * @Route("/book/choose-end/{id}/{day}/{start}/{end}", name="book_choose_desk")
     */
    public function bookChooseDesk(Room $room, string $day, string $start, string $end)
    {
        $startDate = $this->getDateTime($day, $start);
        $endDate = $this->getDateTime($day, $end);

        return $this->render('booking/book_choose_desk.html.twig', [
            'room' => $room,
            'day' => $day,
            'start' => $start,
            'end' => $end,
            'desks' => $this->bookingService->getAvailableDesksBetween($room, $startDate, $endDate),
        ]);
    }

    /**
     * @Route("/book/choose-name/{id}/{day}/{start}/{end}/{deskNumber}", name="book_choose_name")
     */
    public function bookChooseName(Request $request, Room $room, string $day, string $start, string $end, string $deskNumber)
    {
        $desk = $this->deskRepository->findOneByNumber($deskNumber);
        if (!$desk) {
            throw $this->createNotFoundException();
        }

        $startDate = $this->getDateTime($day, $start);
        $startDate->setTimestamp($startDate->getTimestamp() + 1);

        $endDate = $this->getDateTime($day, $end);

        $form = $this->createFormBuilder()
            ->add('name', TextType::class, [
                'label' => 'Please enter your name',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new Callback(function ($payload, ExecutionContextInterface $context) use ($desk, $startDate, $endDate) {
                        if (count($this->bookingRepository->getDeskBookingsBetween($desk, $startDate, $endDate))) {
                            $context->buildViolation('Oops, it seems that someone just booked that desk...')
                                    ->atPath('name')
                                    ->addViolation();
                        }
                    }),
                ],
            ])
            ->add('book', SubmitType::class)
            ->getForm()
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $booking = new Booking();
            $booking->setDesk($desk);
            $booking->setStart($startDate);
            $booking->setEnd($endDate);
            $booking->setPerson($form->get('name')->getData());
            $booking->setConfirmed(false);

            $this->bookingRepository->save($booking);

            $this->addFlash('success', 'Great, you succesfully booked your desk. See you soon!');

            return $this->redirectToRoute('book_choose_day', [
                'id' => $room->getId(),
            ]);
        }

        return $this->render('booking/book_choose_name.html.twig', [
            'room' => $room,
            'day' => $day,
            'start' => $start,
            'end' => $end,
            'desk' => $desk,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/confirm/{uuid}/{date}", name="confirm", defaults={"date": null})
     */
    public function redirectConfirm(Desk $desk, ?string $date)
    {
        $this->session->set('desk', $desk->getId());

        return $this->redirectToRoute('confirm_page', [
            'date' => $date,
        ]);
    }

    /**
     * @Route("/confirm-page/{date}", name="confirm_page", defaults={"date": null})
     */
    public function confirmationPage(?string $date)
    {
        if (!($desk = $this->session->get('desk'))) {
            throw $this->createNotFoundException();
        }

        if (!($desk = $this->deskRepository->find($desk))) {
            throw $this->createNotFoundException();
        }

        $day = $this->getDate($date);

        return $this->render('booking/confirm.html.twig', [
            'desk' => $desk,
            'booking' => $this->bookingService->getCurrentBooking($desk, $day),
        ]);
    }

    /**
     * @Route("/do-confirm/{id}/{csrf}", name="do_confirm")
     */
    public function doConfirm(Booking $booking, string $csrf)
    {
        $this->validateCsrfOrThrowNotFoundException('confirm', $csrf);

        $booking->setConfirmed(true);

        $this->bookingRepository->save($booking);

        $this->addFlash('success', sprintf('Have a great day, %s!', $booking->getPerson()));

        return $this->redirectToRoute('confirm', [
            'uuid' => $booking->getDesk()->getUuid(),
        ]);
    }
}
