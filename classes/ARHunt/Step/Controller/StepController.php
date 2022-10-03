<?php

namespace ARHunt\Step\Controller;

use ARHunt\Hunt\Model\HuntTable;
use ARHunt\Hunt\Model\Hunt;
use ARHunt\Team\Model\TeamTable;
use ARHunt\Step\Model\StepTable;
use ARHunt\Step\Model\Step;
use ARHunt\Step\Form\StepForm;
use ARHunt\Media\Model\MediaTable;

class StepController
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function ajaxAdd($request, $response, $args)
    {
        $data = $request->getAttribute('__data');
        $form = new StepForm;
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
        if ($form->getMedia()) {
            $mediaTable = new MediaTable($this->container->get('db'));
            try {
                $media = $mediaTable->getMediaById($form->getMedia());
            } catch (\RuntimeException $ex) {
                return $response->withStatus(404)
                                ->withJson([ 'status' => 'not-found' ]);
            }
            if ($media->hunt !== $hunt->id) {
                return $response->withStatus(403)
                                ->withJson([ 'status' => 'you-shall-not-pass' ]);
            }
        }
        $step = $form->toStep(new Step);
        $step->team = $team->id;
        $stepTable = new StepTable($this->container->get('db'));
        if ($stepTable->saveStep($step)) {
            return $response->withJson([
                'status' => 'ok',
                'questionsListPath' => $this->container->get('router')->pathFor('questions-list', [
                    'team' => $step->team,
                    'sequence' => $step->sequence
                ])
            ]);
        } else {
            return $response->withStatus(500)
                            ->withJson([ 'status' => 'not-created' ]);
        }
    }

    public function ajaxEdit($request, $response, $args)
    {
        $data = $request->getAttribute('__data');
        $form = new StepForm;
        $form->setEdit(true);
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
            return $response->withStatus(404)
                            ->withJson([ 'status' => 'not-found' ]);
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
        if ($form->getMedia()) {
            $mediaTable = new MediaTable($this->container->get('db'));
            try {
                $media = $mediaTable->getMediaById($form->getMedia());
            } catch (\RuntimeException $ex) {
                return $response->withStatus(404)
                                ->withJson([ 'status' => 'not-found' ]);
            }
            if ($media->hunt !== $hunt->id) {
                return $response->withStatus(403)
                                ->withJson([ 'status' => 'you-shall-not-pass' ]);
            }
        }
        $step = $form->toStep($step);
        if ($stepTable->saveStep($step)) {
            return $response->withJson([ 'status' => 'ok' ]);
        } else {
            return $response->withJson([ 'status' => 'not-modified' ]);
        }
    }

    public function ajaxDelete($request, $response, $args)
    {
        $data = $request->getAttribute('__data');
        if (!isset($data['team'])) {
            return $response->withStatus(400)
                            ->withJson([ 'status' => 'missing-team' ]);
        }
        if (!isset($data['sequence'])) {
            return $response->withStatus(400)
                            ->withJson([ 'status' => 'missing-sequence' ]);
        }
        $teamTable = new TeamTable($this->container->get('db'));
        try {
            $team = $teamTable->getTeamById($data['team']);
        } catch (\RuntimeException $ex) {
            return $response->withStatus(404)
                            ->withJson([ 'status' => 'not-found' ]);
        }
        $stepTable = new StepTable($this->container->get('db'));
        try {
            $step = $stepTable->getStepByTeamAndSequence($team, $data['sequence']);
        } catch (\RuntimeException $ex) {
            return $response->withStatus(404)
                            ->withJson([ 'status' => 'not-found' ]);
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
        if ($stepTable->deleteStep($step)) {
            return $response->withJson([ 'status' => 'ok' ]);
        } else {
            return $response->withStatus(500)
                            ->withJson([ 'status' => 'not-modified' ]);
        }
    }

    public function ajaxMove($request, $response, $args)
    {
        $data = $request->getAttribute('__data');
        if (!isset($data['team'])) {
            return $response->withStatus(400)
                            ->withJson([ 'status' => 'missing-team' ]);
        }
        if (!isset($data['sequence'])) {
            return $response->withStatus(400)
                            ->withJson([ 'status' => 'missing-sequence' ]);
        }
        if (!isset($data['dir'])) {
            return $response->withStatus(400)
                            ->withJson([ 'status' => 'missing-dir' ]);
        }
        $dir = $data['dir'];
        if (!is_numeric($dir) || ($dir != 1 && $dir != -1)) {
            return $response->withStatus(400)
                            ->withJson([ 'status' => 'invalid-dir' ]);
        }
        $teamTable = new TeamTable($this->container->get('db'));
        try {
            $team = $teamTable->getTeamById($data['team']);
        } catch (\RuntimeException $ex) {
            return $response->withStatus(404)
                            ->withJson([ 'status' => 'not-found' ]);
        }
        $stepTable = new StepTable($this->container->get('db'));
        try {
            $step = $stepTable->getStepByTeamAndSequence($team, $data['sequence']);
        } catch (\RuntimeException $ex) {
            return $response->withStatus(404)
                            ->withJson([ 'status' => 'not-found' ]);
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
        if ($dir > 0) {
            $result = $stepTable->moveDown($step);
        } else {
            $result = $stepTable->moveUp($step);
        }
        if ($result) {
            return $response->withJson([ 'status' => 'ok' ]);
        } else {
            return $response->withStatus(500)
                            ->withJson([ 'status' => 'not-modified' ]);
        }
    }

    public function listSteps($request, $response, $args)
    {
        $teamTable = new TeamTable($this->container->get('db'));
        try {
            $team = $teamTable->getTeamById($args['team']);
        } catch (\RuntimeException $ex) {
            return $response->withStatus(404);
        }
        $huntTable = new HuntTable($this->container->get('db'));
        $hunt = $huntTable->getHuntById($team->hunt);
        if ($hunt->author !== $_SESSION['user']->nickname) {
            return $response->withStatus(403);
        }
        $stepTable = new StepTable($this->container->get('db'));
        $steps = $stepTable->getStepsByTeam($team);
        $mediaTable = new MediaTable($this->container->get('db'));
        foreach ($steps as $step) {
            if ($step->media) {
                $step->media = $mediaTable->getMediaById($step->media);
            }
        }
        return $this->container->get('view')->render($response, 'steps-list.html.twig', [
            'hunt' => $hunt,
            'team' => $team,
            'steps' => $steps,
            'pathLength' => round($teamTable->getPathLength($team))
        ]);
    }

    public function add($request, $response, $args)
    {
        $teamTable = new TeamTable($this->container->get('db'));
        try {
            $team = $teamTable->getTeamById($args['team']);
        } catch (\RuntimeException $ex) {
            return $response->withStatus(404);
        }
        $huntTable = new HuntTable($this->container->get('db'));
        $hunt = $huntTable->getHuntById($team->hunt);
        if ($hunt->author !== $_SESSION['user']->nickname) {
            return $response->withStatus(403);
        }
        if ($hunt->status != Hunt::EDITING && $hunt->status != Hunt::PUBLISHED) {
            return $response->withStatus(403);
        }
        $mediaTable = new MediaTable($this->container->get('db'));
        return $this->container->get('view')->render($response, 'steps-edit.html.twig', [
            'action' => 'add',
            'hunt' => $hunt,
            'team' => $team,
            'media' => $mediaTable->getMediaByHunt($hunt)
        ]);
    }

    public function edit($request, $response, $args)
    {
        $teamTable = new TeamTable($this->container->get('db'));
        try {
            $team = $teamTable->getTeamById($args['team']);
        } catch (\RuntimeException $ex) {
            return $response->withStatus(404);
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
        if ($hunt->status != Hunt::EDITING && $hunt->status != Hunt::PUBLISHED) {
            return $response->withStatus(403);
        }
        $mediaTable = new MediaTable($this->container->get('db'));
        return $this->container->get('view')->render($response, 'steps-edit.html.twig', [
            'action' => 'edit',
            'hunt' => $hunt,
            'team' => $team,
            'media' => $mediaTable->getMediaByHunt($hunt),
            'step' => $step
        ]);
    }
}