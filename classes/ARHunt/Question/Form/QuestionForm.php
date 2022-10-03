<?php

namespace ARHunt\Question\Form;

use Stdlib\Form\Form;

class QuestionForm extends Form
{
    private $edit = false;

    private $forEdit;

    private $id;

    private $team;

    private $sequence;

    private $text;

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
            if (!isset($data['team'])) {
                return $response->withStatus(400)
                                ->withJson([ 'status' => 'missing-team' ]);
            }
            $this->team = $data['team'];
            if (!isset($data['sequence'])) {
                return $response->withStatus(400)
                                ->withJson([ 'status' => 'missing-sequence' ]);
            }
            $this->sequence = $data['sequence'];
        }
        if (!isset($data['text'])) {
            $this->error = 'missing-text';
            return false;
        }
        $text = trim($data['text']);
        if (strlen($text) < 1) {
            $this->error = 'invalid-text';
            return false;
        }
        $this->text = $text;
        $this->valid = true;
        return true;
    }

    public function toQuestion($question)
    {
        if (!$this->valid) {
            return;
        }
        $question->text = $this->text;
        return $question;
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
}