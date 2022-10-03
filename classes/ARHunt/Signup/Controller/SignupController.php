<?php

namespace ARHunt\Signup\Controller;

use ARHunt\Signup\Form\SignupForm;
use ARHunt\User\Model\UserTable;
use ARHunt\Signup\Mail\EmailVerificationMail;
use Stdlib\Mail\Address\MailAddress;

class SignupController
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function ajaxSignup($request, $response, $args)
    {
        $data = $request->getAttribute('__data');
        $form = new SignupForm();
        if (!$form->setData($data)) {
            return $response->withStatus(400)
                            ->withJson([ 'status' => $form->getError() ]);
        }
        $userTable = new UserTable($this->container->get('db'));
        try {
            $userTable->getUserByEmail($form->getEmail());
            return $response->withStatus(500)
                            ->withJson([ 'status' => 'email-already-exists' ]);
        } catch (\RuntimeException $ex) {
        }
        try {
            $userTable->getUserByNickname($form->getNickname());
            return $response->withStatus(500)
                            ->withJson([ 'status' => 'nickname-already-exists' ]);
        } catch (\RuntimeException $ex) {
        }
        try {
            $user = $userTable->createUser($form);
        } catch (\RuntimeException $ex) {
            return $response->withStatus(500)
                            ->withJson([ 'status' => 'user-not-created' ]);
        }
        $mail = new EmailVerificationMail($this->container->get('view'));
        $mail->addTo(new MailAddress($user->email, $user->nickname));
        $adminSettings = $this->container->get('settings')['arhunt']['admin'];
        $mail->setFrom(new MailAddress($adminSettings['email'], $adminSettings['name']));
        $mail->setSubject('Verifica il tuo indirizzo email');
        $mail->setUser($user);
        $mail->send();
        return $response->withJson([ 'status' => 'ok' ]);
    }

    public function signup($request, $response, $args)
    {
        $response = $this->container->get('view')->render($response, 'signup.html.twig', []);
        return $response;
    }
}