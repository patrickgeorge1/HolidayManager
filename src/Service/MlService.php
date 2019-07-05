<?php


namespace App\Service;


use App\Entity\Demands;
use App\Entity\User;
use App\Repository\DemandsRepository;
use App\Repository\EventsRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Phpml\Classification\SVC;
use Phpml\Regression\LeastSquares;
use Phpml\SupportVectorMachine\Kernel;
use Symfony\Component\HttpFoundation\Response;

class MlService
{
    private $demandsRepository;
    private $eventsRepository;
    public $regression;

    public function __construct(DemandsRepository $demandsRepository, EventsRepository $eventsRepository)
    {
        $this->demandsRepository = $demandsRepository;
        $this->eventsRepository = $eventsRepository;
        $this->regression = new LeastSquares();

        // data for training
        $data = $this->getSamplesLabels();
        $samples = $data["samples"];
        $labels = $data["labels"];
        // training
        $this -> regression -> train($samples, $this -> demandMap($labels));
    }


    // STATISTICS

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





    // MACHINE LEARNING

    public function populateDemands(EntityManagerInterface $entityManager, UserRepository $userRepository) {
        for ($i = 0; $i <= 1000; $i++) {
//            $user = new User();
//            $user->setEmail("user".$i."@gmail.com");
//            $user->setRoles("ROLE_USER");
//            $user->setFirstName("user_first".$i.mt_rand(7, 33));
//            $user->setLastName("user_last".$i.mt_rand(33, 77));
//            $user->setPhone("+407".$this->number());
//            $user->setPassword("$2y$13\$gOW.OQV3SR1elCAU6rHRF.2DQMT7sFy6KFPEGrnMn0jYr7Io4.DIe");
//            $manager->persist($user);
            $demand = new Demands();
            $demand->setName($this->generateRandomString());
            $id = rand ( 200 , 209 );
            $user = $userRepository->findOneBy(['id' => $id]);
            $demand->setEmployee($user);
            $demand->setStatus(1);
            //Generate a timestamp using mt_rand.
            $timestamp = mt_rand(1, time());
            $randomDate = date("Y-m-d", $timestamp);
            $demand->setDate(\DateTime::createFromFormat("Y-m-d", $randomDate));
            $demand->setDuration(rand ( 1 , 5 ));
            $entityManager->persist($demand);
        }

        $entityManager->flush();
        return new Response("lal");
    }

    function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }




    public function populateUsers(EntityManagerInterface $entityManager, UserRepository $userRepository) {
        for ($i = 5000; $i <= 6000; $i++) {
            $user = new User();
            $id = rand(1, 1029);
            $user->setEmail("user".$i.$id."@gmail.com");
            $user->setRoles("ROLE_USER");
            $user->setFirstName("user_first".$i.$id);
            $user->setLastName("user_last".$i.$id);
            $user->setPhone("+407".mt_rand(1000, 2000));
            $user->setFreeDays(rand(1, 21));
            $user->setPassword("$2y$13\$gOW.OQV3SR1elCAU6rHRF.2DQMT7sFy6KFPEGrnMn0jYr7Io4.DIe");
            $entityManager->persist($user);
        }
        $entityManager->flush();
    }


    public function prepareDemandsJson() {
        // make a data asset in src->mlData -> results.json
        // [day_index free_days] => [demand_yes/no]
        $days = array();
        $remaining_days = 21;
        $demands = array();
        for ($i = 0; $i<= 365; $i++) {
            array_push($days, 21);
            array_push($demands, 0);
        }
        for ($i = 1; $i <= 365; $i++) {

            $random_factor = rand (1, 600);
            // zi de vacanta
            if ($random_factor < 10) {
                $demand_day_number = rand(1, 100);
                $demand_day_result = 1;
                // compute requested days
                switch ($demand_day_number) {
                    case ($demand_day_number <= 100 && $demand_day_number >= 60):
                        $demand_day_result = 4;
                        break;
                    case ($demand_day_number < 60 && $demand_day_number >= 30):
                        $demand_day_result = 3;
                        break;
                    case ($demand_day_number < 30 && $demand_day_number >= 10):
                        $demand_day_result = 2;
                        break;
                    case ($demand_day_number < 10):
                        $demand_day_result = 1;
                        break;
                }
                $remaining_days = $remaining_days- $demand_day_result;
                if ($remaining_days < 0) $remaining_days = 0;

                $demands[$i] = 1;
            }
            else $demands[$i] = 0;

            $days[$i] = $remaining_days;
        }


        dump(json_encode($days));
        dump(json_encode($demands));

        $values = array('days' => $days, 'demands' => $demands);

        $fp = fopen('../src/MlData/results.json', 'w');
        fwrite($fp, json_encode($values));
        fclose($fp);
    }


    public function getSamplesLabels() {
        $string = file_get_contents("../src/MlData/results.json");
        $json_a = json_decode($string);
        $days = $json_a->days;
        $demands = $json_a->demands;

        // prepare feed data
        $samples = array();
        $labels = array();
        $i = 0;
        foreach ($days as $day) {
            $day_action = array();
            array_push($day_action, ($i + 1));
            array_push($day_action, $days[$i]);
            array_push($samples, $day_action);
            array_push($labels, $demands[$i]);
            $i++;
        }
        return array("samples" => $samples, "labels" => $labels);

    }



    public function trainRegression() {
        // decode demands Json Result.json
        $string = file_get_contents("../src/MlData/results.json");
        $json_a = json_decode($string);
        $days = $json_a->days;
        $demands = $json_a->demands;

        // prepare feed data
        $samples = array();
        $labels = array();
        $i = 0;
        foreach ($days as $day) {
            $day_action = array();
            array_push($day_action, ($i + 1));
            array_push($day_action, $days[$i]);
            array_push($samples, $day_action);
            array_push($labels, $demands[$i]);
            $i++;
        }

        // SVC support vector classification

//        $classifier = new SVC(
//            Kernel::LINEAR, // $kernel
//            13.0,            // $cost
//            3,              // $degree
//            null,           // $gamma
//            0.0,            // $coef0
//            0.001,          // $tolerance
//            100,            // $cacheSize
//            true,           // $shrinking
//            true            // $probabilityEstimates, set to true
//        );
//        $classifier->train($samples, $labels);
//        dump($classifier->predictProbability([100, 11]));


//        SVR
//        $regression = new SVR(Kernel::LINEAR, $degree = 3, $epsilon=10.0);
//        $regression->train($samples, $labels);
//        dump($regression->predict([100, 0]));


        // Least Squares   best aproximation
        $regression = new LeastSquares();
        $regression -> train($samples, $this -> demandMap($labels));
//      dump( (100 * ( $regression->predict([365, 21]) + 17 )) / 54 );

        return new $regression;
    }

    // to predict Demand Ability use -->  predictAjustment(trainRegression(), $day_index, $free_days)
    public function predictAjustment($day_index, $free_days) {
        $corelationFactor = 17;
        $corelationMapping = 54;
        return ((100 * ( $this -> regression->predict([$day_index, $free_days]) + $corelationFactor )) / $corelationMapping);
    }


    // for mapping the distribution
    public function demandMap(array $demands) : array {
        $start = 1;
        $end = 365;
        while ($start != $end) {
            $nr = 0;
            $iter = $start - 1;
            if ($iter  <= 364)
            {
                while($demands[$iter] == 0) {
                    $nr++;
                    if ($iter <= 364) $iter++;
                    else break;
                }
                if ($nr != 0) {
                    $factor =  (1/$nr) * (365/21);
                    for($j = $start; $j<= $start + $nr; $j++)
                    {
                        $demands[$j] = ($j - $start) * $factor;
                    }
                    $start = $start + $nr;
                }
                else $start++;
            }
            else $start = $end;
        }
        return array_slice($demands, 0, 366);
    }




}