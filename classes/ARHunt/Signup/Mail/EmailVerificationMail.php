<?php

namespace ARHunt\Signup\Mail;

use Stdlib\Mail\HtmlMail;

class EmailVerificationMail extends HtmlMail
{
    protected $view;

    protected $user = null;

    public function __construct($view)
    {
        $this->view = $view;
    }

    public function setUser($user)
    {
        $this->user = $user;
    }

    public function send()
    {
        if (is_null($this->user)) {
            throw new \RuntimeException('Missing user.');
        }
        parent::setMessage($this->view->getEnvironment()->render('email-verification-mail.html.twig', [ 'user' => $this->user ]));
        return parent::send();
    }
}