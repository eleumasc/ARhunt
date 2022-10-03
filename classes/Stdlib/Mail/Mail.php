<?php

namespace Stdlib\Mail;

class Mail
{
    protected $headers = [];

    protected $to = [];

    protected $from = null;

    protected $cc = [];

    protected $bcc = [];

    protected $subject;

    protected $message;

    public function setHeader($name, $value)
    {
        $this->headers[strtolower(trim($name))] = trim($value);
        return $this;
    }

    public function addTo($address)
    {
        $this->to[] = $address;
        return $this;
    }

    public function setFrom($address)
    {
        $this->from = $address;
        return $this;
    }

    public function addCc($address)
    {
        $this->cc[] = $address;
        return $this;
    }

    public function addBcc($address)
    {
        $this->bcc[] = $address;
        return $this;
    }

    public function setSubject($subject)
    {
        $this->subject = trim($subject);
        return $this;
    }

    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    public function send()
    {
        if (empty($this->to)) {
            throw new \RuntimeException('Missing recipient.');
        }
        if (is_null($this->from)) {
            throw new \RuntimeException('Missing sender.');
        }

        $mailAddressToString = function($address) {
            return (string) $address;
        };
        $mailAddressGetEmail = function($address) {
            return $address->getEmail();
        };
        $headersToString = function($name, $value) {
            return "$name: $value";
        };

        $this->setHeader('To', implode(', ', array_map($mailAddressToString, $this->to)));
        $this->setHeader('From', $mailAddressToString($this->from));
        if (!empty($this->cc)) {
            $this->setHeader('Cc', implode(', ', array_map($mailAddressToString, $this->cc)));
        }
        if (!empty($this->bcc)) {
            $this->setHeader('Bcc', implode(', ', array_map($mailAddressToString, $this->bcc)));
        }

        return @mail(
            implode(', ', array_map($mailAddressGetEmail, $this->to)),
            $this->subject,
            $this->message,
            implode("\r\n", array_map($headersToString, array_keys($this->headers), $this->headers)));
    }
}