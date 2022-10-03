<?php

namespace ARHunt\Hunt\Form;

use Stdlib\Form\Form;

class HuntForm extends Form
{
    private $edit = false;

    private $forEdit;

    private $id;

    private $name;

    private $description;

    private $callTime;

    private $callLat;

    private $callLng;

    public function setData($data)
    {
        $this->valid = false;
        $this->error = false;
        $this->forEdit = false;
        if ($this->edit) {
            $this->forEdit = true;
            if (!isset($data['id'])) {
                $this->error = 'missing-id';
                return false;
            }
            $this->id = $data['id'];
        }
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
        if (!isset($data['description'])) {
            $this->error = 'missing-description';
            return false;
        }
        $this->description = $data['description'];
        if (!isset($data['callTime'])) {
            $this->error = 'missing-call-time';
            return false;
        }
        if (!\DateTime::createFromFormat('Y-m-d H:i:s', $data['callTime'])) {
            $this->error = 'invalid-call-time';
            return false;
        }
        $this->callTime = $data['callTime'];
        if (!isset($data['callLat'])) {
            $this->error = 'missing-call-lat';
            return false;
        }
        $callLat = $data['callLat'];
        if (!is_numeric($callLat) || !is_numeric($callLat) || $callLat < -90 || $callLat > 90) {
            $this->error = 'invalid-call-lat';
            return false;
        }
        $this->callLat = $callLat;
        if (!isset($data['callLng'])) {
            $this->error = 'missing-call-lng';
            return false;
        }
        $callLng = $data['callLng'];
        if (!is_numeric($callLng) || !is_numeric($callLng) || $callLng < -180 || $callLng > 180) {
            $this->error = 'invalid-call-lng';
            return false;
        }
        $this->callLng = $callLng;
        $this->valid = true;
        return true;
    }

    public function toHunt($hunt)
    {
        if (!$this->valid) {
            return;
        }
        $hunt->name = $this->name;
        $hunt->description = $this->description;
        $hunt->callTime = $this->callTime;
        $hunt->callLat = $this->callLat;
        $hunt->callLng = $this->callLng;
        return $hunt;
    }

    public function setEdit($edit)
    {
        $this->edit = $edit;
    }

    public function isEdit()
    {
        return $this->edit;
    }

    public function isForEdit()
    {
        return $this->forEdit;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getCallTime()
    {
        return $this->callTime;
    }

    public function getCallLat()
    {
        return $this->callLat;
    }

    public function getCallLng()
    {
        return $this->callLng;
    }
}