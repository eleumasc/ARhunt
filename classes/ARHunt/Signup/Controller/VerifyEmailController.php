<?php

namespace ARHunt\Signup\Controller;

use ARHunt\Signup\Form\VerifyEmailForm;
use ARHunt\User\Model\UserTable;

class VerifyEmailController
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function verifyEmail($request, $response, $args)
    {
        $data = $request->getQueryParams();
        $verifyEmailAux = function() use ($data) {
            $form = new VerifyEmailForm();
            if (!$form->setData($data)) {
                return $form->getError();
            }
            $userTable = new UserTable($this->container->get('db'));
            try {
                $user = $userTable->getUserByNickname($form->getNickname());
            } catch (\RuntimeException $ex) {
                return 'wrong-nickname';
            }
            if ($user->emailVerified || $userTable->verifyEmail($user, $form->getEmailVerificationCode())) {
                return 'ok';
            } else {
                return 'wrong-evc';
            }
        };
        $result = $verifyEmailAux();
        $response = $this->container->get('view')->render($response, 'verify-email.html.twig', [
            'result' => $result
        ]);
        return $response;
    }
}