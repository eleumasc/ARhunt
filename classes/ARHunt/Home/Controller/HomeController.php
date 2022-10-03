<?php

namespace ARHunt\Home\Controller;

class HomeController
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function home($request, $response, $args)
    {
        $response = $this->container->get('view')->render($response, 'home.html.twig', []);
        return $response;
    }

    public function contactUs($request, $response, $args)
    {
        $response = $this->container->get('view')->render($response, 'contact-us.html.twig', []);
        return $response;
    }
}