<?php

namespace ARHunt\Media\Form;

use Stdlib\Form\Form;

class MediaForm extends Form
{
    private $id;

    private $name;

    public function setData($data)
    {
        $this->valid = false;
        $this->error = false;
        if (!isset($data['id'])) {
            $this->error = 'missing-id';
            return false;
        }
        $this->id = $data['id'];
        if (!isset($data['name'])) {
            $this->error = 'missing-name';
            return false;
        }
        $name = trim($data['name']);
        if (strlen($name) < 1 || strlen($name) > 120) {
            $this->error = 'invalid-name';
            return false;
        }
        $this->name = $name;
        $this->valid = true;
        return true;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }
}