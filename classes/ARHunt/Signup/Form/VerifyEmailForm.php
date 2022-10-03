<?php

namespace ARHunt\Signup\Form;

use Stdlib\Form\Form;

class VerifyEmailForm extends Form
{
    private $nickname;

    private $emailVerificationCode;

    public function setData($data)
    {
        $this->valid = false;
        $this->error = false;
        if (!isset($data['nickname'])) {
            $this->error = 'missing-nickname';
            return false;
        }
        if (!preg_match('/^[A-Za-z0-9]{4,24}$/', $data['nickname'])) {
            $this->error = 'invalid-nickname';
            return false;
        }
        $this->nickname = $data['nickname'];
        if (!isset($data['evc'])) {
            $this->error = 'missing-evc';
            return false;
        }
        if (strlen($data['evc']) !== \ARHunt\User\Model\UserTable::EMAIL_VERIFICATION_CODE_LENGTH) {
            $this->error = 'invalid-evc';
            return false;
        }
        $this->emailVerificationCode = $data['evc'];
        $this->valid = true;
        return true;
    }

    public function getNickname()
    {
        return $this->nickname;
    }

    public function getEmailVerificationCode()
    {
        return $this->emailVerificationCode;
    }
}