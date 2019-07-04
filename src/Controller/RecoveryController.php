<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\MailService;
use App\Service\RecoveryService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class RecoveryController extends AbstractController
{

    private $recoveryService;
    private $userRepository;
    private $mailer;

    public function __construct(RecoveryService $recoveryService, UserRepository $userRepository, MailService $mailer)
    {
        $this->recoveryService = $recoveryService;
        $this->userRepository = $userRepository;
        $this->mailer = $mailer;
    }

    /**
     * @Route("/recoveryRequest", name="recoveryRequest", methods={"POST"})
     */
    public function recoveryRequest(Request $request)
    {
        // prepare for validation
        $email = $request -> get("recoveryEmail");
        $token = $this -> recoveryService -> getRandomToken();
        $user = $this->userRepository->findOneBy(['email' => $email]);
        $this->recoveryService->giveToken($user, $token);

        // send a email
        $this->mailer->mail(array($user->getEmail()), "Password recovery", "recovery/recovery.html.twig", array('token' => $token, 'base_href' => $request->getSchemeAndHttpHost().'/'));
        return $this -> redirectToRoute("app_login");
    }


    /**
     * @Route("/formRecovery/{token}", name="formRecovery", methods={"GET", "POST"})
     */
    public function formRecovery($token, Request $request) {
        if(count($this -> userRepository -> findOneBy(["token" => $token])) == 0) return $this->redirectToRoute("app_login");
        return $this->render("recovery/reset.html.twig", array('token' => $token, 'base_href' => $request->getSchemeAndHttpHost().'/'));
    }


    /**
     * @Route("/performRecovery/{token}", name="performRecovery", methods={"GET", "POST"})
     */
    public function performRecovery($token, Request $request)
    {
        $newPassword = $request->get("newPassword");
        $user = $this -> userRepository -> findOneBy(["token" => $token]);
        $this->recoveryService->changePassword($user, $newPassword);
        return  $this->redirectToRoute("app_login");
    }


}
