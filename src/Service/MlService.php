<?php


namespace App\Service;


use App\Entity\Demands;
use App\Repository\DemandsRepository;
use App\Repository\EventsRepository;
use Symfony\Component\HttpFoundation\Response;

class MlService
{
    private $demandsRepository;
    private $eventsRepository;

    public function __construct(DemandsRepository $demandsRepository, EventsRepository $eventsRepository)
    {
        $this->demandsRepository = $demandsRepository;
        $this->eventsRepository = $eventsRepository;
    }

    public function mostWantedCatcher() {
        /** @var Demands $demand */
        $daysPerMonth = array("01" => 31, "02" => 30,"03" => 31,"04" => 30,"05" => 31,"06" => 30,"07" => 31,"08" => 31,"09" => 30,"10" => 31,"11" => 30,"12" => 31);
        //$demands = $demandsRepository->findAll();
        $demands = $this -> demandsRepository->findAllGreaterThanYear(date("Y",strtotime("-3 year")));
        $days_freq = array();
        for ($i = 1; $i <= 357; $i++) {
            array_push($days_freq, 0);
        }

        foreach ($demands as $demand) {
            $date_number = $daysPerMonth[$demand->getDate()->format('m')] + intval($demand->getDate()->format('d'));
            $days_freq[$date_number] = $days_freq[$date_number] + 1;
        }

        $sum = 0;
        for ($i = 1; $i <= 356; $i++) {
            $sum = $sum + $days_freq[$i];
        }

        $res = array();
        if ($sum != 0) {
            for ($i = 1; $i <= 356; $i++) {
                $days_freq[$i] = $days_freq[$i] * 100 / $sum ;
            }

            $maximum = max($days_freq);

            array_push($res, (string)(round($maximum,2)).'%');
            foreach ($demands as $demand) {
                $date_number = $daysPerMonth[$demand->getDate()->format('m')] + intval($demand->getDate()->format('d'));
                if ($days_freq[$date_number] == $maximum ) array_push($res, $demand->getDate()->format('d/m'));
            }

            $res =  array_unique($res);
        }
        else array_push($res, "N/A");
        return $res;
    }



    public function mostWantedCatcherMonth() {
        /** @var Demands $demand */
        $allRecords = $this -> eventsRepository -> findAll();

        $res = array();
        $months = array();
        for ($i = 1; $i <= 13; $i++)
        {
            array_push($months, 0);
        }
        foreach ($allRecords as $one) {
            $months[intval($one->getStart()->format('m'))] ++;
        }

        $sum = 0;
        for ($i = 1; $i <= 12; $i++)
        {
            $sum = $sum + $months[$i];
        }

        $max = 0;
        $maxi = 0;


        if ($sum != 0) {
            for ($i = 1; $i <= 12; $i++)
            {
                $months[$i] = $months[$i] * 100 / $sum;
                if ($max < $months[$i]) {
                    $max = $months[$i];
                    $maxi = $i;
                }
                //array_push($res, $months[$i]);
            }

            $monthPerNumberFinal = array(1 => "January", 2 => "February",3 => "March",4 => "April",5 => "May",6 => "June",7 => "July",8 => "August",9 => "September",10 => "October",11 => "November",12 => "December");
            array_push($res, round($max,2));
            array_push($res, $monthPerNumberFinal[$maxi]);
        }
        else {
            array_push($res, 0);
            array_push($res, "N/A");
        }



        return $res;
    }

}