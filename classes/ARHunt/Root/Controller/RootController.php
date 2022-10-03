<?php

namespace ARHunt\Root\Controller;

class RootController
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function root($request, $response, $args)
    {
        $response = $response->withStatus(301);
        if (isset($_SESSION['user'])) {
            $response = $response->withHeader('Location', $this->container->get('router')->pathFor('home'));
        } else {
            $response = $response->withHeader('Location', $this->container->get('router')->pathFor('signin'));
        }
        return $response;
    }
}