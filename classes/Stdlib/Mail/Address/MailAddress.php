<?php

namespace Stdlib\Mail\Address;

class MailAddress
{
    private $email;

    private $name;

    public function __construct($email, $name = false)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \RuntimeException("Invalid email: $email");
        }
        $this->email = $email;
        $this->name = ($name ? trim($name) : false);
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getName()
    {
        return $this->name;
    }

    public function __toString()
    {
        if ($this->name) {
            return "{$this->name} <{$this->email}>";
        } else {
            return $this->email;
        }
    }
}