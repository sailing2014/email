<?php

use Phalcon\DI\FactoryDefault,
    Phalcon\Mvc\View,
    Phalcon\Mvc\Url as UrlResolver,
    Phalcon\Config\Adapter\Ini as ConfigIni,
    Phalcon\Mvc\Router,
    App\Helper\HttpRequest,
    App\Helper\HttpResponse,
    Phalcon\Mvc\Dispatcher,
    App\Plugins\NotFoundPlugin,
    App\Plugins\SecurityPlugin,
    Phalcon\Session\Adapter\Files as SessionAdapter,
    Phalcon\Events\Manager as EventsManager;

$di = new FactoryDefault();
$di->set('url', function() {
    $url = new UrlResolver();
    $url->setBaseUri('/');
    return $url;
}, true);

$di->set('router', function() {
    $router = new Router();
    $router->setDefaultNamespace("App\Controllers");

    $router->add("/v1/email/:action", array(
    'controller' => 'email',
    'action' => 1,
    ));
    $router->add("/v1/email/intl/config/:action", array(
    'controller' => 'config',
    'action' => 1,
    ));
    return $router;
});

$di->set('dispatcher', function() {
    $eventsManager = new EventsManager;
    $eventsManager->attach('dispatch:beforeDispatch', new SecurityPlugin);
    $eventsManager->attach('dispatch:beforeException', new NotFoundPlugin);
    $dispatcher = new Dispatcher;
    $dispatcher->setEventsManager($eventsManager);
    return $dispatcher;
});

$di->setShared("code.config", function() {
    return new ConfigIni(CONF_PATH . "code.config.ini");
});

$di->setShared("api.config", function() {
    return new ConfigIni(CONF_PATH . "api.config.ini");
});

$di->setShared("rabbitmq.config", function() {
    return new ConfigIni(CONF_PATH . "rabbitmq.config.ini");
});

$di->setShared("smtp.config", function() {
    return new ConfigIni(CONF_PATH . "smtp.config.ini");
});

$di->set('view', function() {
    $view = new View();
    $view->disable();
    return $view;
}, true);
$di->set('http.request', function() {
    $request = new HttpRequest();
    $request->requestJson();
    return $request;
}, TRUE);
$di->set('http.response', function() use($di){
    $request = new HttpResponse($di);
    return $request;
}, TRUE);

$di->set('session', function() {
    $session = new SessionAdapter();
    $session->start();
    return $session;
});
