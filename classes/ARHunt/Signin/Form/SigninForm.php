<?php

namespace ARHunt\Signin\Form;

use Stdlib\Form\Form;

class SigninForm extends Form
{
    private $nickname;

    private $password;

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
        if (!isset($data['password'])) {
            $this->error = 'missing-password';
            return false;
        }
        if (!preg_match('/^.{8,128}$/', $data['password'])) {
            $this->error = 'invalid-password';
            return false;
        }
        $this->password = $data['password'];
        $this->valid = true;
        return true;
    }

    public function getNickname()
    {
        return $this->nickname;
    }

    public function getPassword()
    {
        return $this->password;
    }
}