<?php

namespace ARHunt\Hunt\Controller;

use ARHunt\Hunt\Model\HuntTable;
use ARHunt\Hunt\Model\Hunt;

class PlayController
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function play($request, $response, $args)
    {
        $huntTable = new HuntTable($this->container->get('db'));
        try {
            $hunt = $huntTable->getHuntById($args['id']);
        } catch (\RuntimeException $ex) {
            return $response->withStatus(404);
        }
        if ($hunt->author === $_SESSION['user']->nickname) {
            return $response->withStatus(403);
        }
        if ($hunt->status == Hunt::EDITING || $hunt->status == Hunt::PUBLISHED) {
            return $response->withStatus(403);
        }
        return $this->container->get('view')->render($response, 'hunts-play-make.html.twig', [
            'hunt' => $hunt,
            'play' => true
        ]);
    }
}