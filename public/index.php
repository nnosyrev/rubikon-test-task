<?php

use App\Config\Config;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

require_once __DIR__ . '/../vendor/autoload.php';

// Загружаем конфиг при подключении
Config::load();

$routes = require_once __DIR__ . '/../routes/routes.php';
$dispatcher = FastRoute\simpleDispatcher($routes);

$request = Request::createFromGlobals();

$routeInfo = $dispatcher->dispatch($request->getMethod(), $request->getPathInfo());
switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        // ... 404 Not Found
        $response = new Response(
            '404 Not Found',
            Response::HTTP_NOT_FOUND,
            ['content-type' => 'text/html']
        );
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        // ... 405 Method Not Allowed
        $response = new Response(
            '405 Method Not Allowed',
            Response::HTTP_METHOD_NOT_ALLOWED,
            ['content-type' => 'text/html']
        );
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];

        $controllerClass = $handler[0];
        $controllerMethod = $handler[1];

        try {
            $controller = new $controllerClass();
            $response = $controller->$controllerMethod($vars);
        } catch (\Throwable $e) {
            $response = new Response(
                '500 Internal Server Error',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                ['content-type' => 'text/html']
            );
        }
        break;
}

$response->send();
