<?php

namespace ARHunt\Signin\Middleware;

class NoSigninMiddleware
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function __invoke($request, $response, $next)
    {
        if (!isset($_SESSION['user'])) {
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