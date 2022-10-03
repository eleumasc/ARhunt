<?php

namespace ARHunt\Choice\Controller;

use ARHunt\Hunt\Model\HuntTable;
use ARHunt\Hunt\Model\Hunt;
use ARHunt\Team\Model\TeamTable;
use ARHunt\Question\Model\QuestionTable;
use ARHunt\Choice\Model\ChoiceTable;
use ARHunt\Choice\Model\Choice;
use ARHunt\Choice\Form\ChoiceForm;

class ChoiceController
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function ajaxAdd($request, $response, $args)
    {
        $data = $request->getAttribute('__data');
        $form = new ChoiceForm;
        $form->setEdit(false);
        if (!$form->setData($data)) {
            return $response->withStatus(400)
                            ->withJson([ 'status' => $form->getError() ]);
        }
        $questionTable = new QuestionTable($this->container->get('db'));
        try {
            $question = $questionTable->getQuestionById($form->getQuestion());
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
        $choice = $form->toChoice(new Choice);
        $choice->question = $question->id;
        $choice->right = 0;
        $choiceTable = new ChoiceTable($this->container->get('db'));
        if ($choiceTable->saveChoice($choice)) {
            return $response->withJson([ 'status' => 'ok' ]);
        } else {
            return $response->withStatus(500)
                            ->withJson([ 'status' => 'not-created' ]);
        }
    }

    public function ajaxEdit($request, $response, $args)
    {
        $data = $request->getAttribute('__data');
        $form = new ChoiceForm;
        $form->setEdit(true);
        if (!$form->setData($data)) {
            return $response->withStatus(400)
                            ->withJson([ 'status' => $form->getError() ]);
        }
        $choiceTable = new ChoiceTable($this->container->get('db'));
        try {
            $choice = $choiceTable->getChoiceById($form->getId());
        } catch (\RuntimeException $ex) {
            return $response->withStatus(404)
                            ->withJson([ 'status' => 'not-found' ]);
        }
        $questionTable = new QuestionTable($this->container->get('db'));
        $question = $questionTable->getQuestionById($choice->question);
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
        $choice = $form->toChoice($choice);
        if ($choiceTable->saveChoice($choice)) {
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
        $choiceTable = new ChoiceTable($this->container->get('db'));
        try {
            $choice = $choiceTable->getChoiceById($data['id']);
        } catch (\RuntimeException $ex) {
            return $response->withStatus(404)
                            ->withJson([ 'status' => 'not-found' ]);
        }
        $questionTable = new QuestionTable($this->container->get('db'));
        $question = $questionTable->getQuestionById($choice->question);
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
        if ($choiceTable->deleteChoice($choice)) {
            return $response->withJson([ 'status' => 'ok' ]);
        } else {
            return $response->withStatus(500)
                            ->withJson([ 'status' => 'not-modified' ]);
        }
    }

    public function ajaxToggle($request, $response, $args)
    {
        $data = $request->getAttribute('__data');
        if (!isset($data['id'])) {
            return $response->withStatus(400)
                            ->withJson([ 'status' => 'missing-id' ]);
        }
        $choiceTable = new ChoiceTable($this->container->get('db'));
        try {
            $choice = $choiceTable->getChoiceById($data['id']);
        } catch (\RuntimeException $ex) {
            return $response->withStatus(404)
                            ->withJson([ 'status' => 'not-found' ]);
        }
        $questionTable = new QuestionTable($this->container->get('db'));
        $question = $questionTable->getQuestionById($choice->question);
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
        $choice->right = ($choice->right ? 0 : 1);
        if ($choiceTable->saveChoice($choice)) {
            return $response->withJson([ 'status' => 'ok' ]);
        } else {
            return $response->withStatus(500)
                            ->withJson([ 'status' => 'not-modified' ]);
        }
    }
}