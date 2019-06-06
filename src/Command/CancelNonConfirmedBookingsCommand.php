<?php

namespace App\Command;

use App\Repository\BookingRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CancelNonConfirmedBookingsCommand extends Command
{
    protected static $defaultName = 'app:cancel-non-confirmed-bookings';

    protected $bookingRepository;

    public function __construct(BookingRepository $bookingRepository)
    {
        $this->bookingRepository = $bookingRepository;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Cancel bookings that have not been confirmed after N minutes.')
            ->addArgument('minutes', InputArgument::REQUIRED, 'Number of minutes before the booking expires')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $limit = (new \DateTime())->sub(new \DateInterval(sprintf('PT%dM', $input->getArgument('minutes'))));

        $io = new SymfonyStyle($input, $output);
        $io->comment(sprintf('Getting non confirmed bookings before %s', $limit->format('d/m/Y H:i:s')));

        $bookings = $this->bookingRepository->getNonConfirmedBookingsBefore($limit);

        $io->comment(sprintf('Going to delete %d entries', count($bookings)));

        foreach ($bookings as $booking) {
            /* @var \App\Entity\Booking $booking */
            $io->comment(sprintf(
                'Deleted #%d, booking of desk #%s in room %s by %s between %s and %s',
                $booking->getId(),
                $booking->getDesk()->getNumber(),
                $booking->getDesk()->getRoom()->getLabel(),
                $booking->getPerson(),
                $booking->getStart()->format('d/m/Y H:i'),
                $booking->getEnd()->format('d/m/Y H:i')
            ));

            $this->bookingRepository->remove($booking);
        }
    }
}
