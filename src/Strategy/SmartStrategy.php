<?php

namespace Xocotlah\SmartController\Strategy;

use Exception;
use Xocotlah\SmartController\SmartControllerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use League\Route\Strategy\StrategyInterface;
use League\Route\Strategy\ApplicationStrategy;
use League\Route\Route;
use League\Route\Http\Exception\NotFoundException;
use League\Route\Http\Exception\MethodNotAllowedException;

class SmartStrategy extends ApplicationStrategy
{
    protected $contentIsJson = false;

    /**
     * {@inheritdoc}
     */
    public function getCallable(Route $route, array $vars)
    {
        return function (
            ServerRequestInterface $request,
            ResponseInterface $response,
            callable $next
        ) use (
            $route,
            $vars
        ) {
            $callable = $route->getCallable();

            // Inject Route parameters into the SmartController
            if (is_array($callable) && isset($callable[0]) && $callable[0] instanceof SmartControllerInterface) {
                $callable[0]->setParams($vars);
            }

            $return = call_user_func_array($callable, [$request, $response, $vars]);

            // Write the content to the response body
            $response = $this->elaborateResponse($return, $response);
            $response = $next($request, $response);

            // Add the appropriate header for Json content
            if ($this->contentIsJson) {
                return $response->withAddedHeader('content-type', 'application/json');
            }

            return $response;
        };
    }

    protected function elaborateResponse($content, $response)
    {
        if (is_scalar($content) || (is_object($content) && method_exists($content, '__toString'))) {
            $response->getBody()->write($content);
        } elseif (is_array($content)) {
            $this->contentIsJson = true;
            $response->getBody()->write(json_encode($content));
        }
        return $response;
    }
}
