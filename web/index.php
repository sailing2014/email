<?php

error_reporting(E_ALL);

use Phalcon\Mvc\Application;

date_default_timezone_set('Asia/Shanghai');

define('APP_PATH', realpath('..') . '/');
define('CONF_PATH', "/home/email/conf/");
define('SERVICE_KEY','djddf2893kjdsdfdi');
define('SERVICE_SECRET','alksdfjaslkdfj');

try {
    
    include __DIR__ . "/init/loader.php";   
    include __DIR__ . "/init/services.php";  
    
    $application = new Application($di);   
    $application->response->setContentType("application/json", "utf-8");
    echo $application->handle()->getContent();   
} catch (Phalcon\Exception $e) {
    echo $e->getMessage();
}