<?php

namespace ARHunt\Team\Model;

class Team
{
    public $id;

    public $hunt;

    public $name;

    public $color;

    public $sequence;

    public $closed;

    public $closeTime;

    public function fromArray($data)
    {
        $this->id = $data['id'];
        $this->hunt = $data['hunt'];
        $this->name = $data['name'];
        $this->color = $data['color'];
        $this->sequence = $data['sequence'];
        $this->closed = $data['closed'];
        $this->closeTime = $data['closeTime'];
        return $this;
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'hunt' => $this->hunt,
            'name' => $this->name,
            'color' => $this->color,
            'sequence' => $this->sequence,
            'closed' => $this->closed,
            'closeTime' => $this->closeTime,
        ];
    }

    public function getTextColor()
    {
        return \Stdlib\Color::getContrastColor($this->color);
    }

    public function toMinifiedObject()
    {
        return (object) [
            'id' => $this->id,
            'name' => $this->name,
            'color' => $this->color,
            'textColor' => $this->getTextColor(),
            'sequence' => $this->sequence,
            'closed' => (boolean) $this->closed,
            'closeTime' => $this->closeTime,
        ];
    }

    public static function toMinifiedObjects($teams)
    {
        return array_map(function($team) {
            return $team->toMinifiedObject();
        }, $teams);
    }
}