<?php

namespace ARHunt\Choice\Model;

class Choice
{
    public $id;

    public $question;

    public $right;

    public $text;

    public $picked;

    public $pickTime;

    public $pickUser;

    public function fromArray($data)
    {
        $this->id = $data['id'];
        $this->question = $data['question'];
        $this->right = $data['right'];
        $this->text = $data['text'];
        $this->picked = $data['picked'];
        $this->pickTime = $data['pickTime'];
        $this->pickUser = $data['pickUser'];
        return $this;
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'question' => $this->question,
            'right' => $this->right,
            'text' => $this->text,
            'picked' => $this->picked,
            'pickTime' => $this->pickTime,
            'pickUser' => $this->pickUser,
        ];
    }

    public function toMinifiedObject()
    {
        return (object) [
            'id' => $this->id,
            'right' => $this->picked ? (boolean) $this->right : null,
            'text' => $this->text,
            'picked' => (boolean) $this->picked,
            'pickTime' => $this->pickTime,
            'pickUser' => $this->pickUser,
        ];
    }

    public static function toMinifiedObjects($choices)
    {
        return array_map(function($choice) {
            return $choice->toMinifiedObject();
        }, $choices);
    }
}