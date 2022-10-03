<?php

namespace ARHunt\Choice\Model;

class ChoiceTable
{
    const ID_LENGTH = 16;

    protected $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getChoiceById($id)
    {
        $stmt = $this->db->prepare('SELECT * FROM choices WHERE id = :id');
        $stmt->execute([
            'id' => $id
        ]);
        if (!$stmt->rowCount()) {
            throw new \RuntimeException("Choice with id `$id` doesn't exists.");
        }
        $choiceArray = $stmt->fetch();
        return (new Choice)->fromArray($choiceArray);
    }

    public function getChoicesByQuestion($question)
    {
        $stmt = $this->db->prepare('SELECT * FROM choices WHERE question = :question');
        $stmt->execute([
            'question' => $question->id
        ]);
        $choices = [];
        while ($choiceArray = $stmt->fetch()) {
            $choices[] = (new Choice)->fromArray($choiceArray);
        }
        return $choices;
    }

    public function getChoices()
    {
        $stmt = $this->db->prepare('SELECT * FROM choices');
        $stmt->execute();
        $choices = [];
        while ($choiceArray = $stmt->fetch()) {
            $choices[] = (new Choice)->fromArray($choiceArray);
        }
        return $choices;
    }

    public function saveChoice($choice)
    {
        if ($choice->id) {
            $stmt = $this->db->prepare('UPDATE choices SET question = :question, `right` = :right, text = :text, picked = :picked, pickTime = :pickTime, pickUser = :pickUser WHERE id = :id');
        } else {
            $choice->id = \Stdlib\UniqueId::generate(self::ID_LENGTH);
            $choice->picked = 0;
            $choice->pickTime = null;
            $choice->pickUser = null;
            $stmt = $this->db->prepare('INSERT INTO choices (id, question, `right`, text, picked, pickTime, pickUser) VALUES (:id, :question, :right, :text, :picked, :pickTime, :pickUser)');
        }
        $stmt->execute($choice->toArray());
        return ($stmt->rowCount() === 1);
    }

    public function deleteChoice($choice)
    {
        if (!$choice->id) {
            return true;
        }
        $stmt = $this->db->prepare('DELETE FROM choices WHERE id = :id');
        $stmt->execute([
            'id' => $choice->id
        ]);
        return ($stmt->rowCount() === 1);
    }

    public function getLastPickedChoice($step)
    {
        $stmt = $this->db->prepare('SELECT * FROM choices c1 INNER JOIN questions q1 ON (q1.id = c1.question) WHERE q1.team = :team AND q1.sequence = :sequence AND c1.pickTime = (SELECT MAX(c2.pickTime) FROM choices c2 INNER JOIN questions q2 ON (q2.id = c2.question) WHERE q2.team = q1.team AND q2.sequence = q1.sequence)');
        $stmt->execute([
            'team' => $step->team,
            'sequence' => $step->sequence
        ]);
        if ($stmt->rowCount()) {
            $choiceArray = $stmt->fetch();
            return (new Choice)->fromArray($choiceArray);
        } else {
            return null;
        }
    }
}