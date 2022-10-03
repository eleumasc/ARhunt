<?php

namespace ARHunt\Step\Form;

use Stdlib\Form\Form;

class StepForm extends Form
{
    private $edit = false;

    private $forEdit;

    private $team;

    private $sequence;

    private $text;

    private $media;

    private $takeLat;

    private $takeLng;

    public function setData($data)
    {
        $this->valid = false;
        $this->error = false;
        if (!isset($data['team'])) {
            $this->error = 'missing-team';
            return false;
        }
        $this->team = $data['team'];
        $this->forEdit = false;
        if ($this->edit) {
            $this->forEdit = true;
            if (!isset($data['sequence'])) {
                $this->error = 'missing-sequence';
                return false;
            }
            $this->sequence = $data['sequence'];
        }
        if (!isset($data['text'])) {
            $this->error = 'missing-text';
            return false;
        }
        $this->text = $data['text'];
        $this->media = (isset($data['media']) ? $data['media'] : null);
        if (!isset($data['takeLat'])) {
            $this->error = 'missing-take-lat';
            return false;
        }
        $takeLat = $data['takeLat'];
        if (!is_numeric($takeLat) || !is_numeric($takeLat) || $takeLat < -90 || $takeLat > 90) {
            $this->error = 'invalid-take-lat';
            return false;
        }
        $this->takeLat = $takeLat;
        if (!isset($data['takeLng'])) {
            $this->error = 'missing-take-lng';
            return false;
        }
        $takeLng = $data['takeLng'];
        if (!is_numeric($takeLng) || !is_numeric($takeLng) || $takeLng < -180 || $takeLng > 180) {
            $this->error = 'invalid-take-lng';
            return false;
        }
        $this->takeLng = $takeLng;
        $this->valid = true;
        return true;
    }

    public function toStep($step)
    {
        if (!$this->valid) {
            return;
        }
        $step->text = $this->text;
        $step->media = $this->media;
        $step->takeLat = $this->takeLat;
        $step->takeLng = $this->takeLng;
        return $step;
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

    public function getTeam()
    {
        return $this->team;
    }

    public function getSequence()
    {
        return $this->sequence;
    }

    public function getText()
    {
        return $this->text;
    }

    public function getMedia()
    {
        return $this->media;
    }

    public function getTakeLat()
    {
        return $this->takeLat;
    }

    public function getTakeLng()
    {
        return $this->takeLng;
    }
}