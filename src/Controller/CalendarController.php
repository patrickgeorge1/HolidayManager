<?php

namespace App\Controller;

use App\Entity\Events;
use App\Repository\EventsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\DateTime;

class CalendarController extends AbstractController
{
    /**
     * @Route("/calendar", name="calendar")
     */
    public function calendar()
    {

        if (!$this->getUser()) return $this->redirectToRoute("app_login");
        // ruta cu privilegii
        $rol = $this->getUser()->getRoles();
        if ($rol[0] == "ROLE_ADMIN") return $this->redirectToRoute("calendar_admin");

        return $this->render('calendar/calendarAdmin.html.twig', [
            'user_display' => $this->getUser()->getFirstName(),
            'profile' => $this->getUser()->getId(),
            'person' => $this->getUser()
        ]);
    }


    /**
     * @Route("/calendarAdmin", name="calendar_admin")
     * @IsGranted("ROLE_ADMIN")
     */
    public function calendarAdmin()
    {
        if (!$this->getUser()) return $this->redirectToRoute("app_login");
        // ruta cu privilegii
        $rol = $this->getUser()->getRoles();
        if ($rol[0] == "ROLE_USER") return $this->redirectToRoute("calendar");

        return $this->render('calendar/calendarAdmin.html.twig', [
            'user_display' => $this->getUser()->getFirstName(),
            'profile' => $this->getUser()->getId(),
            'person' => $this->getUser()
        ]);
    }


    /**
     * @Route("/calendarTool", name="calendar_tool", methods={"GET","POST"})
     */
    public function tool(EventsRepository $eventsRepository)
    {
        if (!$this->getUser()) return $this->redirectToRoute("app_login");

        $info = $eventsRepository->findAll();
        $data = array();
        foreach ($info as $event) {
            $row = array();
            $row["id"] = $event->getId();
            $row["title"] = $event->getTitle();
            $row["start"] = $event->getStart()->format('Y-m-d H:i');
            $row["end"] = $event->getEnd()->format('Y-m-d H:i');
            array_push($data, $row);
        }
        return new Response(json_encode($data));
    }


    /**
     * @Route("/calendarToolUpdate", name="calendar_tool_update", methods={"GET","POST"})
     */
    public function toolUpdate(EntityManagerInterface $entityManager, Request $request)
    {
        if (!$this->getUser()) return $this->redirectToRoute("app_login");
        // ruta cu privilegii
        $rol = $this->getUser()->getRoles();
        if ($rol[0] == "ROLE_USER") return $this->redirectToRoute("calendar");

        $title = $request->get("title");
        $start = $request->get("start");
        $end = $request->get("end");
        $event = new Events();
        $event->setTitle($title);
        $event->setStart(\DateTime::createFromFormat('Y-m-d', $start));
        $event->setEnd(\DateTime::createFromFormat('Y-m-d', $end));
        $entityManager->persist($event);
        $entityManager->flush();
        return new Response(1);
    }
}
