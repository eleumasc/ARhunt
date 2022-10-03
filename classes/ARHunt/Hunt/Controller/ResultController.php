<?php

namespace ARHunt\Hunt\Controller;

use ARHunt\Hunt\Model\HuntTable;
use ARHunt\Hunt\Model\Hunt;
use ARHunt\Team\Model\TeamTable;

class ResultController
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function result($request, $response, $args)
    {
        $huntTable = new HuntTable($this->container->get('db'));
        try {
            $hunt = $huntTable->getHuntById($args['id']);
        } catch (\RuntimeException $ex) {
            return $response->withStatus(404);
        }
        if ($hunt->status != Hunt::CLOSED && $hunt->status != Hunt::CANCELLED) {
            return $response->withStatus(403);
        }
        $teams = $huntTable->getResultByHunt($hunt);
        $teamTable = new TeamTable($this->container->get('db'));
        foreach ($teams as $team) {
            $team->stats = $teamTable->getStatsByTeam($team);
        }
        return $this->container->get('view')->render($response, 'hunts-result.html.twig', [
            'hunt' => $hunt,
            'teams' => $teams
        ]);
    }
}