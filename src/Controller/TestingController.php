<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\CalendarService;
use App\Service\CustomerProtectionService;
use App\Service\MlService;
use App\Service\PdfService;
use Faker\Factory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class TestingController extends AbstractController
{

    private $userRepository;
    private $customerProtectionService;
    private $calendarService;
    private $mlService;

    public function __construct(UserRepository $userRepository, CustomerProtectionService $customerProtectionService, CalendarService $calendarService, MlService $mlService)
    {
        $this -> userRepository = $userRepository;
        $this -> customerProtectionService = $customerProtectionService;
        $this -> calendarService = $calendarService;
        $this -> mlService = $mlService;
    }

    /**
     * @Route("/admin/test")
     */
    public function test(Request $request) {

        $start = $request->get("start");
        $end   = $request->get("stop");
        if ($request->isMethod('get')) {
            return $this->render("ml/graph.html.twig", array("start" => $start, "end" => $end));
        }
        else {
            return new JsonResponse($this->parseAvailabilityData($start , $end, $this->userRepository, $this->customerProtectionService, $this -> calendarService, $this -> mlService));
        }
    }

    public function parseAvailabilityData(string $start, string $end, UserRepository $userRepository, CustomerProtectionService $customerProtectionService, CalendarService $calendarService, MlService $mlService) {

        $start = \DateTime::createFromFormat('Y-m-d' ,$start);
        $end = \DateTime::createFromFormat('Y-m-d' ,$end);
        $nr = $customerProtectionService->countDays($start, $end);

        $months = array(1 => 31, 2 => 28, 3 => 31, 4 => 30, 5 => 31, 6 => 30, 7 => 31, 8 => 31, 9 => 30, 10 => 31, 11 => 30, 12 => 31 );
        $day_index = $calendarService->getDateIndex($start);

        $mean = array();
        $users = $userRepository->findAll();
        $persons = array();
        foreach ($users as $user) {
            $mean_sum = 0;
            for ($i = 1; $i <= $nr; $i++) {
                $mean_sum += $mlService->predictAjustment($day_index + $i, $user->getFreeDays());
            }
            $mean_sum /= $nr;
            array_push($persons, $user->getFirstName()." ".$user->getLastName());
            array_push($mean, round($mean_sum));
        }
        $data = array("persons" => $persons, "mean" => $mean);
        return json_encode($data);
    }

}
