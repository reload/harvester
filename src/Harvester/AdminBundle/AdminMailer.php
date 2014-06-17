<?php

namespace Harvester\AdminBundle;

use Swift_Message;
use Swift_Mailer;
use Symfony\Component\Templating\EngineInterface;

class AdminMailer
{
    protected $mailer;
    protected $templating;

    public function __construct(Swift_Mailer $mailer, EngineInterface $templating)
    {
        $this->mailer = $mailer;
        $this->templating = $templating;
    }

    public function sendMail($from, $to, $name, $password, $subject)
    {
        $message = Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($from)
            ->setTo($to)
            ->setBody(
                $this->templating->render(
                    'HarvesterAdminBundle:Admin:email.html.twig',
                    array(
                        'name' => $name,
                        'password' => $password,
                    )
                )
            );

        return $this->mailer->send($message);
    }
}