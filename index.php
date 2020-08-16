<?php
require 'vendor/autoload.php';


$router = new \Gotee\Routing\Router();
$router->get('/users', function($request) {
    dd($request);
})
    ->setMiddleware('AuthMiddleware')
    ->setName('user');

$router->get('/users/login', 'UserController@index')
    ->setMiddleware('AuthMiddleware')
    ->setName('user');


$router->group(['name' => 'users::', 'prefix' => '/users', 'middleware' => ['x']], function (\Gotee\Routing\Router $router) {
    $router->get('/notification', 'NotificationController')
        ->setName('notification')
        ->setMiddleware('y');

    $router->get('/{id}', 'NotificationController')
        ->setName('notification');

    $router->group(['name' => 'notification.', 'prefix' => '/notification'], function (\Gotee\Routing\Router $router) {
        $router->get('/send', 'NotificationController')
            ->setName('send');
    });
});


$router->group(['name' => 'comment:', 'prefix' => '/comments'], function (\Gotee\Routing\Router $router) {
    $router->get('/', 'CommentController')
        ->setName('index');
});

$app = new \Gotee\App($router);

$app->start();
die();

$dispatcher = \FastRoute\simpleDispatcher(function (\FastRoute\RouteCollector $route) {
    $route->addGroup();
    $route->get('/users/{id}/posts', 'TestController@action');
});

$routeInfo = $dispatcher->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);

dd($routeInfo);
