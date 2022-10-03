<?php

namespace ARHunt\Choice\Form;

use Stdlib\Form\Form;

class ChoiceForm extends Form
{
    private $edit = false;

    private $forEdit;

    private $id;

    private $question;

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
            if (!isset($data['question'])) {
                return $response->withStatus(400)
                                ->withJson([ 'status' => 'missing-question' ]);
            }
            $this->question = $data['question'];
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

    public function toChoice($choice)
    {
        if (!$this->valid) {
            return;
        }
        $choice->text = $this->text;
        return $choice;
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

    public function getQuestion()
    {
        return $this->question;
    }

    public function getText()
    {
        return $this->text;
    }
}