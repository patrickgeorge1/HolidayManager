<?php


namespace App\Service;


class CustomerProtectionService
{
    function isweekend(string  $date) : bool
    {
        // m/d/Y
        $timestamp = strtotime($date);
        $weekday= date("l", $timestamp );
        if ($weekday =="Saturday" OR $weekday =="Sunday") { return true; }
        else {return false; }
    }


    function countFreeDays(\DateTime $start, \DateTime $end) : int {
        $start = date('m/d/Y', strtotime($start->format('d M Y')));
        $end = date('m/d/Y', strtotime($end->format('d M Y')));

        $nr = 0;
        while ( $start != $end) {
            if($this ->isweekend($start) === true) $nr = $nr + 1;
            $start = date('m/d/Y', strtotime($start. ' + 1 days'));
        }
        if($this ->isweekend($start) === true) $nr = $nr + 1;

        return $nr;
    }

}