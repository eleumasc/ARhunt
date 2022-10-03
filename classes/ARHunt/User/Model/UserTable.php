<?php

namespace ARHunt\User\Model;

class UserTable
{
    const PASSWORD_HASH_ALGO = 'sha256';

    const EMAIL_VERIFICATION_CODE_LENGTH = 16;

    protected $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function createUser($form)
    {
        $userArray = [
            'nickname' => $form->getNickname(),
            'password' => hash(self::PASSWORD_HASH_ALGO, $form->getPassword()),
            'email' => $form->getEmail(),
            'emailVerificationCode' => \Stdlib\RandomString::generate(self::EMAIL_VERIFICATION_CODE_LENGTH),
            'signupTime' => (new \DateTime)->format('Y-m-d H:i:s')
        ];
        $stmt = $this->db->prepare('INSERT INTO users (nickname, password, email, emailVerificationCode, signupTime) VALUES (:nickname, :password, :email, :emailVerificationCode, :signupTime)');
        $stmt->execute($userArray);
        if (!$stmt->rowCount()) {
            throw new \RuntimeException("Unable to create user with nickname `{$form->getNickname()}` and email `{$form->getEmail()}`.");
        }
        $userArray['emailVerified'] = 0;
        $userArray['signinTime'] = null;
        $userArray['lastSigninTime'] = null;
        return (new User)->fromArray($userArray);
    }

    public function getUserByNickname($nickname)
    {
        $stmt = $this->db->prepare('SELECT nickname, email, emailVerified, emailVerificationCode, signupTime, signinTime, lastSigninTime FROM users WHERE nickname = :nickname');
        $stmt->execute([
            'nickname' => $nickname
        ]);
        if (!$stmt->rowCount()) {
            throw new \RuntimeException("User with nickname `$nickname` doesn't exists.");
        }
        $userArray = $stmt->fetch();
        return (new User)->fromArray($userArray);
    }

    public function getUserByEmail($email)
    {
        $stmt = $this->db->prepare('SELECT nickname, email, emailVerified, emailVerificationCode, signupTime, signinTime, lastSigninTime FROM users WHERE email = :email');
        $stmt->execute([
            'email' => $email
        ]);
        if (!$stmt->rowCount()) {
            throw new \RuntimeException("User with email `$email` doesn't exists.");
        }
        $userArray = $stmt->fetch();
        return (new User)->fromArray($userArray);
    }

    public function getUsers()
    {
        $stmt = $this->db->prepare('SELECT nickname, email, emailVerified, emailVerificationCode, signupTime, signinTime, lastSigninTime FROM users');
        $stmt->execute();
        $users = [];
        while ($userArray = $stmt->fetch()) {
            $users[] = (new User)->fromArray($userArray);
        }
        return $users;
    }

    public function verifyPassword($user, $password)
    {
        sleep(1);
        $stmt = $this->db->prepare('SELECT EXISTS(SELECT * FROM users WHERE nickname = :nickname AND password = :password) `password_verified`');
        $stmt->execute([
            'nickname' => $user->nickname,
            'password' => hash(self::PASSWORD_HASH_ALGO, $password)
        ]);
        return ($stmt->fetch()['password_verified'] == 1);
    }

    public function changeNickname($user, $nickname)
    {
        $stmt = $this->db->prepare('UPDATE users SET nickname = :newNickname WHERE nickname = :nickname');
        $stmt->execute([
            'nickname' => $user->nickname,
            'newNickname' => $nickname
        ]);
        if ($stmt->rowCount()) {
            $user->nickname = $nickname;
        }
        return ($stmt->rowCount() === 1);
    }

    public function changePassword($user, $password)
    {
        $stmt = $this->db->prepare('UPDATE users SET password = :newPassword WHERE nickname = :nickname');
        $stmt->execute([
            'nickname' => $user->nickname,
            'newPassword' => hash(self::PASSWORD_HASH_ALGO, $password)
        ]);
        return ($stmt->rowCount() === 1);
    }

    public function verifyEmail($user, $emailVerificationCode)
    {
        sleep(1);
        $stmt = $this->db->prepare('UPDATE users SET emailVerified = 1 WHERE nickname = :nickname AND emailVerificationCode = :emailVerificationCode');
        $stmt->execute([
            'nickname' => $user->nickname,
            'emailVerificationCode' => $emailVerificationCode
        ]);
        if ($stmt->rowCount()) {
            $user->emailVerified = 1;
        }
        return ($stmt->rowCount() === 1);
    }

    public function updateSigninTime($user)
    {
        $newSigninTime = (new \DateTime())->format('Y-m-d H:i:s');
        $stmt = $this->db->prepare('UPDATE users SET lastSigninTime = signinTime, signinTime = :newSigninTime WHERE nickname = :nickname');
        $stmt->execute([
            'nickname' => $user->nickname,
            'newSigninTime' => $newSigninTime
        ]);
        if ($stmt->rowCount()) {
            $user->lastSigninTime = $user->signinTime;
            $user->signinTime = $newSigninTime;
        }
        return ($stmt->rowCount() === 1);
    }
}