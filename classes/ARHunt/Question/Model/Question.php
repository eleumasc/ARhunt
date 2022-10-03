<?php

namespace ARHunt\Question\Model;

class Question
{
    public $id;

    public $team;

    public $sequence;

    public $text;

    public function fromArray($data)
    {
        $this->id = $data['id'];
        $this->team = $data['team'];
        $this->sequence = $data['sequence'];
        $this->text = $data['text'];
        return $this;
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'team' => $this->team,
            'sequence' => $this->sequence,
            'text' => $this->text,
        ];
    }

    public function toMinifiedObject()
    {
        return (object) [
            'id' => $this->id,
            'text' => $this->text,
        ];
    }

    public static function toMinifiedObjects($questions)
    {
        return array_map(function($question) {
            return $question->toMinifiedObject();
        }, $questions);
    }
}