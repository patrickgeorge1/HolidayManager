<?php

namespace App\Controller;

use App\Entity\Messages;
use App\Entity\User;
use App\Form\EditUserType;
use App\Form\RegistrationFormType;
use App\Repository\DemandsRepository;
use App\Repository\EventsRepository;
use App\Repository\UserRepository;
use App\Service\CalendarService;
use App\Service\CustomerProtectionService;
use App\Service\MlService;
use App\Service\PdfService;
use Doctrine\ORM\EntityManagerInterface;
use mysql_xdevapi\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class UserController extends AbstractController
{

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }


    /**
     * @Route("/", name="slash")
     */
    public function index()
    {
        // verifica daca e logat
        if (!$this->getUser()) return $this->redirectToRoute("app_login");

        //dd($this->getUser()->getRoles());
        // verifica rol
        /*
        if ($this->isGranted("ROLE_ADMIN"))
            return $this->render('main/display.html.twig', [
                'controller_name' => 'UserController',
            ]);
        else
            return new Response("Nu esti admin");
        */
        try {
            return $this->render('main/index.html.twig', [
                "user_display" => $this->getUser()->getFirstName(),
                'profile' => $this->getUser()->getId(),
                'person' => $this->getUser()
            ]);
        } catch (Exception $e) {
            return $this->redirectToRoute("app_login");
        }
    }



    /**
     * @Route("/?_switch_user=user1@gmail.com", name="slashImpersonate", methods={"POST", "GET"})
     */
    public function indexImpersonate()
    {
        // verifica daca e logat
        if (!$this->getUser()) return $this->redirectToRoute("app_login");

        /*
        if ($this->isGranted("ROLE_ADMIN"))
            return $this->render('main/display.html.twig', [
                'controller_name' => 'UserController',
            ]);
        else
            return new Response("Nu esti admin");
        */
        try {
            return $this->render('main/index.html.twig', [
                "user_display" => $this->getUser()->getFirstName(),
                'profile' => $this->getUser()->getId(),
                'person' => $this->getUser()
            ]);
        } catch (\Exception $e) {
            return $this->redirectToRoute("app_login");
        }
    }




    /**
     * @Route("/logout", name="logout")
     */
    public function logout()
    {
    }


    public function mail(\Swift_Mailer $mailer, $target_person, $message_title, $message_text)
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
            $mailer->send($message);
        } catch (\Exception $e) {
            return $this->redirectToRoute("app_login");
        }
    }


    /**
     * @Route("/messages/mail", name="messages_mail", methods={"GET", "POST"})
     */
    public function messages()
    {
        try {
            return $this->render("main/messages.html.twig", array("user_display" => $this->getUser()->getFirstName(), 'profile' => $this->getUser()->getId(), 'person' => $this->getUser()));
        } catch (\Exception $e) {
            return $this->redirectToRoute("app_login");
        }
    }


    /**
     * @Route("/user/list", name="user_list")
     */
    public function list(UserRepository $userRepository)
    {
        // extrag info din db
        try {
            $all = $userRepository->findAll();
            $stationary = array();
            foreach ($all as $user) {
                $now = array();
                $now["first_name"] = $user->getFirstName();
                $now["last_name"] = $user->getLastName();
                $now["email"] = $user->getEmail();
                $now["phone"] = $user->getPhone();
                array_push($stationary, $now);
            }
            $param = array("users" => $stationary, "user_display" => $this->getUser()->getFirstName(), 'profile' => $this->getUser()->getId(), 'person' => $this->getUser());
        } catch (\Exception $e) {
            return $this->redirectToRoute("slash", array("error" => $e->getMessage(), "user_display" => $this->getUser()->getFirstName(), 'person' => $this->getUser()));
        }
        return $this->render("users/list.html.twig", $param);
    }


    /**
     * @Route("/admin/delete/{id}", name="admin_delete_user")
     */
    public function deleteAdmin(UserRepository $userRepository, User $user)
    {
        // extrag info din db
        try {
            $this->entityManager->remove($user);
            $this->entityManager->flush();
        } catch (\Exception $e) {
            return $this->redirectToRoute("slash", array("error" => $e->getMessage()));
        }
        return $this->redirectToRoute("admin_list");
    }


    /**
     * id o sa fie o referinta la obj din tabela
     * @Route("/admin/edit/{id}", name="formular_edit", methods={"POST", "GET"})
     */
    public function editUser(Request $request, User $user, UserPasswordEncoderInterface $passwordEncoder): Response
    {

        try {
            // creez forma pe baza clasei, USER o sa fie entitatea din tabela coresp referintei $id
            $form = $this->createForm(EditUserType::class, $user);
            $form->remove('password');

            // permit formei sa ia informatiile din requesturi
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                // o sa l faca automat de tipul User



                $formInfo = $form->getData();


                $user->setEmail($formInfo->getEmail());
                $user->setPhone($formInfo->getPhone());
                $user->setFirstName($formInfo->getFirstName());
                $user->setLastName($formInfo->getLastName());

                // editez in db
                $this->entityManager->persist($user);
                $this->entityManager->flush();
                return $this->redirectToRoute("admin_list");
            }

            // randez twig cu parm view form
            return $this->render('users/edit.html.twig', [
                'editForm' => $form->createView(),
                "user_display" => $this->getUser()->getFirstName(),
                'profile' => $this->getUser()->getId(),
                'person' => $this->getUser()
            ]);
        } catch (\Exception $e) {
            return $this->redirectToRoute("app_login");
        }
    }


    /**
     * @Route("/admin/list", name="admin_list")
     */
    public function listAdmin(UserRepository $userRepository, Request $request, UserPasswordEncoderInterface $passwordEncoder, MlService $mlService, CalendarService $calendarService)
    {
        try {
            // extrag info din db

            $all = $userRepository->findAll();
            $stationary = array();
            $day_index = $calendarService->curentDateIndex();


            foreach ($all as $user) {
                $now = array();
                $now["id"] = $user->getId();
                $now["first_name"] = $user->getFirstName();
                $now["last_name"] = $user->getLastName();
                $now["email"] = $user->getEmail();
                $now["phone"] = $user->getPhone();
                $now["demandAbility"] = $mlService->predictAjustment($day_index, $user->getFreeDays());
                array_push($stationary, $now);
            }


            // fac forma de adaugat useri !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
            $user = new User();
            $form = $this->createForm(RegistrationFormType::class, $user);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $user->setRoles("ROLE_USER");
                $user->setPassword($passwordEncoder->encodePassword($user, $form->get('plainPassword')->getData()));

                $this->entityManager->persist($user);
                $this->entityManager->flush();
                return $this->redirectToRoute("admin_list");
            }

            $param = array("users" => $stationary, "registrationForm" => $form->createView(), "user_display" => $this->getUser()->getFirstName(), 'profile' => $this->getUser()->getId(), 'person' => $this->getUser());


            //return $this->render("users/listAdmin.html.twig", $param);
            return $this->render("users/listAdmin.html.twig", $param);
        } catch (\Exception $e) {
            return $this->redirectToRoute("app_login");
        }
    }

    /**
     * @Route("/profile/user", name="profile_user", methods={"GET", "POST"})
     */
    public function profile(DemandsRepository $demandsRepository, CalendarService $calendarService)
    {

            $demands = $demandsRepository -> findBy(['status' => 1]);
            return $this->render("users/profile.html.twig", array(
                    "user_display" => $this->getUser()->getFirstName(),
                    'profile' => $this->getUser()->getId(),
                    'person' => $this->getUser(),
                    'demands'=> $demands
                )
            );
    }


    /**
     * @Route("/user/generateCertificate", name="generateCertificateUser")
     */
    public function generateCertificateUser(PdfService $pdfService) {
         $pdfService -> generatePDF("pdf/pdf.html.twig", $this->getUser());
    }

    /**
     * @Route("/admin/generateCertificate/{id}", name="generateCertificateAdmin")
     */
    public function generateCertificate(User $user, PdfService $pdfService) {
        $pdfService -> generatePDF("pdf/pdf.html.twig", $user);
    }
}