<?php

namespace ARHunt\Media\Model;

class MediaTable
{
    const ID_LENGTH = 16;

    protected $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getMediaById($id)
    {
        $stmt = $this->db->prepare('SELECT * FROM media WHERE id = :id');
        $stmt->execute([
            'id' => $id
        ]);
        if (!$stmt->rowCount()) {
            throw new \RuntimeException("Media with id `$id` doesn't exists.");
        }
        $mediaArray = $stmt->fetch();
        return (new Media)->fromArray($mediaArray);
    }

    public function getMediaByHunt($hunt)
    {
        $stmt = $this->db->prepare('SELECT * FROM media WHERE hunt = :hunt');
        $stmt->execute([
            'hunt' => $hunt->id
        ]);
        $media = [];
        while ($mediaArray = $stmt->fetch()) {
            $media[] = (new Media)->fromArray($mediaArray);
        }
        return $media;
    }

    public function getMedia()
    {
        $stmt = $this->db->prepare('SELECT * FROM media');
        $stmt->execute();
        $media = [];
        while ($mediaArray = $stmt->fetch()) {
            $media[] = (new Media)->fromArray($mediaArray);
        }
        return $media;
    }

    public function saveMedia($media)
    {
        if ($media->id) {
            $stmt = $this->db->prepare('UPDATE media SET hunt = :hunt, name = :name, filename = :filename, type = :type, subtype = :subtype, length = :length WHERE id = :id');
        } else {
            $media->id = \Stdlib\UniqueId::generate(self::ID_LENGTH);
            $stmt = $this->db->prepare('INSERT INTO media (id, hunt, name, filename, type, subtype, length) VALUES (:id, :hunt, :name, :filename, :type, :subtype, :length)');
        }
        $stmt->execute($media->toArray());
        return ($stmt->rowCount() === 1);
    }

    public function deleteMedia($media)
    {
        if (!$media->id) {
            return true;
        }
        $stmt = $this->db->prepare('DELETE FROM media WHERE id = :id');
        $stmt->execute([
            'id' => $media->id
        ]);
        return ($stmt->rowCount() === 1);
    }

    public function getUsedSpace($hunt)
    {
        $stmt = $this->db->prepare('SELECT SUM(length) `usedSpace` FROM media WHERE hunt = :hunt');
        $stmt->execute([
            'hunt' => $hunt->id
        ]);
        return $stmt->fetch()['usedSpace'];
    }
}