<?php

namespace Harvester\AdminBundle;

use Swift_Message;
use Swift_Mailer;
use Symfony\Component\Templating\EngineInterface;

class AdminMailer
{
    /**
     * @var \Swift_Mailer
     */
    protected $mailer;
    /**
     * @var \Symfony\Component\Templating\EngineInterface
     */
    protected $templating;

    /**
     * @param Swift_Mailer $mailer
     * @param EngineInterface $templating
     */
    public function __construct(Swift_Mailer $mailer, EngineInterface $templating)
    {
        $this->mailer = $mailer;
        $this->templating = $templating;
    }

    /**
     * Send an email.
     *
     * @param string $from
     * @param string $to
     * @param string $name
     * @param string $password
     * @param string $subject
     * @return int
     */
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