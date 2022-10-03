<?php

namespace Stdlib\Middleware;

class JSONDecodeRequestBodyMiddleware
{
    public function __invoke($request, $response, $next)
    {
        $body = $request->getBody();
        if ($data = json_decode($body, true)) {
            return $next($request->withAttribute('__data', $data), $response);
        } else {
            return $response->withStatus(400)
                            ->withJson([ 'status' => 'invalid-json' ]);
        }
    }
}