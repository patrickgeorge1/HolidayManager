<?php

namespace App\Controller;

use App\Entity\Demands;
use App\Entity\Events;
use App\Entity\User;
use App\Repository\DemandsRepository;
use App\Repository\UserRepository;
use App\Service\CalendarService;
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

    public function __construct(EntityManagerInterface $entityManager, \Swift_Mailer $mailer, UserRepository $userRepository)
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

                //dd($request->get("start_date"));
                //dd(\DateTime::createFromFormat('m/d/Y', $request->get("start_date")));
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
                        $message = "User ".$demand->getEmployee()->getFirstName()." ".$demand->getEmployee()->getLastName().", request a holiday of ".$demand->getDuration()." day/s starting on ".$demand->getDate()->format('Y-m-d')." .  Request name : ".$demand->getName()." .";
                        $this->mail($admin->getEmail(),$title,$message);
                    }

                    // send mail to user
                    $title = "Holiday request ".$demand->getName();
                    $message = "Dear ".$demand->getEmployee()->getFirstName().", ".$demand->getEmployee()->getLastName()." your holiday request of ".$demand->getDuration()." day/s starting on ".$demand->getDate()->format('Y-m-d')." was sent and a admin will ask soon .  Request name : ".$demand->getName()." .";
                    $this->mail($demand->getEmployee()->getEmail(),$title,$message);
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
    public function accept_demand(Demands $demand, CalendarService $calendarService)
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
                        $title =  "Answer for ".$demand->getName()." request";
                        $message = "Congrats, your holiday request was accepted ! We wish you to have fun time and nice weather!";
                        $this -> mail($demand->getEmployee()->getEmail(), $title, $message);


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
     * @Route("/demand/decline/{id}", name="demand_decline", methods={"GET"})
     */
    public function decline_demand(Demands $demand, CalendarService $calendarService)
    {
        // vad daca e logat
        if (!$this->getUser()) return $this->redirectToRoute("app_login");
        $rol = $this->getUser()->getRoles();
        if ($rol[0] == "ROLE_ADMIN")
            try {
                $demand->setStatus(0);

                // send mail
                $title = "Answer for ".$demand->getName()." request";
                $message = "We are sorry, but we can't honor you with this holiday due to internal reasons.";
                $this -> mail($demand->getEmployee()->getEmail(), $title, $message);


                $this->entityManager->persist($demand);
                $this->entityManager->flush();
                return $this->redirectToRoute("profile_user");
            } catch (\Exception $e) {
                echo 'Caught exception: ',  $e->getMessage(), "\n";
            }
        else return new Response("Ce cauta un user pe link de admin ?");
    }

    public function mail($target_person, $message_title, $message_text)
    {
        try {
            $text = $message_text;
            $message = (new \Swift_Message($message_title))
                ->setFrom('msend28@gmail.com')
                ->setTo($target_person)
                ->setBody(
                    $text,
                    'text/plain'
                );
            $this->mailer->send($message);
        } catch (\Exception $e) {
            return $this->redirectToRoute("app_login");
        }

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
