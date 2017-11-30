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

    /**
    * Return the URL corresponding to a given named route with specified parameters
    * @method url
    * @param  string $routeName The route name
    * @param  array  $args      The parameters of the route (values for the placeholders)
    * @return string            The valid URL, usable in links, redirects, etc.
    */
    public function url($routeName, $args = [])
    {
        // If no route of that name exists, do not bother
        try {
            $path = $this->router->getNamedRoute($routeName)->getPath();
        } catch (\Exception $e) {
            return $routeName;
        }

        // Find parameters in the route path
        $regex = \FastRoute\RouteParser\std::VARIABLE_REGEX;

        // Do not try to replace placeholders when values are not given
        $return = preg_replace_callback("~$regex~x", function ($matches) use ($args) {
            return $args[$matches[1]] ?? $matches[0];
        }, $path);

        return preg_replace('`(\[.+\])$`', '', $return);
    }
}
