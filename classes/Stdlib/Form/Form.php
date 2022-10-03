<?php

namespace Stdlib\Form;

abstract class Form
{
    protected $valid;

    protected $error;

    abstract public function setData($data);

    public function isValid()
    {
        return $this->valid;
    }

    public function getError()
    {
        return $this->error;
    }
}