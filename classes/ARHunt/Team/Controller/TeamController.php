<?php

namespace ARHunt\Team\Controller;

use ARHunt\Hunt\Model\HuntTable;
use ARHunt\Hunt\Model\Hunt;
use ARHunt\Team\Model\TeamTable;
use ARHunt\Team\Model\Team;
use ARHunt\Team\Form\TeamForm;

class TeamController
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function ajaxAdd($request, $response, $args)
    {
        $data = $request->getAttribute('__data');
        $form = new TeamForm;
        $form->setEdit(false);
        if (!$form->setData($data)) {
            return $response->withStatus(400)
                            ->withJson([ 'status' => $form->getError() ]);
        }
        $huntTable = new HuntTable($this->container->get('db'));
        try {
            $hunt = $huntTable->getHuntById($form->getHunt());
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
        $team = $form->toTeam(new Team);
        $team->hunt = $hunt->id;
        $teamTable = new TeamTable($this->container->get('db'));
        if ($teamTable->saveTeam($team)) {
            return $response->withJson([
                'status' => 'ok',
                'stepsListPath' => $this->container->get('router')->pathFor('steps-list', [
                    'team' => $team->id
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
        $form = new TeamForm;
        $form->setEdit(true);
        if (!$form->setData($data)) {
            return $response->withStatus(400)
                            ->withJson([ 'status' => $form->getError() ]);
        }
        $teamTable = new TeamTable($this->container->get('db'));
        try {
            $team = $teamTable->getTeamById($form->getId());
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
        $team = $form->toTeam($team);
        if ($teamTable->saveTeam($team)) {
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
        $teamTable = new TeamTable($this->container->get('db'));
        try {
            $team = $teamTable->getTeamById($data['id']);
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
        if ($teamTable->deleteTeam($team)) {
            return $response->withJson([ 'status' => 'ok' ]);
        } else {
            return $response->withStatus(500)
                            ->withJson([ 'status' => 'not-modified' ]);
        }
    }

    public function listTeams($request, $response, $args)
    {
        $huntTable = new HuntTable($this->container->get('db'));
        try {
            $hunt = $huntTable->getHuntById($args['hunt']);
        } catch (\RuntimeException $ex) {
            return $response->withStatus(404);
        }
        if ($hunt->author !== $_SESSION['user']->nickname) {
            return $response->withStatus(403);
        }
        $teamTable = new TeamTable($this->container->get('db'));
        return $this->container->get('view')->render($response, 'teams-list.html.twig', [
            'hunt' => $hunt,
            'teams' => $teamTable->getTeamsByHunt($hunt)
        ]);
    }

    public function add($request, $response, $args)
    {
        $huntTable = new HuntTable($this->container->get('db'));
        try {
            $hunt = $huntTable->getHuntById($args['hunt']);
        } catch (\RuntimeException $ex) {
            return $response->withStatus(404);
        }
        if ($hunt->author !== $_SESSION['user']->nickname) {
            return $response->withStatus(403);
        }
        if ($hunt->status != Hunt::EDITING && $hunt->status != Hunt::PUBLISHED) {
            return $response->withStatus(403);
        }
        $team = new Team;
        $team->name = '';
        $team->color = 'ffffff';
        return $this->container->get('view')->render($response, 'teams-edit.html.twig', [
            'action' => 'add',
            'hunt' => $hunt,
            'team' => $team
        ]);
    }

    public function edit($request, $response, $args)
    {
        $teamTable = new TeamTable($this->container->get('db'));
        try {
            $team = $teamTable->getTeamById($args['id']);
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
        return $this->container->get('view')->render($response, 'teams-edit.html.twig', [
            'action' => 'edit',
            'hunt' => $hunt,
            'team' => $team
        ]);
    }
}