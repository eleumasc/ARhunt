<?php

namespace ARHunt\Hunt\Controller;

use ARHunt\Hunt\Model\HuntTable;
use ARHunt\Hunt\Model\Hunt;
use ARHunt\Hunt\Form\HuntForm;
use ARHunt\Hunt\Model\PlayerTable;

class HuntController
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function ajaxAdd($request, $response, $args)
    {
        $data = $request->getAttribute('__data');
        $form = new HuntForm;
        $form->setEdit(false);
        if (!$form->setData($data)) {
            return $response->withStatus(400)
                            ->withJson([ 'status' => $form->getError() ]);
        }
        $hunt = $form->toHunt(new Hunt);
        $hunt->author = $_SESSION['user']->nickname;
        $huntTable = new HuntTable($this->container->get('db'));
        if ($huntTable->saveHunt($hunt)) {
            $this->container->get('storage')->createDirectory($hunt->id);
            return $response->withJson([
                'status' => 'ok',
                'huntsViewPath' => $this->container->get('router')->pathFor('hunts-view', [
                    'id' => $hunt->id
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
        $form = new HuntForm;
        $form->setEdit(true);
        if (!$form->setData($data)) {
            return $response->withStatus(400)
                            ->withJson([ 'status' => $form->getError() ]);
        }
        $huntTable = new HuntTable($this->container->get('db'));
        try {
            $hunt = $huntTable->getHuntById($form->getId());
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
        $hunt = $form->toHunt($hunt);
        if ($huntTable->saveHunt($hunt)) {
            return $response->withJson([
                'status' => 'ok',
                'huntsViewPath' => $this->container->get('router')->pathFor('hunts-view', [
                    'id' => $hunt->id
                ])
            ]);
        } else {
            return $response->withJson([
                'status' => 'not-modified',
                'huntsViewPath' => $this->container->get('router')->pathFor('hunts-view', [
                    'id' => $hunt->id
                ])
            ]);
        }
    }

    public function ajaxDelete($request, $response, $args)
    {
        $data = $request->getAttribute('__data');
        if (!isset($data['id'])) {
            return $response->withStatus(404)
                            ->withJson([ 'status' => 'missing-id' ]);
        }
        $huntTable = new HuntTable($this->container->get('db'));
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
        if ($huntTable->deleteHunt($hunt)) {
            $this->container->get('storage')->deleteDirectory($hunt->id);
            return $response->withJson([ 'status' => 'ok' ]);
        } else {
            return $response->withStatus(500)
                            ->withJson([ 'status' => 'not-modified' ]);
        }
    }

    public function listPlay($request, $response, $args)
    {
        $huntTable = new HuntTable($this->container->get('db'));
        return $this->container->get('view')->render($response, 'hunts-list-play.html.twig', [
            'hunts' => $huntTable->getHuntsByAnyoneExcept($_SESSION['user'], [
                Hunt::PUBLISHED, Hunt::CALLING, Hunt::CALLING, Hunt::PREPARING, Hunt::WAITING, Hunt::PLAYING ])
        ]);
    }

    public function listMake($request, $response, $args)
    {
        $huntTable = new HuntTable($this->container->get('db'));
        return $this->container->get('view')->render($response, 'hunts-list-make.html.twig', [
            'hunts' => $huntTable->getHuntsByAuthor($_SESSION['user'], [
                Hunt::EDITING, Hunt::PUBLISHED, Hunt::CALLING, Hunt::PREPARING, Hunt::WAITING, Hunt::PLAYING ])
        ]);
    }

    public function view($request, $response, $args)
    {
        $huntTable = new HuntTable($this->container->get('db'));
        try {
            $hunt = $huntTable->getHuntById($args['id']);
        } catch (\RuntimeException $ex) {
            return $response->withStatus(404);
        }
        if ($hunt->author !== $_SESSION['user']->nickname && $hunt->status == Hunt::EDITING) {
            return $response->withStatus(403);
        }
        $playerExists = false;
        if ($hunt->status == Hunt::PREPARING || $hunt->status == Hunt::WAITING || $hunt->status == Hunt::PLAYING) {
            $playerTable = new PlayerTable($this->container->get('db'));
            $playerExists = $playerTable->playerExists($hunt, $_SESSION['user']);
        }
        return $this->container->get('view')->render($response, 'hunts-view.html.twig', [
            'hunt' => $hunt,
            'playerExists' => $playerExists
        ]);
    }

    public function add($request, $response, $args)
    {
        return $this->container->get('view')->render($response, 'hunts-edit.html.twig', [
            'action' => 'add'
        ]);
    }

    public function edit($request, $response, $args)
    {
        if (!isset($args['id'])) {
            return $response->withStatus(500);
        }
        $huntTable = new HuntTable($this->container->get('db'));
        try {
            $hunt = $huntTable->getHuntById($args['id']);
        } catch (\RuntimeException $ex) {
            return $response->withStatus(404);
        }
        if ($hunt->author !== $_SESSION['user']->nickname) {
            return $response->withStatus(403);
        }
        if ($hunt->status != Hunt::EDITING && $hunt->status != Hunt::PUBLISHED) {
            return $response->withStatus(403);
        }
        return $this->container->get('view')->render($response, 'hunts-edit.html.twig', [
            'action' => 'edit',
            'hunt' => $hunt
        ]);
    }
}