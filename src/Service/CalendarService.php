<?php


namespace App\Service;


use App\Entity\Events;
use Doctrine\ORM\EntityManagerInterface;
use mysql_xdevapi\Exception;
use Symfony\Component\HttpFoundation\Response;

class CalendarService
{
    public function program(EntityManagerInterface $entityManager, $title, $start, $end) {

        try {
            $event = new Events();
            $event->setTitle($title);
            $event->setStart(\DateTime::createFromFormat('Y-m-d', $start));
            $event->setEnd(\DateTime::createFromFormat('Y-m-d', $end));
            $entityManager->persist($event);
            $entityManager->flush();
            return 1;
        } catch (Exception $e) {
            return 0;
        }
    }

    public function program2(EntityManagerInterface $entityManager, $title, $start, $end) {

        try {
            $event = new Events();
            $event->setTitle($title);
            $event->setStart(\DateTime::createFromFormat('m/d/Y', $start));
            $event->setEnd(\DateTime::createFromFormat('m/d/Y', $end)->add(new \DateInterval('P1D')));
            $entityManager->persist($event);
            $entityManager->flush();
            return 1;
        } catch (Exception $e) {
            return 0;
        }
    }
}