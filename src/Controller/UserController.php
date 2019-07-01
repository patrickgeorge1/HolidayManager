<?php

namespace App\Controller;

use App\Entity\Messages;
use App\Entity\User;
use App\Form\EditUserType;
use App\Form\RegistrationFormType;
use App\Repository\DemandsRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
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
        } catch (Exception $e) {
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
        } catch (Exception $e) {
            return $this->redirectToRoute("app_login");
        }
    }


    /**
     * @Route("/user/list", name="user_list")
     */
    public function list(UserRepository $userRepository)
    {
        if (!$this->getUser()) return $this->redirectToRoute("app_login");
        // daca e admin il duc pe ruta lui
        $rol = $this->getUser()->getRoles();
        if ($rol[0] == "ROLE_ADMIN") return $this->redirectToRoute("admin_list");

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
        if (!$this->getUser()) return $this->redirectToRoute("app_login");
        // ruta cu privilegii
        $rol = $this->getUser()->getRoles();
        if ($rol[0] == "ROLE_USER") return $this->redirectToRoute("user_list");

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
        if (!$this->getUser()) return $this->redirectToRoute("app_login");
        // ruta cu privilegii
        $rol = $this->getUser()->getRoles();
        if ($rol[0] == "ROLE_USER") return $this->redirectToRoute("user_list");


        try {
            // creez forma pe baza clasei, USER o sa fie entitatea din tabela coresp referintei $id
            $form = $this->createForm(EditUserType::class, $user);

            // permit formei sa ia informatiile din requesturi
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                // o sa l faca automat de tipul User



                // TODO repair edit user
                $formInfo = $form->getData();
                if (empty($formInfo->getPassword())) {
                    $user->setPassword(
                        $passwordEncoder->encodePassword(
                            $user,
                            $form->getData()->getPassword()
                        )
                    );
                } else $user->setPassword($pass);

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
        } catch (Exception $e) {
            return $this->redirectToRoute("app_login");
        }
    }


    /**
     * @Route("/admin/list", name="admin_list")
     */
    public function listAdmin(UserRepository $userRepository, Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        if (!$this->getUser()) return $this->redirectToRoute("app_login");
        // daca e user il trimit pe ruta lui
        $rol = $this->getUser()->getRoles();
        if ($rol[0] == "ROLE_USER") return $this->redirectToRoute("user_list");

        try {
            // extrag info din db

            $all = $userRepository->findAll();
            $stationary = array();
            foreach ($all as $user) {
                $now = array();
                $now["id"] = $user->getId();
                $now["first_name"] = $user->getFirstName();
                $now["last_name"] = $user->getLastName();
                $now["email"] = $user->getEmail();
                $now["phone"] = $user->getPhone();
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
        } catch (Exception $e) {
            return $this->redirectToRoute("app_login");
        }
    }

    /**
     * @Route(" /profile/user", name="profile_user", methods={"GET", "POST"})
     */
    public function profile(DemandsRepository $demandsRepository)
    {
        try {
            if (!$this->getUser()) return $this->redirectToRoute("app_login");
            $demands = $demandsRepository -> findBy(['status' => 1]);
            return $this->render("users/profile.html.twig", array(
                    "user_display" => $this->getUser()->getFirstName(),
                    'profile' => $this->getUser()->getId(),
                    'person' => $this->getUser(),
                    'demands'=> $demands
                )
            );
        } catch (\Exception $e) {
            return $this->redirectToRoute("app_login");
        }

    }


    /**
     * @Route("/modal", methods={"POST", "GET"})
     */
    public function modal(DemandsRepository $demandsRepository) {
        $nr = $demandsRepository -> findAll();
        return new Response("blah");
    }

}