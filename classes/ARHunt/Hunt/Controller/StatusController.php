<?php

namespace ARHunt\Hunt\Controller;

use \Psr\Http\Message\ResponseInterface;
use ARHunt\User\Model\User;
use ARHunt\Hunt\Model\HuntTable;
use ARHunt\Hunt\Model\Hunt;
use ARHunt\Hunt\Model\PlayerTable;
use ARHunt\Team\Model\TeamTable;
use ARHunt\Team\Model\Team;
use ARHunt\Step\Model\StepTable;
use ARHunt\Media\Model\MediaTable;
use ARHunt\Question\Model\QuestionTable;
use ARHunt\Question\Model\Question;
use ARHunt\Choice\Model\ChoiceTable;
use ARHunt\Choice\Model\Choice;

class StatusController
{
    const TAG_HASH_ALGO = 'sha256';

    const MAX_DISTANCE = 50;

    protected $container;

    protected $huntTable;

    protected $playerTable;

    protected $teamTable;

    protected $stepTable;

    protected $mediaTable;

    protected $questionTable;

    protected $choiceTable;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function getHuntTable()
    {
        if (!$this->huntTable) {
            $this->huntTable = new HuntTable($this->container->get('db'));
        }
        return $this->huntTable;
    }

    public function getPlayerTable()
    {
        if (!$this->playerTable) {
            $this->playerTable = new PlayerTable($this->container->get('db'));
        }
        return $this->playerTable;
    }

    public function getTeamTable()
    {
        if (!$this->teamTable) {
            $this->teamTable = new TeamTable($this->container->get('db'));
        }
        return $this->teamTable;
    }

    public function getStepTable()
    {
        if (!$this->stepTable) {
            $this->stepTable = new StepTable($this->container->get('db'));
        }
        return $this->stepTable;
    }

    public function getMediaTable()
    {
        if (!$this->mediaTable) {
            $this->mediaTable = new MediaTable($this->container->get('db'));
        }
        return $this->mediaTable;
    }

    public function getQuestionTable()
    {
        if (!$this->questionTable) {
            $this->questionTable = new QuestionTable($this->container->get('db'));
        }
        return $this->questionTable;
    }

    public function getChoiceTable()
    {
        if (!$this->choiceTable) {
            $this->choiceTable = new ChoiceTable($this->container->get('db'));
        }
        return $this->choiceTable;
    }

    public function actionChangeStatus($data, $hunt)
    {
        if (!isset($data['status'])) {
            return false;
        }
        $newStatus = $data['status'];
        $changeStatus = false;
        $huntTable = $this->getHuntTable();
        if ($hunt->status == Hunt::EDITING) {
            if ($newStatus == Hunt::PUBLISHED) {
                $changeStatus = true;
            }
        } else
        if ($hunt->status == Hunt::PUBLISHED) {
            if ($newStatus == Hunt::EDITING) {
                $changeStatus = true;
            } else
            if ($newStatus == Hunt::CALLING) {
                $changeStatus = $huntTable->isHuntReadyForCalling($hunt);
            }
        } else
        if ($hunt->status == Hunt::CALLING) {
            if ($newStatus == Hunt::PREPARING) {
                $changeStatus = $huntTable->isHuntReadyForPreparing($hunt);
            } else
            if ($newStatus == Hunt::CANCELLED) {
                $changeStatus = true;
            }
        } else
        if ($hunt->status == Hunt::PREPARING) {
            if ($newStatus == Hunt::WAITING) {
                if ($changeStatus = $huntTable->isHuntReadyForPlaying($hunt)) {
                    $playerTable = $this->getPlayerTable();
                    $playerTable->deletePlayersWithoutTeam($hunt);
                }
            } else
            if ($newStatus == Hunt::CALLING) {
                $changeStatus = true;
                $playerTable = $this->getPlayerTable();
                $playerTable->resetTeams($hunt);
            } else
            if ($newStatus == Hunt::CANCELLED) {
                $changeStatus = true;
            }
        } else
        if ($hunt->status == Hunt::WAITING) {
            if ($newStatus == Hunt::PLAYING) {
                $hunt->playTime = (new \DateTime)->format('Y-m-d H:i:s');
                $changeStatus = true;
            } else
            if ($newStatus == Hunt::PREPARING) {
                $changeStatus = true;
            } else
            if ($newStatus == Hunt::CANCELLED) {
                $changeStatus = true;
            }
        } else
        if ($hunt->status == Hunt::PLAYING) {
            if ($newStatus == Hunt::CANCELLED) {
                $changeStatus = true;
            }
        }
        if ($changeStatus) {
            $hunt->status = $newStatus;
            $huntTable->saveHunt($hunt);
            return true;
        } else {
            return false;
        }
    }

    public function actionJoin($data, $hunt)
    {
        if (!isset($data['lat']) || !isset($data['lng']) || !isset($data['acc'])) {
            return false;
        }
        if (!is_numeric($data['lat']) || !is_numeric($data['lng']) || !is_numeric($data['acc'])) {
            return false;
        }
        $lat = $data['lat'];
        $lng = $data['lng'];
        $acc = $data['acc'];
        if (\Stdlib\GeoCoordinate::haversineGreatCircleDistance($lat, $lng, $hunt->callLat, $hunt->callLng) <= self::MAX_DISTANCE) {
            $playerTable = $this->getPlayerTable();
            $playerTable->join($hunt, $_SESSION['user']);
            return true;
        } else {
            return false;
        }
    }

    public function actionLeave($data, $hunt)
    {
        $playerTable = $this->getPlayerTable();
        $playerTable->leave($hunt, $_SESSION['user']);
        $playerTable->updateLastModifiedPlayerInfo($hunt);
    }

    public function actionChooseTeam($data, $hunt)
    {
        if (isset($data['team'])) {
            $teamTable = $this->getTeamTable();
            try {
                $team = $teamTable->getTeamById($data['team']);
            } catch (\RuntimeException $ex) {
                return false;
            }
        } else {
            $team = null;
        }
        $playerTable = $this->getPlayerTable();
        return $playerTable->chooseTeam($hunt, $_SESSION['user'], $team);
    }

    public function goToNextStep($hunt, $team, $step)
    {
        $questionTable = $this->getQuestionTable();
        if ($questionTable->areAllQuestionsAnswered($step)) {
            $teamTable = $this->getTeamTable();
            $nextSequence = $teamTable->getNextSequence($team);
            if ($nextSequence) {
                $team->sequence = $nextSequence;
            } else {
                $team->closed = 1;
                $team->closeTime = (new \DateTime)->format('Y-m-d H:i:s');
            }
            $teamTable->saveTeam($team);
            $huntTable = $this->getHuntTable();
            if ($huntTable->haveAllTeamsClosed($hunt)) {
                $hunt->status = Hunt::CLOSED;
                $huntTable->saveHunt($hunt);
            }
            return true;
        } else {
            return false;
        }
    }

    public function actionTakeStep($data, $hunt, $team, $step)
    {
        if (!isset($data['lat']) || !isset($data['lng']) || !isset($data['acc'])) {
            return false;
        }
        if (!is_numeric($data['lat']) || !is_numeric($data['lng']) || !is_numeric($data['acc'])) {
            return false;
        }
        $lat = $data['lat'];
        $lng = $data['lng'];
        $acc = $data['acc'];
        if (\Stdlib\GeoCoordinate::haversineGreatCircleDistance($lat, $lng, $step->takeLat, $step->takeLng) <= self::MAX_DISTANCE) {
            $stepTable = $this->getStepTable();
            $step->taken = 1;
            $step->takeTime = (new \DateTime)->format('Y-m-d H:i:s');
            $step->takeUser = $_SESSION['user']->nickname;
            $stepTable->saveStep($step);
            return $this->goToNextStep($hunt, $team, $step);
        } else {
            return false;
        }
    }

    public function actionPickChoice($data, $hunt, $team, $step)
    {
        if (!isset($data['choice'])) {
            return false;
        }
        $choiceTable = $this->getChoiceTable();
        try {
            $choice = $choiceTable->getChoiceById($data['choice']);
        } catch (\RuntimeException $ex) {
            return false;
        }
        $questionTable = $this->getQuestionTable();
        try {
            $question = $questionTable->getQuestionByChoice($choice);
        } catch (\RuntimeException $ex) {
            return false;
        }
        if ($questionTable->isQuestionAnswered($question)) {
            return false;
        }
        if ($step->team == $question->team && $step->sequence == $question->sequence && !$choice->picked) {
            $choice->picked = 1;
            $choice->pickTime = (new \DateTime)->format('Y-m-d H:i:s');
            $choice->pickUser = $_SESSION['user']->nickname;
            $choiceTable->saveChoice($choice);
            return $this->goToNextStep($hunt, $team, $step);
        } else {
            return false;
        }
    }

    public function getStatus($request, $response, $args, $data, $action)
    {
        $huntTable = $this->getHuntTable();
        try {
            $hunt = $huntTable->getHuntById($data['id']);
        } catch (\RuntimeException $ex) {
            return $response->withStatus(404)
                            ->withJson([ 'status' => 'not-found' ]);
        }
        if ($hunt->status != Hunt::CLOSED && $hunt->status != Hunt::CANCELLED) {
            if ($action === 'change-status' && $hunt->author === $_SESSION['user']->nickname) {
                return $this->actionChangeStatus($data['action'], $hunt);
            }
        }
        if ($hunt->status == Hunt::EDITING || $hunt->status == Hunt::PUBLISHED) {
            if ($hunt->author === $_SESSION['user']->nickname) {
                $tag = hash(self::TAG_HASH_ALGO, "{$hunt->status}");
                if (isset($data['tag'])) {
                    if ($data['tag'] === $tag) {
                        return $response->withJson([ 'status' => 'updated' ]);
                    }
                }
                return $response->withJson([
                                    'status' => 'ok',
                                    'tag' => $tag,
                                    'huntStatus' => $hunt->status,
                                    'huntStatusName' => $hunt->getStatusName()
                                ]);
            } else {
                return $response->withStatus(403)
                                ->withJson([ 'status' => 'you-shall-not-pass' ]);
            }
        } else
        if ($hunt->status == Hunt::CALLING) {
            if (($action === 'join' || $action === 'leave') && $hunt->author !== $_SESSION['user']->nickname) {
                if ($action === 'join') {
                    return $this->actionJoin($data['action'], $hunt);
                } else {
                    return $this->actionLeave($data['action'], $hunt);
                }
            }
            $playerTable = $this->getPlayerTable();
            if ($lmPlayerInfo = $playerTable->getLastModifiedPlayerInfo($hunt)) {
                $tag = hash(self::TAG_HASH_ALGO, "{$hunt->status};{$lmPlayerInfo['user']};{$lmPlayerInfo['team']};{$lmPlayerInfo['lastModified']}");
            } else {
                $tag = hash(self::TAG_HASH_ALGO, "{$hunt->status}");
            }
            if (isset($data['tag'])) {
                if ($data['tag'] === $tag) {
                    return $response->withJson([ 'status' => 'updated' ]);
                }
            }
            if ($hunt->author === $_SESSION['user']->nickname) {
                return $response->withJson([
                    'status' => 'ok',
                    'tag' => $tag,
                    'huntStatus' => $hunt->status,
                    'huntStatusName' => $hunt->getStatusName(),
                    'players' => User::toMinifiedObjects($playerTable->getPlayersByHunt($hunt))
                ]);
            } else {
                return $response->withJson([
                    'status' => 'ok',
                    'tag' => $tag,
                    'huntStatus' => $hunt->status,
                    'playerExists' => $playerTable->playerExists($hunt, $_SESSION['user']),
                    'players' => User::toMinifiedObjects($playerTable->getPlayersByHunt($hunt))
                ]);
            }
        } else
        if ($hunt->status == Hunt::PREPARING || $hunt->status == Hunt::WAITING) {
            $teamTable = $this->getTeamTable();
            $playerTable = $this->getPlayerTable();
            if ($hunt->author !== $_SESSION['user']->nickname && !$playerTable->playerExists($hunt, $_SESSION['user'])) {
                return $response->withStatus(403)
                                ->withJson([
                                    'status' => 'redirect',
                                    'location' => $this->container->get('router')->pathFor('hunts-view', [
                                        'id' => $hunt->id
                                    ])
                                ]);
            }
            if ($hunt->status == Hunt::PREPARING) {
                if ($action === 'choose-team' && $hunt->author !== $_SESSION['user']->nickname) {
                    return $this->actionChooseTeam($data['action'], $hunt);
                }
            }
            if ($lmPlayerInfo = $playerTable->getLastModifiedPlayerInfo($hunt)) {
                $tag = hash(self::TAG_HASH_ALGO, "{$hunt->status};{$lmPlayerInfo['user']};{$lmPlayerInfo['team']};{$lmPlayerInfo['lastModified']}");
            } else {
                $tag = hash(self::TAG_HASH_ALGO, "{$hunt->status}");
            }
            if (isset($data['tag'])) {
                if ($data['tag'] === $tag) {
                    return $response->withJson([ 'status' => 'updated' ]);
                }
            }
            $teams = $teamTable->getTeamsByHunt($hunt);
            $minTeams = Team::toMinifiedObjects($teams);
            foreach ($minTeams as $mt) {
                $mt->players = User::toMinifiedObjects($playerTable->getPlayersByTeam($mt));
                $mt->available = $playerTable->isTeamAvailable($mt);
            }
            $remainingPlayers = $playerTable->getPlayersWithoutTeam($hunt);
            $minRemainingPlayers = User::toMinifiedObjects($remainingPlayers);
            if ($hunt->author === $_SESSION['user']->nickname) {
                return $response->withJson([
                    'status' => 'ok',
                    'tag' => $tag,
                    'huntStatus' => $hunt->status,
                    'huntStatusName' => $hunt->getStatusName(),
                    'teams' => $minTeams,
                    'remainingPlayers' => $minRemainingPlayers
                ]);
            } else {
                try {
                    $team = $teamTable->getTeamByPlayer($hunt, $_SESSION['user']);
                    $minTeam = $team->toMinifiedObject();
                } catch (\RuntimeException $ex) {
                    $minTeam = null;
                }
                return $response->withJson([
                    'status' => 'ok',
                    'tag' => $tag,
                    'huntStatus' => $hunt->status,
                    'playerTeam' => $minTeam,
                    'teams' => $minTeams,
                    'remainingPlayers' => $minRemainingPlayers
                ]);
            }
        } else
        if ($hunt->status == Hunt::PLAYING) {
            if ($hunt->author === $_SESSION['user']->nickname) {
                $tag = hash(self::TAG_HASH_ALGO, "{$hunt->status}");
                if (isset($data['tag'])) {
                    if ($data['tag'] === $tag) {
                        return $response->withJson([ 'status' => 'updated' ]);
                    }
                }
                return $response->withJson([
                    'status' => 'ok',
                    'tag' => $tag,
                    'huntStatus' => $hunt->status,
                    'huntStatusName' => $hunt->getStatusName()
                ]);
            } else {
                $teamTable = $this->getTeamTable();
                $stepTable = $this->getStepTable();
                try {
                    $team = $teamTable->getTeamByPlayer($hunt, $_SESSION['user']);
                    $minTeam = $team->toMinifiedObject();
                } catch (\RuntimeException $ex) {
                    return $response->withStatus(403)
                                    ->withJson([
                                        'status' => 'redirect',
                                        'location' => $this->container->get('router')->pathFor('hunts-view', [
                                            'id' => $hunt->id
                                        ])
                                    ]);
                }
                if ($team->closed) {
                    $tag = hash(self::TAG_HASH_ALGO, "{$hunt->status}");
                    if (isset($data['tag'])) {
                        if ($data['tag'] === $tag) {
                            return $response->withJson([ 'status' => 'updated' ]);
                        }
                    }
                    return $response->withJson([
                        'status' => 'ok',
                        'tag' => $tag,
                        'huntStatus' => $hunt->status,
                        'huntPlayStatus' => 'closed',
                        'playerTeam' => $minTeam
                    ]);
                }
                $step = $stepTable->getStepByTeam($team);
                $minStep = $step->toMinifiedObject();
                if (!$step->taken) {
                    if ($action === 'take-step') {
                        return $this->actionTakeStep($data['action'], $hunt, $team, $step);
                    }
                    $mediaTable = $this->getMediaTable();
                    $tag = hash(self::TAG_HASH_ALGO, "{$hunt->status};{$step->sequence};0");
                    if (isset($data['tag'])) {
                        if ($data['tag'] === $tag) {
                            return $response->withJson([ 'status' => 'updated' ]);
                        }
                    }
                    if ($step->media) {
                        $media = $mediaTable->getMediaById($step->media);
                        $minMedia = $media->toMinifiedObject();
                        $minMedia->path = $this->container->get('router')->pathFor('media-download', [
                            'id' => $media->id
                        ]);
                    } else {
                        $minMedia = null;
                    }
                    return $response->withJson([
                        'status' => 'ok',
                        'tag' => $tag,
                        'huntStatus' => $hunt->status,
                        'huntPlayStatus' => 'step',
                        'playerTeam' => $minTeam,
                        'step' => $minStep,
                        'media' => $minMedia
                    ]);
                } else {
                    $questionTable = $this->getQuestionTable();
                    $timeout = $questionTable->getPenaltyTimeout($step);
                    if (!$timeout) {
                        if ($action === 'pick-choice') {
                            return $this->actionPickChoice($data['action'], $hunt, $team, $step);
                        }
                        $choiceTable = $this->getChoiceTable();
                        $lpChoice = $choiceTable->getLastPickedChoice($step);
                        if ($lpChoice) {
                            $tag = hash(self::TAG_HASH_ALGO, "{$hunt->status};{$step->sequence};1;{$lpChoice->id};{$lpChoice->pickTime}");
                        } else {
                            $tag = hash(self::TAG_HASH_ALGO, "{$hunt->status};{$step->sequence};1");
                        }
                        if (isset($data['tag'])) {
                            if ($data['tag'] === $tag) {
                                return $response->withJson([ 'status' => 'updated' ]);
                            }
                        }
                        $minQuestions = Question::toMinifiedObjects($questionTable->getQuestionsByStep($step));
                        foreach ($minQuestions as $mq) {
                            $mq->choices = Choice::toMinifiedObjects($choiceTable->getChoicesByQuestion($mq));
                            $mq->answered = $questionTable->isQuestionAnswered($mq);
                        }
                        return $response->withJson([
                            'status' => 'ok',
                            'tag' => $tag,
                            'huntStatus' => $hunt->status,
                            'huntPlayStatus' => 'questions',
                            'playerTeam' => $minTeam,
                            'questions' => $minQuestions
                        ]);
                    } else {
                        $tag = hash(self::TAG_HASH_ALGO, "{$hunt->status};{$step->sequence};2");
                        return $response->withJson([
                            'status' => 'ok',
                            'tag' => $tag,
                            'huntStatus' => $hunt->status,
                            'huntPlayStatus' => 'timeout',
                            'playerTeam' => $minTeam,
                            'timeout' => $timeout
                        ]);
                    }
                }
            }
        } else
        if ($hunt->status == Hunt::CLOSED || $hunt->status == Hunt::CANCELLED) {
            return $response->withStatus(403)
                            ->withJson([
                                'status' => 'redirect',
                                'location' => $this->container->get('router')->pathFor('hunts-result', [
                                    'id' => $hunt->id
                                ])
                            ]);
        }
    }

    public function ajaxVerify($request, $response, $args)
    {
        $data = $request->getAttribute('__data');
        if (!isset($data['id'])) {
            return $response->withStatus(400)
                            ->withJson([ 'status' => 'missing-id' ]);
        }
        $huntTable = $this->getHuntTable();
        try {
            $hunt = $huntTable->getHuntById($data['id']);
        } catch (\RuntimeException $ex) {
            return $response->withStatus(404)
                            ->withJson([ 'status' => 'not-found' ]);
        }
        if ($hunt->author !== $_SESSION['user']->nickname) {
            return $response->withStatus(403)
                            ->withJson([ 'status' => 'you-shall-not-pass' ]);
        }
        if ($hunt->status != Hunt::EDITING && $hunt->status != Hunt::PUBLISHED) {
            return $response->withStatus(403)
                            ->withJson([ 'status' => 'you-shall-not-pass' ]);
        }
        if ($huntTable->isHuntReadyForCalling($hunt)) {
            return $response->withJson([ 'status' => 'ok' ]);
        } else {
            return $response->withJson([ 'status' => 'not-ready' ]);
        }
    }

    public function ajaxStatus($request, $response, $args)
    {
        $data = $request->getAttribute('__data');
        if (!isset($data['id'])) {
            return $response->withStatus(400)
                            ->withJson([ 'status' => 'missing-id' ]);
        }
        if (isset($data['action']) && isset($data['action']['type'])) {
            $result = $this->getStatus($request, $response, $args, $data, $data['action']['type']);
            if ($result instanceof ResponseInterface) {
                return $result;
            }
        }
        return $this->getStatus($request, $response, $args, $data, false);
    }
}