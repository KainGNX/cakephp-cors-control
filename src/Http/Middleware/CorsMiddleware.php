<?php

namespace CorsControl\Http\Middleware;

use CorsControl\Http\CorsResolver;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class CorsMiddleware
{
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next
    ) {

        $response = $response->cors($request)
                ->allowOrigin(['*.localhost'])
                ->allowMethods(['GET', 'POST', 'PUT', 'PATCH', 'DELETE'])
                ->allowHeaders(['*'])
                ->allowCredentials()
                ->exposeHeaders(['Link'])
                ->maxAge(300)
                ->build();

            return ($request->getMethod() === 'OPTIONS') ? $response :  $next($request, $response);
    }
}
