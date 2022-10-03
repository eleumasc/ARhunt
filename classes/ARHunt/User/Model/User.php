<?php

namespace ARHunt\User\Model;

class User
{
    public $nickname;

    public $email;

    public $emailVerified;

    public $emailVerificationCode;

    public $signupTime;

    public $signinTime;

    public $lastSigninTime;

    public function fromArray($array)
    {
        $this->nickname = $array['nickname'];
        $this->email = $array['email'];
        $this->emailVerified = $array['emailVerified'];
        $this->emailVerificationCode = $array['emailVerificationCode'];
        $this->signupTime = $array['signupTime'];
        $this->signinTime = $array['signinTime'];
        $this->lastSigninTime = $array['lastSigninTime'];
        return $this;
    }

    public function toArray()
    {
        return [
            'nickname' => $this->nickname,
            'email' => $this->email,
            'emailVerified' => $this->emailVerified,
            'emailVerificationCode' => $this->emailVerificationCode,
            'signupTime' => $this->signupTime,
            'signinTime' => $this->signinTime,
            'lastSigninTime' => $this->lastSigninTime,
        ];
    }

    public function toMinifiedObject()
    {
        return (object) [
            'nickname' => $this->nickname
        ];
    }

    public static function toMinifiedObjects($users)
    {
        return array_map(function($user) {
            return $user->toMinifiedObject();
        }, $users);
    }
}