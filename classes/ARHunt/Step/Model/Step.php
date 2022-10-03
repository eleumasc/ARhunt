<?php

namespace ARHunt\Step\Model;

class Step
{
    public $team;

    public $sequence;

    public $text;

    public $media;

    public $takeLat;

    public $takeLng;

    public $taken;

    public $takeTime;

    public $takeUser;

    public function fromArray($data)
    {
        $this->team = $data['team'];
        $this->sequence = $data['sequence'];
        $this->text = $data['text'];
        $this->media = $data['media'];
        $this->takeLat = $data['takeLat'];
        $this->takeLng = $data['takeLng'];
        $this->taken = $data['taken'];
        $this->takeTime = $data['takeTime'];
        $this->takeUser = $data['takeUser'];
        return $this;
    }

    public function toArray()
    {
        return [
            'team' => $this->team,
            'sequence' => $this->sequence,
            'text' => $this->text,
            'media' => $this->media,
            'takeLat' => $this->takeLat,
            'takeLng' => $this->takeLng,
            'taken' => $this->taken,
            'takeTime' => $this->takeTime,
            'takeUser' => $this->takeUser,
        ];
    }

    public function toMinifiedObject()
    {
        return (object) [
            'team' => $this->team,
            'sequence' => $this->sequence,
            'text' => $this->text,
            'media' => $this->media,
            'taken' => (boolean) $this->taken,
            'takeTime' => $this->takeTime,
            'takeUser' => $this->takeUser,
        ];
    }

    public static function toMinifiedObjects($steps)
    {
        return array_map(function($step) {
            return $step->toMinifiedObject();
        }, $steps);
    }
}