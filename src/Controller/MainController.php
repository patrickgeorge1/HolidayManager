<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="slash")
     */
    public function index()
    {
        // verifica daca e logat
        if (!$this->getUser()) return $this->redirectToRoute("app_login");


        // verifica rol
        /*
        if ($this->isGranted("ROLE_ADMIN"))
            return $this->render('main/index.html.twig', [
                'controller_name' => 'MainController',
            ]);
        else
            return new Response("Nu esti admin");
        */


        return $this->render('main/index.html.twig', [

        ]);
    }

    /**
     * @Route("/mail", name="mail", methods={"GET", "POST"})
     */
    public function mail(\Swift_Mailer $mailer, Request $request) {
        $text = $request->get("mesaj");

        $message = (new \Swift_Message($request->get("title")))
            ->setFrom('msend28@gmail.com')
            ->setTo($request->get("recipient"))
            ->setBody(
                $text,
                'text/plain'
            );

        $mailer->send($message);

        return new Response("mail sent!");
    }


    /**
     * @Route("/messages", name="messages", methods={"GET", "POST"})
     */
    public function messages () {
        return $this->render("main/messages.html.twig");
    }
}