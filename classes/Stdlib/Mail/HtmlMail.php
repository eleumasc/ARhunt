<?php

namespace Stdlib\Mail;

class HtmlMail extends Mail
{
    public function send()
    {
        parent::setHeader('MIME-Version', '1.0');
        parent::setHeader('Content-type', 'text/html; charset=iso-8859-1');
        return parent::send();
    }
}