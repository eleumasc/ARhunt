<?php

namespace ARHunt\Signin\Middleware;

class SigninMiddleware
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function __invoke($request, $response, $next)
    {
        if (isset($_SESSION['user'])) {
            $this->container->get('view')->getEnvironment()->addGlobal('__user', $_SESSION['user']);
            return $next($request, $response);
        } else {
            if (!$request->isXhr()) {
                return $response->withStatus(301)
                                ->withHeader('Location', $this->container->get('router')->pathFor('root'));
            } else {
                return $response->withStatus(403)
                                ->withJson([ 'status' => 'you-shall-not-pass' ]);
            }
        }
    }
}