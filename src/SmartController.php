<?php

namespace Xocotlah\SmartController;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use League\Route\RouteCollection;
use Xocotlah\SmartController\SmartControllerInterface;

abstract class SmartController implements SmartControllerInterface
{
    /**
     * The Request instance
     * @var Psr\Http\Message\ServerRequestInterface;
     */
    protected $request;

    /**
     * The Response instance
     * @var Psr\Http\Message\ResponseInterface
     */
    protected $response;

    /**
     * The Router instance
     * @var League\Route\RouteCollection
     */
    private $router;

    /**
     * The Route parameters
     * @var array
     */
    protected $params;

    /**
     * {@inheritdoc}
     */
    public function __construct(
        ServerRequestInterface $request,
        ResponseInterface $response,
        RouteCollection $router
    ) {
        $this->request = $request;
        $this->response = $response;
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function setParams(array $params)
    {
        $this->params = $params;
    }
}
