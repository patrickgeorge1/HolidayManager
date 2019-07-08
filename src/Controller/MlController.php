<?php

namespace App\Controller;

use App\Entity\Demands;
use App\Entity\User;

use App\Repository\DemandsRepository;
use App\Repository\UserRepository;
use App\Service\CalendarService;
use App\Service\CustomerProtectionService;
use App\Service\MlService;
use Doctrine\DBAL\Types\DateTimeType;
use Doctrine\ORM\EntityManagerInterface;
use Phpml\Regression\LeastSquares;
use Phpml\Regression\SVR;
use Phpml\SupportVectorMachine\Kernel;
use Phpml\Classification\KNearestNeighbors;
use Phpml\Classification\SVC;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MlController extends AbstractController
{

    private $demandsRepository;
    private $mlService;
    public $calendarService;
    public $userRepository;
    public function __construct(DemandsRepository $demandsRepository, MlService $mlService, CalendarService $calendarService, UserRepository $userRepository)
    {
        $this -> demandsRepository = $demandsRepository;
        $this->mlService =$mlService;
        $this->calendarService = $calendarService;
        $this->userRepository = $userRepository;
    }


    /**
     * @Route("/admin/checkAvailability", name ="checkAvailability", methods={"POST", "GET"})
     */
    public function checkAvailability(Request $request) {

        return $this->render("ml/checkAvailability.html.twig", array(
            "user_display" => $this->getUser()->getFirstName(),
            'profile' => $this->getUser()->getId(),
            'person' => $this->getUser(),
        ));
    }


    /**
     * @Route("/admin/checkAvailabilityProcessing", name ="checkAvailabilityProcessing", methods={"POST", "GET"})
     */
    public function checkAvailabilityProcessing(Request $request, MlService $mlService, CustomerProtectionService $customerProtectionService, CalendarService $calendarService) {

        $start = \DateTime::createFromFormat('Y-m-d' ,$request->get("start"));
        $end = \DateTime::createFromFormat('Y-m-d' ,$request->get("stop"));
        $nr = $customerProtectionService->countDays($start, $end);

        $months = array(1 => 31, 2 => 28, 3 => 31, 4 => 30, 5 => 31, 6 => 30, 7 => 31, 8 => 31, 9 => 30, 10 => 31, 11 => 30, 12 => 31 );
        $day_index = $calendarService->getDateIndex($start);

        $mean = array();
        $users = $this->userRepository->findAll();

        foreach ($users as $user) {
            $mean_sum = 0;
            for ($i = 1; $i <= $nr; $i++) {
                $mean_sum += $mlService->predictAjustment($day_index + $i, $user->getFreeDays());
            }
            $mean_sum /= $nr;
            $item = array();
            $item["person_id"] = $user->getId();
            $item["person_name"] = $user->getFirstName();
            $item["person_email"] = $user->getEmail();
            $item["mean"] = $mean_sum;
            array_push($mean, $item);
        }

        return $this->render("ml/displayAvailability.html.twig", array("results" => $mean));
    }



}
