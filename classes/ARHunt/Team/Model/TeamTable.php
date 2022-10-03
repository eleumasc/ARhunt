<?php

namespace ARHunt\Team\Model;

use ARHunt\Hunt\Model\HuntTable;
use ARHunt\Step\Model\StepTable;

class TeamTable
{
    const ID_LENGTH = 16;

    protected $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getTeamById($id)
    {
        $stmt = $this->db->prepare('SELECT * FROM teams WHERE id = :id');
        $stmt->execute([
            'id' => $id
        ]);
        if (!$stmt->rowCount()) {
            throw new \RuntimeException("Team with id `$id` doesn't exists.");
        }
        $teamArray = $stmt->fetch();
        return (new Team)->fromArray($teamArray);
    }

    public function getTeamByPlayer($hunt, $user)
    {
        $stmt = $this->db->prepare('SELECT teams.* FROM teams INNER JOIN players ON (players.team = teams.id) WHERE players.hunt = :hunt AND players.user = :user');
        $stmt->execute([
            'hunt' => $hunt->id,
            'user' => $user->nickname
        ]);
        if (!$stmt->rowCount()) {
            throw new \RuntimeException("User with nickname `{$user->nickname}` is not a player of hunt with id `{$hunt->id}`.");
        }
        $teamArray = $stmt->fetch();
        return (new Team)->fromArray($teamArray);
    }

    public function getTeamsByHunt($hunt)
    {
        $stmt = $this->db->prepare('SELECT * FROM teams WHERE hunt = :hunt');
        $stmt->execute([
            'hunt' => $hunt->id
        ]);
        $teams = [];
        while ($teamArray = $stmt->fetch()) {
            $teams[] = (new Team)->fromArray($teamArray);
        }
        return $teams;
    }

    public function getTeams()
    {
        $stmt = $this->db->prepare('SELECT * FROM teams');
        $stmt->execute();
        $teams = [];
        while ($teamArray = $stmt->fetch()) {
            $teams[] = (new Team)->fromArray($teamArray);
        }
        return $teams;
    }

    public function saveTeam($team)
    {
        if ($team->id) {
            $stmt = $this->db->prepare('UPDATE teams SET hunt = :hunt, name = :name, color = :color, sequence = :sequence, closed = :closed, closeTime = :closeTime WHERE id = :id');
        } else {
            $team->id = \Stdlib\UniqueId::generate(self::ID_LENGTH);
            $team->sequence = 1;
            $team->closed = 0;
            $team->closeTime = null;
            $stmt = $this->db->prepare('INSERT INTO teams (id, hunt, name, color, sequence, closed, closeTime) VALUES (:id, :hunt, :name, :color, :sequence, :closed, :closeTime)');
        }
        $stmt->execute($team->toArray());
        return ($stmt->rowCount() === 1);
    }

    public function deleteTeam($team)
    {
        if (!$team->id) {
            return true;
        }
        $stmt = $this->db->prepare('DELETE FROM teams WHERE id = :id');
        $stmt->execute([
            'id' => $team->id
        ]);
        return ($stmt->rowCount() === 1);
    }

    public function getPathLength($team)
    {
        $length = 0;
        $huntTable = new HuntTable($this->db);
        $stepTable = new StepTable($this->db);
        $hunt = $huntTable->getHuntById($team->hunt);
        $steps = $stepTable->getStepsByTeam($team);
        $lat1 = $hunt->callLat;
        $lng1 = $hunt->callLng;
        foreach ($steps as $step) {
            $lat2 = $step->takeLat;
            $lng2 = $step->takeLng;
            $length += \Stdlib\GeoCoordinate::haversineGreatCircleDistance($lat1, $lng1, $lat2, $lng2);
            $lat1 = $lat2;
            $lng1 = $lng2;
        }
        return $length;
    }

    public function getNextSequence($team)
    {
        $stmt = $this->db->prepare('SELECT steps.sequence `nextSequence` FROM steps WHERE steps.team = :team AND steps.sequence = (SELECT (teams.sequence + 1) FROM teams WHERE teams.id = steps.team)');
        $stmt->execute([
            'team' => $team->id
        ]);
        if ($stmt->rowCount()) {
            return $stmt->fetch()['nextSequence'];
        } else {
            return false;
        }
    }

    public function getStatsByTeam($team)
    {
        $stmt = $this->db->prepare('SELECT players.user `player`, A.stepsTaken, B.rightChoicesPicked, B.wrongChoicesPicked FROM players INNER JOIN (SELECT players.user, SUM(IF(players.user = steps.takeUser, 1, 0)) `stepsTaken` FROM players INNER JOIN steps ON (steps.team = players.team) GROUP BY players.user) A ON (A.user = players.user) INNER JOIN (SELECT players.user, SUM(IF(choices.right AND players.user = choices.pickUser, 1, 0)) `rightChoicesPicked`, SUM(IF(NOT choices.right AND players.user = choices.pickUser, 1, 0)) `wrongChoicesPicked` FROM players INNER JOIN questions ON (questions.team = players.team) INNER JOIN choices ON (choices.question = questions.id) GROUP BY players.user) B ON (B.user = players.user) WHERE players.team = :team');
        $stmt->execute([
            'team' => $team->id
        ]);
        return $stmt->fetchAll();
    }
}