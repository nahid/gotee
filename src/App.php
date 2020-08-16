<?php

namespace Gotee;

use Laminas\Diactoros\ServerRequestFactory;
use Gotee\Routing\Router;
use Psr\Container\ContainerInterface;

class App
{

    /**
     * @var Router
     */
    protected Router $router;
    /**
     * @var ContainerInterface
     */
    private ?ContainerInterface $container;

    /**
     * App constructor.
     * @param Router $router
     * @param ContainerInterface $container
     */
    public function __construct(Router $router, ?ContainerInterface $container = null)
    {
        $this->router = $router;
        $this->container = $container;
    }

    public function getContainer() : ContainerInterface
    {
        return $this->container;
    }

    public function getRouter() : Router
    {
        return $this->router;
    }


    public function start()
    {
        $this->getRouter()->register();
        $routeInfo = $this->getRouter()->resolve();
        $request = ServerRequestFactory::fromGlobals();
        $routeInfo['handler']($request);
    }


}