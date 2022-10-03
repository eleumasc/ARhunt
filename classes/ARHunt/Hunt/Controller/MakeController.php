<?php

namespace ARHunt\Hunt\Controller;

use ARHunt\Hunt\Model\HuntTable;

class MakeController
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function make($request, $response, $args)
    {
        $huntTable = new HuntTable($this->container->get('db'));
        try {
            $hunt = $huntTable->getHuntById($args['id']);
        } catch (\RuntimeException $ex) {
            return $response->withStatus(404);
        }
        if ($hunt->author !== $_SESSION['user']->nickname) {
            return $response->withStatus(403);
        }
        return $this->container->get('view')->render($response, 'hunts-play-make.html.twig', [
            'hunt' => $hunt,
            'play' => false
        ]);
    }
}