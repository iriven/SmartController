<?php

namespace Xocotlah\SmartController;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use League\Route\RouteCollection;

interface SmartControllerInterface
{
    /**
     * Build the SmartController with Request, Response and Router injected
     * @method __construct
     * @param  ServerRequestInterface $request  [description]
     * @param  ResponseInterface      $response [description]
     * @param  RouteCollection        $router   [description]
     */
    public function __construct(
        ServerRequestInterface $request,
        ResponseInterface $response,
        RouteCollection $router
    );

    /**
     * Inject the route parameters
     * @method setParams
     * @param  array     $params
     */
    public function setParams(array $params);
}
