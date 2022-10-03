<?php

namespace ARHunt\Hunt\Model;

use ARHunt\Team\Model\Team;

class HuntTable
{
    const ID_LENGTH = 16;

    protected $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getHuntById($id)
    {
        $stmt = $this->db->prepare('SELECT * FROM hunts WHERE id = :id');
        $stmt->execute([
            'id' => $id
        ]);
        if (!$stmt->rowCount()) {
            throw new \RuntimeException("Hunt with id `$id` doesn't exists.");
        }
        $huntArray = $stmt->fetch();
        return (new Hunt)->fromArray($huntArray);
    }

    public function getHuntsByAuthor($user, $statuses)
    {
        $statusesIn = implode(',', $statuses);
        $stmt = $this->db->prepare("SELECT * FROM hunts WHERE author = :author AND status IN ($statusesIn) ORDER BY callTime DESC");
        $stmt->execute([
            'author' => $user->nickname
        ]);
        $hunts = [];
        while ($huntArray = $stmt->fetch()) {
            $hunts[] = (new Hunt)->fromArray($huntArray);
        }
        return $hunts;
    }

    public function getHuntsByAnyoneExcept($user, $statuses)
    {
        $statusesIn = implode(',', $statuses);
        $stmt = $this->db->prepare("SELECT * FROM hunts WHERE author <> :author AND status IN ($statusesIn) ORDER BY callTime DESC");
        $stmt->execute([
            'author' => $user->nickname
        ]);
        $hunts = [];
        while ($huntArray = $stmt->fetch()) {
            $hunts[] = (new Hunt)->fromArray($huntArray);
        }
        return $hunts;
    }

    public function getHuntsByPlayer($user, $statuses)
    {
        $statusesIn = implode(',', $statuses);
        $stmt = $this->db->prepare("SELECT hunts.* FROM hunts INNER JOIN players ON (players.hunt = hunts.id) WHERE user = :user AND status IN ($statusesIn) ORDER BY callTime DESC");
        $stmt->execute([
            'user' => $user->nickname
        ]);
        $hunts = [];
        while ($huntArray = $stmt->fetch()) {
            $hunts[] = (new Hunt)->fromArray($huntArray);
        }
        return $hunts;
    }

    public function getHunts($statuses)
    {
        $statusesIn = implode(',', $statuses);
        $stmt = $this->db->prepare("SELECT * FROM hunts WHERE status IN ($statusesIn) ORDER BY callTime DESC");
        $stmt->execute();
        $hunts = [];
        while ($huntArray = $stmt->fetch()) {
            $hunts[] = (new Hunt)->fromArray($huntArray);
        }
        return $hunts;
    }

    public function saveHunt($hunt)
    {
        if ($hunt->id) {
            $stmt = $this->db->prepare('UPDATE hunts SET name = :name, description = :description, author = :author, callTime = :callTime, callLat = :callLat, callLng = :callLng, playTime = :playTime, status = :status WHERE id = :id');
        } else {
            $hunt->id = \Stdlib\UniqueId::generate(self::ID_LENGTH);
            $hunt->playTime = null;
            $hunt->status = 0;
            $stmt = $this->db->prepare('INSERT INTO hunts (id, name, description, author, callTime, callLat, callLng, playTime, status) VALUES (:id, :name, :description, :author, :callTime, :callLat, :callLng, :playTime, :status)');
        }
        $stmt->execute($hunt->toArray());
        return ($stmt->rowCount() === 1);
    }

    public function deleteHunt($hunt)
    {
        if (!$hunt->id) {
            return true;
        }
        $stmt = $this->db->prepare('DELETE FROM hunts WHERE id = :id');
        $stmt->execute([
            'id' => $hunt->id
        ]);
        return ($stmt->rowCount() === 1);
    }

    public function haveAllTeamsClosed($hunt)
    {
        $stmt = $this->db->prepare("SELECT NOT EXISTS(SELECT * FROM teams WHERE hunt = :hunt AND NOT closed) `allTeamsClosed`");
        $stmt->execute([
            'hunt' => $hunt->id
        ]);
        return ($stmt->fetch()['allTeamsClosed'] == 1);
    }

    public function isHuntReadyForCalling($hunt)
    {
        $stmt = $this->db->prepare('SELECT (A.atLeastTwoTeams AND B.atLeastOneStep AND C.atLeastOneRightAndOneWrongAnswer) `huntReadyForCalling` FROM (SELECT (COUNT(*) >= 2) `atLeastTwoTeams` FROM teams WHERE hunt = :hunt) A, (SELECT NOT EXISTS(SELECT * FROM teams LEFT JOIN steps ON (steps.team = teams.id) WHERE teams.hunt = :hunt GROUP BY teams.id HAVING COUNT(steps.team) < 1) `atLeastOneStep`) B, (SELECT NOT EXISTS(SELECT * FROM teams INNER JOIN questions ON (questions.team = teams.id) LEFT JOIN choices ON (choices.question = questions.id) WHERE teams.hunt = :hunt GROUP BY questions.id HAVING SUM(IF(choices.right, 1, 0)) < 1 OR SUM(IF(NOT choices.right, 1, 0)) < 1) `atLeastOneRightAndOneWrongAnswer`) C');
        $stmt->execute([
            'hunt' => $hunt->id
        ]);
        return ($stmt->fetch()['huntReadyForCalling'] == 1);
    }

    public function isHuntReadyForPreparing($hunt)
    {
        $stmt = $this->db->prepare('SELECT ((SELECT COUNT(*) FROM players WHERE players.hunt = :hunt) >= (SELECT COUNT(*) FROM teams WHERE teams.hunt = :hunt)) `huntReadyForPreparing`;');
        $stmt->execute([
            'hunt' => $hunt->id
        ]);
        return ($stmt->fetch()['huntReadyForPreparing'] == 1);
    }

    public function isHuntReadyForPlaying($hunt)
    {
        $stmt = $this->db->prepare('SELECT NOT EXISTS(SELECT * FROM teams LEFT JOIN players ON (players.team = teams.id) WHERE teams.hunt = :hunt GROUP BY teams.id HAVING COUNT(players.user) < 1) `huntReadyForPlaying`');
        $stmt->execute([
            'hunt' => $hunt->id
        ]);
        return ($stmt->fetch()['huntReadyForPlaying'] == 1);
    }

    public function getResultByHunt($hunt)
    {
        $stmt = $this->db->prepare('(SELECT * FROM teams WHERE hunt = :hunt AND closed ORDER BY closeTime) UNION (SELECT * FROM teams WHERE hunt = :hunt AND NOT closed)');
        $stmt->execute([
            'hunt' => $hunt->id
        ]);
        $teams = [];
        $ranking = 1;
        while ($teamArray = $stmt->fetch()) {
            $teams[$ranking++] = (new Team)->fromArray($teamArray);
        }
        return $teams;
    }
}