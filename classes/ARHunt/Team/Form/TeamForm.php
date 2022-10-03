<?php

namespace ARHunt\Team\Form;

use Stdlib\Form\Form;

class TeamForm extends Form
{
    private $edit = false;

    private $forEdit;

    private $id;

    private $hunt;

    private $name;

    private $color;

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
        } else {
            if (!isset($data['hunt'])) {
                return $response->withStatus(400)
                                ->withJson([ 'status' => 'missing-hunt' ]);
            }
            $this->hunt = $data['hunt'];
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
        if (!isset($data['color'])) {
            $this->error = 'missing-color';
            return false;
        }
        if (strlen($data['color']) !== 6 || !hexdec($data['color'])) {
            $this->error = 'invalid-color';
            return false;
        }
        $this->color = $data['color'];
        $this->valid = true;
        return true;
    }

    public function toTeam($team)
    {
        if (!$this->valid) {
            return;
        }
        $team->name = $this->name;
        $team->color = $this->color;
        return $team;
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

    public function getHunt()
    {
        return $this->hunt;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getColor()
    {
        return $this->color;
    }
}