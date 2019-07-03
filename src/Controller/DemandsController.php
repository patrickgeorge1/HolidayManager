<?php

namespace App\Controller;

use App\Entity\Demands;
use App\Entity\Events;
use App\Entity\User;
use App\Repository\DemandsRepository;
use App\Repository\EventsRepository;
use App\Repository\UserRepository;
use App\Service\CalendarService;
use App\Service\MailService;
use App\Service\MlService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use mysql_xdevapi\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DemandsController extends AbstractController
{
    private $entityManager;
    private $mailer;
    private $admins;

    public function __construct(EntityManagerInterface $entityManager, MailService $mailer, UserRepository $userRepository)
    {
        $this->entityManager = $entityManager;
        $this->mailer = $mailer;
        $this->admins = $userRepository->findUserByRole('ROLE_ADMIN');
    }


    /**
     * @Route("/request/holiday", name="request_holiday", methods={"POST"})
     */
    public function request(Request $request, CalendarService $calendarService)
    {
        if (!$this->getUser()) return $this->redirectToRoute("app_login");
        // daca e admin il trimit pe ruta lui
        $rol = $this->getUser()->getRoles();

        if ($rol[0] == "ROLE_ADMIN")
        {
            // TODO note in calendar
            $start_date = $request->get("start_date");
            $end_date = $request->get("end_date");
            $duration = $this->date_diff($start_date, $end_date);

            if ($this -> getUser() -> getFreeDays() - $duration < 0) return new Response("Not enough free days !");
            else {
                /** @var User  $user */
                $user = $this->getUser();
                $user -> setFreeDays($user->getFreeDays() - $duration);
                $calendarService->program2($this -> entityManager, $request->get("title"), $request->get("start_date"), $request->get("end_date"));
                return $this->redirectToRoute("calendar");
            }
        }
        else
            try {
                /** @var User $user */
                $user = $this->getUser();
                $title = $request->get("title");
                $start_date = $request->get("start_date");
                $end_date = $request->get("end_date");
                // input of dates comes as string
                $duration = $this->date_diff($start_date, $end_date);


                // daca are suficiente zile libere
                if ($user -> getFreeDays() - $duration < 0) {
                    return new \Symfony\Component\HttpFoundation\Response("Not enough free days !");
                }
                else {
                    // mail($message);  dau mesaj unui admin
                    $demand = new Demands();
                    $demand->setName($title);
                    $demand->setStatus(1);
                    $demand->setDate(new \DateTime($start_date));;
                    $demand->setDuration($duration);
                    $demand->setEmployee($this->getUser());
                    $this->entityManager->persist($demand);
                    $this->entityManager->flush();

                    // send mail to EVERY admin
                    foreach ($this->admins as $admin) {
                        $title = "Holiday request by ".$demand->getEmployee()->getFirstName();
                        $twigPath = "emails/requestInfoAdmin.html.twig";
                        $twigParam = array("demand" => $demand, 'base_href' => $request->getSchemeAndHttpHost().'/');
                        $this->mailer->mail(array($admin -> getEmail()), $title, $twigPath, $twigParam);

                    }

                    // send mail to user
                    $title = "Holiday request ".$demand->getName();
                    $twigPath = "emails/requestInfoUser.html.twig";
                    $twigParam = array("demand" => $demand, 'base_href' => $request->getSchemeAndHttpHost().'/');
                    $this -> mailer->mail(array($demand->getEmployee()->getEmail()), $title, $twigPath, $twigParam);


                }
                return $this->redirectToRoute("benefits_user_list");
            } catch (\Exception $e) {
                return $this->redirectToRoute("app_login");
            }

    }


    public function date_diff($start_date, $end_date) {
        try {
            $end = strtotime($end_date);; // or your date as well
            $start = strtotime($start_date);
            $datediff = $end - $start;
            return round($datediff / (60 * 60 * 24)) + 1;
        } catch (\Exception $e) {
            return $this->redirectToRoute("app_login");
        }
    }

    /**
     * @Route("/demand/accept/{id}", name="demand_accept", methods={"GET"})
     */
    public function accept_demand(Demands $demand, CalendarService $calendarService, Request $request)
    {

            // vad daca e logat
            if (!$this->getUser()) return $this->redirectToRoute("app_login");
            $rol = $this->getUser()->getRoles();
            if ($rol[0] == "ROLE_ADMIN")
            {

                    if ($demand->getEmployee()->getFreeDays() - $demand->getDuration() >= 0)
                    {
                        $demand->setStatus(0);
                        // TODO add on calenar
                        $endDate = date('Y-m-d', strtotime($demand->getDate()->format('d M Y'). ' + '.$demand ->getDuration().' days'));
                        $calendarService->program($this -> entityManager, $demand->getName(), $demand->getDate()->format('Y-m-d'), $endDate);



                        // send mail
                        $title = "Answer for ".$demand->getName()." request";
                        $twigPath = "emails/accepted.html.twig";
                        $this -> mailer->mail(array($demand->getEmployee()->getEmail()), $title, $twigPath, array('base_href' => $request->getSchemeAndHttpHost().'/'));




                        $demand->getEmployee()->setFreeDays($demand->getEmployee()->getFreeDays() - $demand->getDuration());
                        $this->entityManager->persist($demand);
                        $this->entityManager->flush();
                        return $this->redirectToRoute("profile_user");
                    }
                    else return new Response("Baiatul si depasit zilele intre timp");
            }
            else return new Response("Ce cauta un user pe link de admin ?");


    }

    /**
     * @Route("/ml/test", name="demand_test", methods={"GET"})
     */
    public function testEmail(MlService $mlService) {

        $res = $mlService -> mostWantedCatcherMonth();
        // return new Response($res[1]);
    }


    /**
     * @Route("/demand/decline/{id}", name="demand_decline", methods={"GET"})
     */
    public function decline_demand(Demands $demand, CalendarService $calendarService, Request $request)
    {
        // vad daca e logat
        if (!$this->getUser()) return $this->redirectToRoute("app_login");
        $rol = $this->getUser()->getRoles();
        if ($rol[0] == "ROLE_ADMIN")
            try {
                $demand->setStatus(0);

                // send mail
                $title = "Answer for ".$demand->getName()." request";
                $twigPath = "emails/declined.html.twig";
                $this -> mailer->mail(array($demand->getEmployee()->getEmail()), $title, $twigPath, array('base_href' => $request->getSchemeAndHttpHost().'/'));


                $this->entityManager->persist($demand);
                $this->entityManager->flush();
                return $this->redirectToRoute("profile_user");
            } catch (\Exception $e) {
                echo 'Caught exception: ',  $e->getMessage(), "\n";
            }
        else return new Response("Ce cauta un user pe link de admin ?");
    }


    /**
     * @Route("/blah")
     */
    public function nothing(DemandsRepository $demandsRepository) {
        $res = $demandsRepository->findOneBy(["id" => 1]);
        //dd($res->getDate()->format('d M Y'));  // asa convertesc date time to string
        $duration = 7;
        dd(

            date('Y-m-d', strtotime($res->getDate()->format('d M Y'). ' + '.$duration.' days'))

        );

    }

}
