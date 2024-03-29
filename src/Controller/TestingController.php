<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\CalendarService;
use App\Service\CustomerProtectionService;
use App\Service\MailService;
use App\Service\MlService;
use App\Service\PdfService;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
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
    private $mailService;

    public function __construct(UserRepository $userRepository, CustomerProtectionService $customerProtectionService, MailService $mailService , CalendarService $calendarService, MlService $mlService)
    {
        $this -> userRepository = $userRepository;
        $this -> customerProtectionService = $customerProtectionService;
        $this -> calendarService = $calendarService;
        $this -> mlService = $mlService;
        $this -> mailService = $mailService;
    }

    /**
     * @Route("/admin/test/json", methods={"POST"})
     */
    public function testGenerate(Request $request) {
        $start = $request->get("start");
        $end = $request->get("stop");
        return new JsonResponse($this->parseAvailabilityData($start , $end, $this->userRepository, $this->customerProtectionService, $this -> calendarService, $this -> mlService));
    }


    /**
     * @Route("/admin/test/{start}/{end}", defaults={"start"="2000-12-03", "end"="2000-12-03"})
     */
    public function test($start, $end) {
        return $this->render("ml/graph.html.twig", array("start" => $start, "end" => $end));
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
        return $data;
    }





    /**
     * @Route("/requestRemainingDays/{id}")
     * @IsGranted("ENDYEAR", subject="user")
     */
    public function requestRemainingDays(User $user, Request $request) {
        $title = "Extra payment";
        $twigPath = "emails/askPayRemainingDays.html.twig";
        $twigParam = array('base_href' => $request->getSchemeAndHttpHost().'/', 'person' => $user);
        $this->mailService->mail(array($user -> getEmail()), $title, $twigPath, $twigParam);
        return $this->redirectToRoute("slash");
    }



    /**
     * @Route("/requestRemainingDays/{id}/pay")
     * @IsGranted("ENDYEAR", subject="user")
     */
    public function requestRemainingDaysPay(User $user, Request $request, UserRepository $userRepository, EntityManagerInterface $entityManager) {
        $admins = $userRepository->findUserByRole('ROLE_ADMIN');

        foreach ($admins as $admin) {
            $title = "Restanta de plata";
            $twigPath = "emails/askPayRemainingDaysAdminPay.html.twig";
            $twigParam = array('base_href' => $request->getSchemeAndHttpHost().'/', 'person' => $user);
            $this->mailService->mail(array($admin -> getEmail()), $title, $twigPath, $twigParam);
        }
        $user->setFreeDays(0);
        $entityManager->persist($user);
        $entityManager->flush();
        return $this->redirectToRoute("slash");
    }



    /**
     * @Route("/requestRemainingDays/{id}/keep")
     * @IsGranted("ENDYEAR", subject="user")
     */
    public function requestRemainingDaysKeep(User $user, Request $request, UserRepository $userRepository, EntityManagerInterface $entityManager) {
        $admins = $userRepository->findUserByRole('ROLE_ADMIN');
        foreach ($admins as $admin) {
            $title = "Restanta de zile";
            $twigPath = "emails/askPayRemainingDaysAdminKeep.html.twig";
            $twigParam = array('base_href' => $request->getSchemeAndHttpHost().'/', 'person' => $user);
            $this->mailService->mail(array($admin -> getEmail()), $title, $twigPath, $twigParam);
        }
        return $this->redirectToRoute("slash");
    }

}
