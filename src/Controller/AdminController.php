<?php

namespace App\Controller;

use App\Component\HttpFoundation\MpdfResponse;
use App\Entity\Desk;
use App\Entity\DeskCoordinate;
use App\Entity\Room;
use App\Entity\Wall;
use App\Entity\WallCoordinate;
use App\Form\DeleteType;
use App\Form\RoomType;
use App\Repository\DeskCoordinateRepository;
use App\Repository\DeskRepository;
use App\Repository\RoomRepository;
use App\Repository\WallCoordinateRepository;
use App\Repository\WallRepository;
use Endroid\QrCode\QrCode;
use Mpdf\Mpdf;
use Ramsey\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @IsGranted("ROLE_ADMIN")
 */
class AdminController extends BaseController
{
    protected $roomRepository;
    protected $wallRepository;
    protected $wallCoordinateRepository;
    protected $deskRepository;
    protected $deskCoordinateRepository;

    public function __construct(RoomRepository $roomRepository,
                                WallRepository $wallRepository,
                                WallCoordinateRepository $wallCoordinateRepository,
                                DeskRepository $deskRepository,
                                DeskCoordinateRepository $deskCoordinateRepository)
    {
        $this->roomRepository = $roomRepository;
        $this->wallRepository = $wallRepository;
        $this->wallCoordinateRepository = $wallCoordinateRepository;
        $this->deskRepository = $deskRepository;
        $this->deskCoordinateRepository = $deskCoordinateRepository;
    }

    /**
     * @Route("/admin", name="admin_index")
     */
    public function index(Request $request)
    {
        $form = $this->createForm(RoomType::class)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->roomRepository->save($room = $form->getData());

            return $this->redirectToRoute('admin_room', [
                'id' => $room->getId(),
            ]);
        }

        return $this->render('admin/index.html.twig', [
            'rooms' => $this->roomRepository->findAll(),
            'new_room' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/room/{id}", name="admin_room", requirements={"id": "\d+"})
     */
    public function room(Request $request, Room $room)
    {
        $editForm = $this->createForm(RoomType::class, $room)->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->roomRepository->save($room);

            return $this->redirectToRoute('admin_room', [
                'id' => $room->getId(),
            ]);
        }

        return $this->render('admin/room.html.twig', [
            'room' => $room,
            'edit_room' => $editForm->createView(),
            'delete_room' => $this->createForm(DeleteType::class)->createView(),
        ]);
    }

    /**
     * @Route("/admin/delete/{id}", name="admin_room_delete", requirements={"id": "\d+"})
     */
    public function delete(Request $request, Room $room)
    {
        $form = $this->createForm(DeleteType::class)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->roomRepository->remove($room);
        }

        return $this->redirectToRoute('admin_index');
    }

    /**
     * @Route("/admin/room/add-wall/{id}", name="admin_room_add_wall")
     */
    public function addWall(Room $room)
    {
        $wall = new Wall();
        $wall->setRoom($room);
        $wall->setColor('#000000');

        $this->wallRepository->save($wall);

        return $this->redirectToRoute('admin_room', [
            'id' => $room->getId(),
        ]);
    }

    /**
     * @Route("/admin/room/remove-wall/{id}", name="admin_room_remove_wall")
     */
    public function removeWall(Wall $wall)
    {
        $room = $wall->getRoom();

        $wall->getRoom()->removeWall($wall);

        $this->wallRepository->remove($wall);

        return $this->redirectToRoute('admin_room', [
            'id' => $room->getId(),
        ]);
    }

    /**
     * @Route("/admin/room/update-wall", name="admin_room_update_wall")
     */
    public function xhrUpdateWall(Request $request)
    {
        $wall = $this->wallRepository->find($request->request->get('wall_id'));
        if (!$wall) {
            throw $this->createNotFoundException();
        }

        $color = $request->request->get('color');
        if (!preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
            throw $this->createNotFoundException();
        }

        $wall->setColor($color);

        $this->wallRepository->save($wall);

        return new Response();
    }

    /**
     * @Route("/admin/room/place-wall/{id}", name="admin_room_place_wall")
     */
    public function xhrPlaceWall(Request $request, Room $room)
    {
        $wall = $this->wallRepository->find($request->request->get('wall_id'));
        $x = $request->request->get('x');
        $y = $request->request->get('y');

        if ($room->at($x, $y)) {
            return new Response();
        }

        $wallCoordinate = new WallCoordinate();
        $wallCoordinate->setWall($wall);
        $wallCoordinate->setX($x);
        $wallCoordinate->setY($y);

        $this->wallCoordinateRepository->save($wallCoordinate);

        return new Response();
    }

    /**
     * @Route("/admin/room/destroy-wall/{id}", name="admin_room_destroy_wall")
     */
    public function xhrDestroyWall(Request $request, Room $room)
    {
        $x = $request->request->get('x');
        $y = $request->request->get('y');

        $object = $room->at($x, $y);
        $coordinate = $object->at($x, $y);
        $object->removeCoordinate($coordinate);

        $this->wallCoordinateRepository->remove($coordinate);

        return new Response();
    }

    /**
     * @Route("/admin/room/add-desk/{id}", name="admin_room_add_desk")
     */
    public function addDesk(Room $room)
    {
        $desk = new Desk();
        $desk->setRoom($room);
        $desk->setColor('#000000');
        $desk->setNumber(count($room->getDesks()) + 1);

        $this->deskRepository->save($desk);

        return $this->redirectToRoute('admin_room', [
            'id' => $room->getId(),
        ]);
    }

    /**
     * @Route("/admin/room/remove-desk/{id}", name="admin_room_remove_desk")
     */
    public function removeDesk(Desk $desk)
    {
        $room = $desk->getRoom();

        $desk->getRoom()->removeDesk($desk);

        $this->deskRepository->remove($desk);

        return $this->redirectToRoute('admin_room', [
            'id' => $room->getId(),
        ]);
    }

    /**
     * @Route("/admin/room/update-desk", name="admin_room_update_desk")
     */
    public function xhrUpdateDesk(Request $request)
    {
        $desk = $this->deskRepository->find($request->request->get('desk_id'));
        if (!$desk) {
            throw $this->createNotFoundException();
        }

        $color = $request->request->get('color');
        if (!preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
            throw $this->createNotFoundException();
        }
        $desk->setColor($color);

        $number = $request->request->get('number');
        if (!preg_match('/^[0-9a-fA-F]+$/', $number)) {
            $number = 0;
        }
        $desk->setNumber($number);

        $this->deskRepository->save($desk);

        return new Response();
    }

    /**
     * @Route("/admin/room/place-desk/{id}", name="admin_room_place_desk")
     */
    public function xhrPlaceDesk(Request $request, Room $room)
    {
        $desk = $this->deskRepository->find($request->request->get('desk_id'));
        $x = $request->request->get('x');
        $y = $request->request->get('y');

        if ($room->at($x, $y)) {
            return new Response();
        }

        $deskCoordinate = new DeskCoordinate();
        $deskCoordinate->setDesk($desk);
        $deskCoordinate->setX($x);
        $deskCoordinate->setY($y);

        $this->deskCoordinateRepository->save($deskCoordinate);

        return new Response();
    }

    /**
     * @Route("/admin/room/destroy-desk/{id}", name="admin_room_destroy_desk")
     */
    public function xhrDestroyDesk(Request $request, Room $room)
    {
        $x = $request->request->get('x');
        $y = $request->request->get('y');

        $object = $room->at($x, $y);
        $coordinate = $object->at($x, $y);
        $object->removeCoordinate($coordinate);

        $this->deskCoordinateRepository->remove($coordinate);

        return new Response();
    }

    /**
     * @Route("/admin/room/pdf/{id}", name="admin_room_pdf")
     */
    public function pdf(Room $room)
    {
        $files = [];
        $qrCodes = [];
        foreach ($room->getDesks() as $desk) {

            if (!$desk->getUuid()) {
                $desk->setUuid(Uuid::uuid4());
                $this->deskRepository->save($desk);
            }

            $qrCode = new QrCode($this->generateUrl('confirm', ['uuid' => $desk->getUuid()], UrlGeneratorInterface::ABSOLUTE_URL));

            $qrCode->setForegroundColor([
                'r' => hexdec(substr($desk->getColor(), 1, 2)),
                'g' => hexdec(substr($desk->getColor(), 3, 2)),
                'b' => hexdec(substr($desk->getColor(), 5, 2)),
                'a' => 0,
            ]);

            $file = sprintf('%s/%s.png', sys_get_temp_dir(), Uuid::uuid4());
            $qrCode->writeFile($file);
            $qrCodes[$desk->getNumber()] = basename($file);
            $files[] = $file;
        }

        $mpdf = new Mpdf();
        $mpdf->SetBasePath(sys_get_temp_dir());

        $mpdf->WriteHTML($this->renderView('admin/pdf.html.twig', [
            'qrCodes' => $qrCodes,
        ]));

        array_map('unlink', $files);

        $filename = sprintf('%s.pdf', preg_replace('/^[^0-9a-zA-Z_\-\.]+$/', '-', $room->getLabel()));

        return new MpdfResponse($mpdf, $filename);
    }
}
