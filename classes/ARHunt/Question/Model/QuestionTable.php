<?php

namespace ARHunt\Question\Model;

use ARHunt\Choice\Model\Choice;

class QuestionTable
{
    const ID_LENGTH = 16;

    protected $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getQuestionById($id)
    {
        $stmt = $this->db->prepare('SELECT * FROM questions WHERE id = :id');
        $stmt->execute([
            'id' => $id
        ]);
        if (!$stmt->rowCount()) {
            throw new \RuntimeException("Question with id `$id` doesn't exists.");
        }
        $questionArray = $stmt->fetch();
        return (new Question)->fromArray($questionArray);
    }

    public function getQuestionByChoice($choice)
    {
        $stmt = $this->db->prepare('SELECT questions.* FROM questions INNER JOIN choices ON (choices.question = questions.id) WHERE choices.id = :choice');
        $stmt->execute([
            'choice' => $choice->id
        ]);
        if (!$stmt->rowCount()) {
            throw new \RuntimeException("Choice with id `{$choice->id}` doesn't exists.");
        }
        $questionArray = $stmt->fetch();
        return (new Question)->fromArray($questionArray);
    }

    public function getQuestionsByStep($step)
    {
        $stmt = $this->db->prepare('SELECT * FROM questions WHERE team = :team AND sequence = :sequence');
        $stmt->execute([
            'team' => $step->team,
            'sequence' => $step->sequence
        ]);
        $questions = [];
        while ($questionArray = $stmt->fetch()) {
            $questions[] = (new Question)->fromArray($questionArray);
        }
        return $questions;
    }

    public function getQuestions()
    {
        $stmt = $this->db->prepare('SELECT * FROM questions');
        $stmt->execute();
        $questions = [];
        while ($questionArray = $stmt->fetch()) {
            $questions[] = (new Question)->fromArray($questionArray);
        }
        return $questions;
    }

    public function saveQuestion($question)
    {
        if ($question->id) {
            $stmt = $this->db->prepare('UPDATE questions SET team = :team, sequence = :sequence, text = :text WHERE id = :id');
        } else {
            $question->id = \Stdlib\UniqueId::generate(self::ID_LENGTH);
            $stmt = $this->db->prepare('INSERT INTO questions (id, team, sequence, text) VALUES (:id, :team, :sequence, :text)');
        }
        $stmt->execute($question->toArray());
        return ($stmt->rowCount() === 1);
    }

    public function deleteQuestion($question)
    {
        if (!$question->id) {
            return true;
        }
        $stmt = $this->db->prepare('DELETE FROM questions WHERE id = :id');
        $stmt->execute([
            'id' => $question->id
        ]);
        return ($stmt->rowCount() === 1);
    }

    public function isQuestionAnswered($question)
    {
        $stmt = $this->db->prepare('SELECT NOT EXISTS(SELECT * FROM choices WHERE choices.question = :question AND NOT choices.picked AND choices.right) `isQuestionAnswered`');
        $stmt->execute([
            'question' => $question->id
        ]);
        return ($stmt->fetch()['isQuestionAnswered'] == 1);
    }

    public function areAllQuestionsAnswered($step)
    {
        $stmt = $this->db->prepare('SELECT NOT EXISTS(SELECT * FROM choices INNER JOIN questions ON (questions.id = choices.question) WHERE questions.team = :team AND questions.sequence = :sequence AND NOT choices.picked AND choices.right) `allQuestionsAnswered`');
        $stmt->execute([
            'team' => $step->team,
            'sequence' => $step->sequence
        ]);
        return ($stmt->fetch()['allQuestionsAnswered'] == 1);
    }

    public function getPenaltyTimeout($step, $timeout = 30)
    {
        $stmt = $this->db->prepare('SELECT (:timeout - TIME_TO_SEC(TIMEDIFF(NOW(), choices.pickTime))) `timeout` FROM choices INNER JOIN questions ON (questions.id = choices.question) WHERE questions.team = :team AND questions.sequence = :sequence AND choices.picked AND NOT choices.right AND (:timeout - TIME_TO_SEC(TIMEDIFF(NOW(), choices.pickTime))) > 0');
        $stmt->execute([
            'team' => $step->team,
            'sequence' => $step->sequence,
            'timeout' => $timeout
        ]);
        if ($stmt->rowCount()) {
            return $stmt->fetch()['timeout'];
        } else {
            return false;
        }
    }
}