<?php

namespace App\Controller;

use App\Entity\Messages;
use App\Repository\MessagesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MessagesController extends AbstractController
{

    private $entityManager;
    private $messagesRepository;

    public function __construct(EntityManagerInterface $entityManager, MessagesRepository $messagesRepository)
    {
        $this->entityManager = $entityManager;
        $this->messagesRepository = $messagesRepository;
    }

    /**
     * @Route("/user/messages", name="messages")
     */
    public function messages()
    {
        // daca un admin incearca sa intre pe user
        $rol = $this->getUser()->getRoles();
        if ($rol[0] == "ROLE_ADMIN") return $this->redirectToRoute("messages_admin");

        // randez twig de user
        $collection = $this->messagesRepository->findAll();
        $param = array();
        foreach ( $collection as $individual) {
            $now = array();
            $now["admin"] = $individual->getAdmin()->getEmail();
            $now["title"] = $individual->getTitle();
            $now["body"]  = $individual->getBody();
            array_push($param, $now);
        }
        $mesaj = array("mesaj" => $param, "user_display" => $this->getUser()->getFirstName());



        return $this->render('messages/display.html.twig', $mesaj);
    }


    /**
     * @Route("/admin/messages", name="messages_admin")
     */
    public function messagesAdmin()
    {

        // daca incearca un user sa intre pe admin
        $rol = $this->getUser()->getRoles();
        if ($rol[0] != "ROLE_ADMIN") return $this->redirectToRoute("messages");

        // randez twig de admin
        $collection = $this->messagesRepository->findAll();
        $param = array();
        foreach ( $collection as $individual) {
            $now = array();
            $now["id"] = $individual->getId();
            $now["admin"] = $individual->getAdmin()->getEmail();
            $now["title"] = $individual->getTitle();
            $now["body"]  = $individual->getBody();
            array_push($param, $now);
        }
        $mesaj = array("mesaj" => $param, "user_display" => $this->getUser()->getFirstName());



        return $this->render('messages/displayAdmin.html.twig', $mesaj);
    }


    /**
     * @Route("/admin/addMessage", name="messages_admin_add")
     */
    public function addMessage()
    {

        return $this->render('messages/createMessage.html.twig', array("user_display" => $this->getUser()->getFirstName()));
    }


    /**
     * @Route("/admin/addMessage/post", name="messages_admin_post", methods={"POST"})
     */
    public function postMessage(\Symfony\Component\HttpFoundation\Request $request)
    {
        // daca incearca un user sa intre pe admin
        $rol = $this->getUser()->getRoles();
        if ($rol[0] != "ROLE_ADMIN") return $this->redirectToRoute("messages");

        $title = $request->get("title");
        $message = $request->get("message");
        try {
            $now = new Messages();
            $now->setTitle($title);
            $now->setBody($message);
            $now->setAdmin($this->getUser());
            $this->entityManager->persist($now);
            $this->entityManager->flush();
        } catch (Exception $e) {
            return $this->redirectToRoute("messages_admin", array("error" => $e->getMessage()));
        }


        return $this->redirectToRoute("messages_admin");
    }




    /**
     * @Route("/admin/messages/update/{id}", name="messages_admin_update", methods={"GET"})
     */
    public function updateMessage($id, MessagesRepository $messagesRepository)
    {
        $now = $messagesRepository->findOneBy(["id" => $id]);

        return $this->render('messages/updateMessage.html.twig', array("id" => $id, "title" => $now->getTitle(), "body" => $now->getBody(), "user_display" => $this->getUser()->getFirstName()));
    }

    /**
     * @Route("/admin/messages/update/{id}/post", name="messages_admin_update_post", methods={"POST"})
     */
    public function updatePost (Request $request, MessagesRepository $messagesRepository, $id) {
        // daca incearca un user sa intre pe admin
        $rol = $this->getUser()->getRole();
        if ($rol[0] != "ROLE_ADMIN") return $this->redirectToRoute("messages");

        // update la mesaj
        $title = $request->get("title");
        $message = $request->get("message");
        try {
            $now = $messagesRepository->findOneBy(["id" => $id]);
            $now->setTitle($title);
            $now->setBody($message);
            $this->entityManager->persist($now);
            $this->entityManager->flush();
        } catch (Exception $e) {
            return $this->redirectToRoute("messages_admin", array("error" => $e->getMessage()));
        }
        return $this->redirectToRoute("messages_admin");
    }



}
