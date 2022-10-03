<?php

namespace ARHunt\Hunt\Model;

class Hunt
{
    const EDITING = 0;

    const PUBLISHED = 1;

    const CALLING = 2;

    const PREPARING = 3;

    const WAITING = 4;

    const PLAYING = 5;

    const CLOSED = 6;

    const CANCELLED = 7;

    public $id;

    public $name;

    public $description;

    public $author;

    public $callTime;

    public $callLat;

    public $callLng;

    public $playTime;

    public $status;

    public function fromArray($array)
    {
        $this->id = $array['id'];
        $this->name = $array['name'];
        $this->description = $array['description'];
        $this->author = $array['author'];
        $this->callTime = $array['callTime'];
        $this->callLat = $array['callLat'];
        $this->callLng = $array['callLng'];
        $this->playTime = $array['playTime'];
        $this->status = $array['status'];
        return $this;
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'author' => $this->author,
            'callTime' => $this->callTime,
            'callLat' => $this->callLat,
            'callLng' => $this->callLng,
            'playTime' => $this->playTime,
            'status' => $this->status,
        ];
    }

    public function getStatusName()
    {
        switch ($this->status) {
            case self::EDITING:
                return 'REDAZIONE';
            case self::PUBLISHED:
                return 'PUBBLICATA';
            case self::CALLING:
                return 'CHIAMATA';
            case self::PREPARING:
                return 'PREPARAZIONE';
            case self::WAITING:
                return 'IN ATTESA';
            case self::PLAYING:
                return 'IN CORSO';
            case self::CLOSED:
                return 'CHIUSA';
            case self::CANCELLED:
                return 'ANNULLATA';
            default:
                return '?';
        }
    }

    public function toMinifiedObject()
    {
        return (object) [
            'id' => $this->id,
            'name' => $this->name,
            'author' => $this->author,
            'playTime' => $this->playTime,
            'status' => $this->status,
        ];
    }

    public static function toMinifiedObjects($hunts)
    {
        return array_map(function($hunt) {
            return $hunt->toMinifiedObject();
        }, $hunts);
    }
}