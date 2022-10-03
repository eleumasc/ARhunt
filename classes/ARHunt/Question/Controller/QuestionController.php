<?php

namespace ARHunt\Question\Controller;

use ARHunt\Hunt\Model\HuntTable;
use ARHunt\Hunt\Model\Hunt;
use ARHunt\Team\Model\TeamTable;
use ARHunt\Step\Model\StepTable;
use ARHunt\Question\Model\QuestionTable;
use ARHunt\Question\Model\Question;
use ARHunt\Question\Form\QuestionForm;
use ARHunt\Choice\Model\ChoiceTable;

class QuestionController
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function ajaxAdd($request, $response, $args)
    {
        $data = $request->getAttribute('__data');
        $form = new QuestionForm;
        $form->setEdit(false);
        if (!$form->setData($data)) {
            return $response->withStatus(400)
                            ->withJson([ 'status' => $form->getError() ]);
        }
        $teamTable = new TeamTable($this->container->get('db'));
        try {
            $team = $teamTable->getTeamById($form->getTeam());
        } catch (\RuntimeException $ex) {
            return $response->withStatus(404)
                            ->withJson([ 'status' => 'not-found' ]);
        }
        $stepTable = new StepTable($this->container->get('db'));
        try {
            $step = $stepTable->getStepByTeamAndSequence($team, $form->getSequence());
        } catch (\RuntimeException $ex) {
            return $response->withStatus(404);
        }
        $huntTable = new HuntTable($this->container->get('db'));
        $hunt = $huntTable->getHuntById($team->hunt);
        if ($hunt->author !== $_SESSION['user']->nickname) {
            return $response->withStatus(403)
                            ->withJson([ 'status' => 'you-shall-not-pass' ]);
        }
        if ($hunt->status != Hunt::EDITING && $hunt->status != Hunt::PUBLISHED) {
            return $response->withStatus(403)
                            ->withJson([ 'status' => 'you-shall-not-pass' ]);
        }
        $question = $form->toQuestion(new Question);
        $question->team = $step->team;
        $question->sequence = $step->sequence;
        $questionTable = new QuestionTable($this->container->get('db'));
        if ($questionTable->saveQuestion($question)) {
            return $response->withJson([ 'status' => 'ok' ]);
        } else {
            return $response->withStatus(500)
                            ->withJson([ 'status' => 'not-created' ]);
        }
    }

    public function ajaxEdit($request, $response, $args)
    {
        $data = $request->getAttribute('__data');
        $form = new QuestionForm;
        $form->setEdit(true);
        if (!$form->setData($data)) {
            return $response->withStatus(400)
                            ->withJson([ 'status' => $form->getError() ]);
        }
        $questionTable = new QuestionTable($this->container->get('db'));
        try {
            $question = $questionTable->getQuestionById($form->getId());
        } catch (\RuntimeException $ex) {
            return $response->withStatus(404)
                            ->withJson([ 'status' => 'not-found' ]);
        }
        $teamTable = new TeamTable($this->container->get('db'));
        $team = $teamTable->getTeamById($question->team);
        $huntTable = new HuntTable($this->container->get('db'));
        $hunt = $huntTable->getHuntById($team->hunt);
        if ($hunt->author !== $_SESSION['user']->nickname) {
            return $response->withStatus(403)
                            ->withJson([ 'status' => 'you-shall-not-pass' ]);
        }
        if ($hunt->status != Hunt::EDITING && $hunt->status != Hunt::PUBLISHED) {
            return $response->withStatus(403)
                            ->withJson([ 'status' => 'you-shall-not-pass' ]);
        }
        $question = $form->toQuestion($question);
        if ($questionTable->saveQuestion($question)) {
            return $response->withJson([ 'status' => 'ok' ]);
        } else {
            return $response->withJson([ 'status' => 'not-modified' ]);
        }
    }

    public function ajaxDelete($request, $response, $args)
    {
        $data = $request->getAttribute('__data');
        if (!isset($data['id'])) {
            return $response->withStatus(400)
                            ->withJson([ 'status' => 'missing-id' ]);
        }
        $questionTable = new QuestionTable($this->container->get('db'));
        try {
            $question = $questionTable->getQuestionById($data['id']);
        } catch (\RuntimeException $ex) {
            return $response->withStatus(404)
                            ->withJson([ 'status' => 'not-found' ]);
        }
        $teamTable = new TeamTable($this->container->get('db'));
        $team = $teamTable->getTeamById($question->team);
        $huntTable = new HuntTable($this->container->get('db'));
        $hunt = $huntTable->getHuntById($team->hunt);
        if ($hunt->author !== $_SESSION['user']->nickname) {
            return $response->withStatus(403)
                            ->withJson([ 'status' => 'you-shall-not-pass' ]);
        }
        if ($hunt->status != Hunt::EDITING && $hunt->status != Hunt::PUBLISHED) {
            return $response->withStatus(403)
                            ->withJson([ 'status' => 'you-shall-not-pass' ]);
        }
        if ($questionTable->deleteQuestion($question)) {
            return $response->withJson([ 'status' => 'ok' ]);
        } else {
            return $response->withStatus(500)
                            ->withJson([ 'status' => 'not-modified' ]);
        }
    }

    public function listQuestions($request, $response, $args)
    {
        $teamTable = new TeamTable($this->container->get('db'));
        try {
            $team = $teamTable->getTeamById($args['team']);
        } catch (\RuntimeException $ex) {
            return $response->withStatus(404)
                            ->withJson([ 'status' => 'not-found' ]);
        }
        $stepTable = new StepTable($this->container->get('db'));
        try {
            $step = $stepTable->getStepByTeamAndSequence($team, $args['sequence']);
        } catch (\RuntimeException $ex) {
            return $response->withStatus(404);
        }
        $huntTable = new HuntTable($this->container->get('db'));
        $hunt = $huntTable->getHuntById($team->hunt);
        if ($hunt->author !== $_SESSION['user']->nickname) {
            return $response->withStatus(403);
        }
        $questionTable = new QuestionTable($this->container->get('db'));
        $questions = $questionTable->getQuestionsByStep($step);
        $choiceTable = new ChoiceTable($this->container->get('db'));
        foreach ($questions as $question) {
            $question->choices = $choiceTable->getChoicesByQuestion($question);
        }
        return $this->container->get('view')->render($response, 'questions-list.html.twig', [
            'hunt' => $hunt,
            'team' => $team,
            'step' => $step,
            'questions' => $questions
        ]);
    }
}