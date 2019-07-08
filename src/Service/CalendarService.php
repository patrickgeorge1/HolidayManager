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

    public function curentDateIndex() {
        $months = array(1 => 31, 2 => 28, 3 => 31, 4 => 30, 5 => 31, 6 => 30, 7 => 31, 8 => 31, 9 => 30, 10 => 31, 11 => 30, 12 => 31 );
        $t=date('d-m-Y');
        $sum = 0;

        $month = intval(date("m",strtotime($t)));
        $day = intval(date("d",strtotime($t)));
        if($month == 1) $sum = $day;
        else {
            for ($i = 1; $i <= $month; $i++)
                $sum += $months[$i];
            $sum += $day;
        }
        return ($sum);

    }

    public function getDateIndex(\DateTime $date) {
        $months = array(1 => 31, 2 => 28, 3 => 31, 4 => 30, 5 => 31, 6 => 30, 7 => 31, 8 => 31, 9 => 30, 10 => 31, 11 => 30, 12 => 31 );

        $month = intval(date('m', strtotime($date->format('Y-m-d'))));
        $day = intval(date('d', strtotime($date->format('Y-m-d'))));


        $sum = 0;
        for ($i = 1; $i<= $month; $i++)
        {
            $sum = $sum + $months[$i];
        }
        $sum = $sum + $day;
        return $sum;
    }
}