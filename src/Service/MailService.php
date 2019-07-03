<?php


namespace App\Service;


use Twig\Environment;

class MailService
{

    private $mailer;
    private $senderEmail;
    private $twigEnvironment;

    public function __construct(\Swift_Mailer $mailer, Environment $twigEnvironment, string $defaultSenderEmail)
    {
        $this->mailer = $mailer;
        $this->senderEmail = $defaultSenderEmail;
        $this->twigEnvironment=$twigEnvironment;
    }

    /**
     * @param string $target_person
     * @param string $message_title
     * @param string $twigPath
     * @param array $twigParameters
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function mail(array $recipients, string $message_title, string $twigPath, array $twigParameters=[]): void
    {
        $message = (new \Swift_Message($message_title))
            ->setFrom($this->senderEmail)
            ->setTo($recipients)
            ->setBody(
                $this->twigEnvironment->render($twigPath, $twigParameters),
                'text/html'
            );
        $this->mailer->send($message);

    }
}