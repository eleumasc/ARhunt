<?php

namespace ARHunt\Step\Model;

class StepTable
{
    protected $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getStepByTeamAndSequence($team, $sequence)
    {
        $stmt = $this->db->prepare('SELECT * FROM steps WHERE team = :team AND sequence = :sequence');
        $stmt->execute([
            'team' => $team->id,
            'sequence' => $sequence
        ]);
        if (!$stmt->rowCount()) {
            throw new \RuntimeException("Step with team `{$team->id}` and sequence `$sequence` doesn't exists.");
        }
        $stepArray = $stmt->fetch();
        return (new Step)->fromArray($stepArray);
    }

    public function getStepByTeam($team)
    {
        $stmt = $this->db->prepare('SELECT steps.* FROM steps INNER JOIN teams ON (teams.id = steps.team AND teams.sequence = steps.sequence) WHERE teams.id = :team');
        $stmt->execute([
            'team' => $team->id
        ]);
        $stepArray = $stmt->fetch();
        return (new Step)->fromArray($stepArray);
    }

    public function getStepsByTeam($team)
    {
        $stmt = $this->db->prepare('SELECT * FROM steps WHERE team = :team ORDER BY sequence');
        $stmt->execute([
            'team' => $team->id
        ]);
        $steps = [];
        while ($stepArray = $stmt->fetch()) {
            $steps[] = (new Step)->fromArray($stepArray);
        }
        return $steps;
    }

    public function getSteps()
    {
        $stmt = $this->db->prepare('SELECT * FROM steps ORDER BY team, sequence');
        $stmt->execute();
        $steps = [];
        while ($stepArray = $stmt->fetch()) {
            $steps[] = (new Step)->fromArray($stepArray);
        }
        return $steps;
    }

    private function getLastSequence($teamId)
    {
        $stmt = $this->db->prepare('SELECT COALESCE(MAX(sequence), 0) `lastSequence` FROM steps WHERE team = :team');
        $stmt->execute([
            'team' => $teamId
        ]);
        return $stmt->fetch()['lastSequence'];
    }

    public function saveStep($step)
    {
        if ($step->team && $step->sequence) {
            $stmt = $this->db->prepare('UPDATE steps SET text = :text, media = :media, takeLat = :takeLat, takeLng = :takeLng, taken = :taken, takeTime = :takeTime, takeUser = :takeUser WHERE team = :team AND sequence = :sequence');
        } else {
            $lastSequence = $this->getLastSequence($step->team);
            $step->sequence = ($lastSequence ? $lastSequence + 1 : 1);
            $step->taken = 0;
            $step->takeTime = null;
            $step->takeUser = null;
            $stmt = $this->db->prepare('INSERT INTO steps (team, sequence, text, media, takeLat, takeLng, taken, takeTime, takeUser) VALUES (:team, :sequence, :text, :media, :takeLat, :takeLng, :taken, :takeTime, :takeUser)');
        }
        $stmt->execute($step->toArray());
        return ($stmt->rowCount() === 1);
    }

    private function move($step, $up)
    {
        $oldSequence = $step->sequence;
        $newSequence = $up ? $oldSequence - 1 : $oldSequence + 1;
        if (($up && $oldSequence == 1) || (!$up && $oldSequence == $this->getLastSequence($step->team))) {
            return true;
        }
        $this->db->beginTransaction();
        $stmt = $this->db->prepare('UPDATE steps SET sequence = 0 WHERE team = :team AND sequence = :newSequence');
        $stmt->execute([
            'team' => $step->team,
            'newSequence' => $newSequence
        ]);
        if (!$stmt->rowCount()) {
            $this->db->rollBack();
            return false;
        }
        $stmt = $this->db->prepare('UPDATE steps SET sequence = :newSequence WHERE team = :team AND sequence = :oldSequence');
        $stmt->execute([
            'team' => $step->team,
            'oldSequence' => $oldSequence,
            'newSequence' => $newSequence
        ]);
        if (!$stmt->rowCount()) {
            $this->db->rollBack();
            return false;
        }
        $stmt = $this->db->prepare('UPDATE steps SET sequence = :oldSequence WHERE team = :team AND sequence = 0');
        $stmt->execute([
            'team' => $step->team,
            'oldSequence' => $oldSequence
        ]);
        if (!$stmt->rowCount()) {
            $this->db->rollBack();
            return false;
        }
        $this->db->commit();
        $step->sequence = $newSequence;
        return true;
    }

    public function moveUp($step)
    {
        return $this->move($step, true);
    }

    public function moveDown($step)
    {
        return $this->move($step, false);
    }

    public function deleteStep($step)
    {
        if (!$step->team || !$step->sequence) {
            return true;
        }
        $this->db->beginTransaction();
        $stmt = $this->db->prepare('DELETE FROM steps WHERE team = :team AND sequence = :sequence');
        $stmt->execute([
            'team' => $step->team,
            'sequence' => $step->sequence
        ]);
        if (!$stmt->rowCount()) {
            $this->db->rollBack();
            return false;
        }
        $stmt = $this->db->prepare('UPDATE steps SET sequence = sequence - 1 WHERE team = :team AND sequence > :to ORDER BY sequence');
        $stmt->execute([
            'team' => $step->team,
            'to' => $step->sequence
        ]);
        $this->db->commit();
        return true;
    }
}