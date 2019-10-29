<?php

namespace AllProgrammic\Bundle\ResqueBundle\Service;

use Twig\Environment;

/**
 * Class MailSender
 *
 * @package AllProgrammic\Bundle\ResqueBundle\Service
 */
class MailSender
{
    private $mailer;

    /**
     * @var Environment
     */
    private $twig;

    /**
     * MailSender constructor.
     *
     * @param $mailer
     * @param Environment $twig
     */
    public function __construct(Environment $twig, $mailer = null)
    {
        dump($mailer);
        dump($twig);
        die();

        $this->mailer = $mailer;
        $this->twig = $twig;
    }

    /**
     * @param $template
     * @param $data
     *
     * @return mixed
     */
    private function renderView($template, $data)
    {
        return $this->twig->renderView($template, $data);
    }

    /**
     * @return int
     */
    public function send()
    {
        $message = \Swift_Message::newInstance()
            ->setSubject('Hello Email')
            ->setFrom('send@example.com')
            ->setTo('recipient@example.com')
            ->setBody(
                $this->renderView('HelloBundle:Hello:email.txt.twig', array(
                    'name' => $name
                ))
            )
        ;

        return $this->mailer->send($message);
    }
}