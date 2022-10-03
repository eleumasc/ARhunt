<?php

namespace ARHunt\Hunt\Model;

use ARHunt\User\Model\User;
use ARHunt\Team\Model\Team;

class PlayerTable
{
    protected $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getPlayersByHunt($hunt)
    {
        $stmt = $this->db->prepare('SELECT users.* FROM users INNER JOIN players ON (players.user = users.nickname) WHERE players.hunt = :hunt ORDER BY lastModified');
        $stmt->execute([
            'hunt' => $hunt->id
        ]);
        $players = [];
        while ($playerArray = $stmt->fetch()) {
            $players[] = (new User)->fromArray($playerArray);
        }
        return $players;
    }

    public function getPlayersByTeam($team)
    {
        $stmt = $this->db->prepare('SELECT users.* FROM users INNER JOIN players ON (players.user = users.nickname) WHERE players.team = :team ORDER BY lastModified');
        $stmt->execute([
            'team' => $team->id
        ]);
        $players = [];
        while ($playerArray = $stmt->fetch()) {
            $players[] = (new User)->fromArray($playerArray);
        }
        return $players;
    }

    public function playerExists($hunt, $user)
    {
        $stmt = $this->db->prepare('SELECT EXISTS(SELECT * FROM players WHERE hunt = :hunt AND user = :user) `playerExists`');
        $stmt->execute([
            'hunt' => $hunt->id,
            'user' => $user->nickname
        ]);
        return ($stmt->fetch()['playerExists'] == 1);
    }

    public function join($hunt, $user)
    {
        $stmt = $this->db->prepare('INSERT INTO players (hunt, user) SELECT * FROM (SELECT :hunt, :user) A WHERE NOT EXISTS(SELECT hunt, user FROM players WHERE hunt = :hunt AND user = :user)');
        $stmt->execute([
            'hunt' => $hunt->id,
            'user' => $user->nickname
        ]);
        return ($stmt->rowCount() === 1);
    }

    public function leave($hunt, $user)
    {
        $stmt = $this->db->prepare('DELETE FROM players WHERE hunt = :hunt AND user = :user');
        $stmt->execute([
            'hunt' => $hunt->id,
            'user' => $user->nickname
        ]);
        return ($stmt->rowCount() === 1);
    }

    public function isTeamAvailable($team)
    {
        $stmt = $this->db->prepare('SELECT EXISTS(SELECT t1.* FROM teams t1 WHERE t1.id = :team AND (SELECT COUNT(p1.team) FROM players p1 WHERE p1.team = t1.id) < CEIL((SELECT COUNT(*) FROM players p2 WHERE p2.hunt = t1.hunt) / (SELECT COUNT(*) FROM teams t2 WHERE t2.hunt = t1.hunt))) `teamAvailable`');
        $stmt->execute([
            'team' => $team->id
        ]);
        return ($stmt->fetch()['teamAvailable'] == 1);
    }

    public function chooseTeam($hunt, $user, $team)
    {
        if ($team !== null) {
            $stmt = $this->db->prepare('UPDATE players p, (SELECT hunt FROM teams WHERE id = :team) A, (SELECT B.teamPlayersCount < CEIL(C.huntPlayersCount / D.huntTeamsCount) `teamAvailable` FROM (SELECT COUNT(team) `teamPlayersCount` FROM players WHERE team = :team) B, (SELECT COUNT(*) `huntPlayersCount` FROM players WHERE hunt = :hunt) C, (SELECT COUNT(*) `huntTeamsCount` FROM teams WHERE hunt = :hunt) D) E SET p.team = :team WHERE p.hunt = :hunt AND p.user = :user AND p.hunt = A.hunt AND E.teamAvailable');
            $stmt->execute([
                'hunt' => $hunt->id,
                'user' => $user->nickname,
                'team' => $team->id
            ]);
        } else {
            $stmt = $this->db->prepare('UPDATE players SET team = NULL WHERE hunt = :hunt AND user = :user');
            $stmt->execute([
                'hunt' => $hunt->id,
                'user' => $user->nickname
            ]);
        }
        return ($stmt->rowCount() === 1);
    }

    public function resetTeams($hunt)
    {
        $stmt = $this->db->prepare('UPDATE players SET team = NULL WHERE hunt = :hunt');
        $stmt->execute([
            'hunt' => $hunt->id
        ]);
        return ($stmt->rowCount() > 0);
    }

    public function getPlayersWithoutTeam($hunt)
    {
        $stmt = $this->db->prepare('SELECT users.* FROM users INNER JOIN players ON (players.user = users.nickname) WHERE players.hunt = :hunt AND players.team IS NULL ORDER BY lastModified');
        $stmt->execute([
            'hunt' => $hunt->id
        ]);
        $players = [];
        while ($playerArray = $stmt->fetch()) {
            $players[] = (new User)->fromArray($playerArray);
        }
        return $players;
    }

    public function deletePlayersWithoutTeam($hunt)
    {
        $stmt = $this->db->prepare('DELETE FROM players WHERE hunt = :hunt AND team IS NULL');
        $stmt->execute([
            'hunt' => $hunt->id
        ]);
        return ($stmt->rowCount() === 1);
    }

    public function getLastModifiedPlayerInfo($hunt)
    {
        $stmt = $this->db->prepare('SELECT * FROM players p1 WHERE p1.hunt = :hunt AND p1.lastModified = (SELECT MAX(p2.lastModified) FROM players p2 WHERE p2.hunt = p1.hunt)');
        $stmt->execute([
            'hunt' => $hunt->id
        ]);
        if ($stmt->rowCount()) {
            return $stmt->fetch();
        } else {
            return false;
        }
    }

    public function updateLastModifiedPlayerInfo($hunt)
    {
        $stmt = $this->db->prepare('UPDATE players p, (SELECT MAX(lastModified) `maxLastModified` FROM players WHERE hunt = :hunt) A SET p.lastModified = NULL WHERE p.hunt = :hunt AND p.lastModified = A.maxLastModified');
        $stmt->execute([
            'hunt' => $hunt->id
        ]);
        return ($stmt->rowCount() === 1);
    }
}