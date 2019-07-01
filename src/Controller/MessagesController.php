<?php

namespace App\Controller;

use App\Entity\Messages;
use App\Form\AddMessageType;
use App\Form\EditMessageType;
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
        if (!$this->getUser()) return $this->redirectToRoute("app_login");
        // daca un admin incearca sa intre pe user
        $rol = $this->getUser()->getRoles();
        if ($rol[0] == "ROLE_ADMIN") return $this->redirectToRoute("messages_admin");

        try {
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
            $mesaj = array("mesaj" => $param, "user_display" => $this->getUser()->getFirstName(), 'profile' => $this->getUser()->getId(), 'person' => $this->getUser());



            return $this->render('messages/display.html.twig', $mesaj);
        } catch (Exception $e) {
            return $this->redirectToRoute("app_login");
        }
    }


    /**
     * @Route("/admin/messages", name="messages_admin")
     */
    public function messagesAdmin()
    {
        if (!$this->getUser()) return $this->redirectToRoute("app_login");
        // daca incearca un user sa intre pe admin
        $rol = $this->getUser()->getRoles();
        if ($rol[0] != "ROLE_ADMIN") return $this->redirectToRoute("messages");

        try {
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
            $mesaj = array("mesaj" => $param, "user_display" => $this->getUser()->getFirstName(),'profile' => $this->getUser()->getId(), 'person' => $this->getUser());


            return $this->render('messages/displayAdmin.html.twig', $mesaj);
        } catch (Exception $e) {
            return $this->redirectToRoute("app_login");
        }
    }


    /**
     * @Route("/admin/addMessage", name="messages_admin_add")
     */
    public function addMessage(Request $request)
    {
        if (!$this->getUser()) return $this->redirectToRoute("app_login");
        // daca incearca un user sa intre pe admin
        $rol = $this->getUser()->getRoles();
        if ($rol[0] != "ROLE_ADMIN") return $this->redirectToRoute("messages");
        try {
            $form = $this->createForm(AddMessageType::class);
            $form->handleRequest($request);


            if($form->isSubmitted() && $form->isValid()) {
                /** @var $message Messages */
                $message = $form->getData();
                $message->setAdmin($this->getUser());
                $this -> entityManager->persist($message);
                $this -> entityManager->flush();
                return $this ->redirectToRoute("messages_admin");
            }
            return $this->render('messages/createMessage.html.twig', [
                'addMessage' => $form->createView(),
                'user_display' => $this->getUser()->getFirstName(),
                'profile' => $this->getUser()->getId(),
                'person' => $this->getUser()
            ]);
        } catch (Exception $e) {
            return $this->redirectToRoute("app_login");
        }
    }


    /**
     * @Route("/admin/messages/update/{id}", name="messages_admin_update", methods={"GET", "POST"})
     */
    public function updateMessage(Messages $message, MessagesRepository $messagesRepository, Request $request)
    {
        if (!$this->getUser()) return $this->redirectToRoute("app_login");
        if ($rol[0] != "ROLE_ADMIN") return $this->redirectToRoute("messages");
        try {
            // iau prin {id} obj de tip message si l dau in create form ca param ca sa interpreteze ca edit form
            $form = $this->createForm(EditMessageType::class,$message);
            $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid()) {
                /** @var $message Messages */
                $message->setAdmin($this->getUser());
                // nu mai e nevoie de persist
                $this -> entityManager->flush();
                return $this -> redirectToRoute("messages_admin");
            }
            return $this->render('messages/updateMessage.html.twig', [
                'editForm' => $form->createView(),
                "user_display" => $this->getUser()->getFirstName(),
                'profile' => $this->getUser()->getId(),
                'person' => $this->getUser(),
                'message' => $message
            ]);
        } catch (Exception $e) {
            return $this->redirectToRoute("app_login");
        }
    }

    /**
     * @Route("/admin/messages/update/{id}/post", name="messages_admin_update_post", methods={"POST", "GET"})
     */
    public function updatePost (Request $request, MessagesRepository $messagesRepository, $id) {
        if (!$this->getUser()) return $this->redirectToRoute("app_login");
        // daca incearca un user sa intre pe admin
        $rol = $this->getUser()->getRoles();
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
        } catch (\Exception $e) {
            return $this->redirectToRoute("messages_admin", array("error" => $e->getMessage()));
        }
        return $this->redirectToRoute("messages_admin");
    }


    /**
     * @Route("/admin/messages/delete/{id}", name="messages_admin_delete", methods={"GET", "POST"})
     */
    public function deleteMessage($id, MessagesRepository $messagesRepository)
    {
        if (!$this->getUser()) return $this->redirectToRoute("app_login");
        // daca incearca un user sa intre pe admin
        $rol = $this->getUser()->getRoles();
        if ($rol[0] != "ROLE_ADMIN") return $this->redirectToRoute("messages");
        try {
            $now = $messagesRepository->findOneBy(["id" => $id]);
            $this -> entityManager -> remove($now);
            $this -> entityManager -> flush();
            return $this->redirectToRoute("messages_admin");
        } catch (\Exception $e) {
            return $this->redirectToRoute("app_login");
        }
    }

}
