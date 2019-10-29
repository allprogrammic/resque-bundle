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
    /**
     * @var null|\Swift_Mailer
     */
    private $mailer;

    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var string
     */
    private $subject;

    /**
     * @var string
     */
    private $from;

    /**
     * @var string
     */
    private $to;

    /**
     * MailSender constructor.
     *
     * @param $mailer
     * @param Environment $twig
     */
    public function __construct(Environment $twig, $mailer = null, $subject, $from, $to)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->subject = $subject;
        $this->from = $from;
        $this->to = $to;
    }

    /**
     * @param $template
     * @param $data
     *
     * @return mixed
     */
    private function renderView($template, $data)
    {
        return $this->twig->render($template, $data);
    }

    /**
     * @param $template
     * @param array $data
     *
     * @return mixed
     */
    public function send($template, $data = array())
    {
        /** @var $template string */
        $template = sprintf('@AllProgrammicResque/mails/%s.html.twig',
            $template
        );

        $data = array_merge($data, [
            'subject' => $this->subject
        ]);

        $message = (new \Swift_Message($this->subject))
            ->setFrom($this->from)
            ->setTo($this->to)
            ->setBody($this->renderView($template, $data), 'text/html');

        return $this->mailer->send($message);
    }
}