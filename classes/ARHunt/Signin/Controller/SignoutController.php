<?php

namespace ARHunt\Signin\Controller;

class SignoutController
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function signout($request, $response, $args)
    {
        session_destroy();
        return $response->withStatus(301)->withHeader('Location', $this->container->get('router')->pathFor('root'));
    }
}