<?php

namespace ARHunt\Setting\Controller;

class SettingController
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function settings($request, $response, $args)
    {
        return $this->container->get('view')->render($response, 'settings.html.twig', [
            'user' => $_SESSION['user']
        ]);
    }
}