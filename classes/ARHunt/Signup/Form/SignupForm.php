<?php

namespace ARHunt\Signup\Form;

use Stdlib\Form\Form;

class SignupForm extends Form
{
    private $email;

    private $nickname;

    private $password;

    public function setData($data)
    {
        $this->valid = false;
        $this->error = false;
        if (!isset($data['email'])) {
            $this->error = 'missing-email';
            return false;
        }
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->error = 'invalid-email';
            return false;
        }
        $this->email = $data['email'];
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

    public function getEmail()
    {
        return $this->email;
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