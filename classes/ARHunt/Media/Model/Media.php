<?php

namespace ARHunt\Media\Model;

class Media
{
    public $id;

    public $hunt;

    public $name;

    public $filename;

    public $type;

    public $subtype;

    public $length;

    public function fromArray($data)
    {
        $this->id = $data['id'];
        $this->hunt = $data['hunt'];
        $this->name = $data['name'];
        $this->filename = $data['filename'];
        $this->type = $data['type'];
        $this->subtype = $data['subtype'];
        $this->length = $data['length'];
        return $this;
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'hunt' => $this->hunt,
            'name' => $this->name,
            'filename' => $this->filename,
            'type' => $this->type,
            'subtype' => $this->subtype,
            'length' => $this->length,
        ];
    }

    public function getFileExtension()
    {
        if ($this->type === 'image') {
            switch ($this->subtype) {
                case 'bmp':
                    return 'bmp';
                case 'svg':
                    return 'svg';
                case 'jpeg':
                    return 'jpg';
                case 'png':
                    return 'png';
                case 'gif':
                    return 'gif';
            }
        } else
        if ($this->type === 'audio') {
            switch ($this->subtype) {
                case 'mp3':
                    return 'mp3';
            }
        } else
        if ($this->type === 'video') {
            switch ($this->subtype) {
                case 'mp4':
                    return 'mp4';
            }
        }
        return false;
    }

    public function toMinifiedObject()
    {
        return (object) [
            'id' => $this->id,
            'type' => $this->type,
            'subtype' => $this->subtype,
            'length' => $this->length,
        ];
    }

    public static function toMinifiedObjects($media)
    {
        return array_map(function($media) {
            return $media->toMinifiedObject();
        }, $media);
    }
}