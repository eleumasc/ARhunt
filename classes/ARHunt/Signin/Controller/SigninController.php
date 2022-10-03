<?php

namespace ARHunt\Signin\Controller;

use ARHunt\Signin\Form\SigninForm;
use ARHunt\User\Model\UserTable;

class SigninController
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function ajaxSignin($request, $response, $args)
    {
        $data = $request->getAttribute('__data');
        $form = new SigninForm();
        if (!$form->setData($data)) {
            return $response->withStatus(400)
                            ->withJson([ 'status' => $form->getError() ]);
        }
        $userTable = new UserTable($this->container->get('db'));
        try {
            $user = $userTable->getUserByNickname($form->getNickname());
        } catch (\RuntimeException $ex) {
            return $response->withStatus(404)
                            ->withJson([ 'status' => 'user-not-found' ]);
        }
        if (!$user->emailVerified) {
            return $response->withStatus(403)
                            ->withJson([ 'status' => 'email-not-verified' ]);
        }
        if ($userTable->verifyPassword($user, $form->getPassword())) {
            $_SESSION['user'] = $user;
            $userTable->updateSigninTime($user);
            return $response->withJson([ 'status' => 'ok' ]);
        } else {
            return $response->withStatus(403)
                            ->withJson([ 'status' => 'wrong-password' ]);
        }
    }

    public function signin($request, $response, $args)
    {
        $response = $this->container->get('view')->render($response, 'signin.html.twig', []);
        return $response;
    }
}