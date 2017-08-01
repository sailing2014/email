<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once __DIR__ . '/../web/init/loader.php';
//use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Connection\AMQPSSLConnection;
date_default_timezone_set('Asia/Shanghai');
ini_set('memory_limit', '192M');
//$Stime=microtime(true);

syslog(LOG_INFO,"sdcp_email [".date('Y-m-d H:i:s')."] start Dequeue php...\n");
//error_log("sdcp_email [".date('Y-m-d H:i:s')."] start Dequeue php...\n");

$config = array(
"ip" => "rabbitmq.qiwocloud1.com",
    "port" => 5671,
    //dev_doorbell
//    "name" => "68xx",
//"pwd" => "abxx",
//"vhost" => "68xxx"
);

//define('CERTS_PATH', '/home/email/conf');
$sslOptions = array(
//    'cafile' => CERTS_PATH . '/cacert.pem',
//    'capath' => CERTS_PATH . '/cert.pem',
    'verify_peer' => false
);

if($config)
{
    error_log("sdcp_email [".date('Y-m-d H:i:s')."] start sleeping for  3 seconds ...\n\n");
    sleep(3);
    error_log("sdcp_email [".date('Y-m-d H:i:s')."] end  sleeping for  3 seconds ...\n\n");

    try{
        syslog(LOG_INFO,"sdcp_email [".date('Y-m-d H:i:s')."] start Dequeue AMQP connection, "
            . "if no success disappear,then rabbitmq connection failed! ...\n\n");
//                error_log("sdcp_email [".date('Y-m-d H:i:s')."] start Dequeue AMQP connection, "
//                . "if no success disappear,then rabbitmq connection failed! ...\n\n");

        $connection = new AMQPSSLConnection($config["ip"], $config["port"], $config["name"], $config["pwd"], $config["vhost"],$sslOptions);
//        $connection = new AMQPConnection($config["ip"], $config["port"], $config["name"], $config["pwd"],$config["vhost"]);

    }
    catch (PhpAmqpLib\Exception\AMQPProtocolConnectionException $amqpException)
    {
        syslog(LOG_INFO,"sdcp_email [".date('Y-m-d H:i:s')."] catch rabbit connection exception ".$amqpException->getMessage().'...\n');
//                error_log("sdcp_email [".date('Y-m-d H:i:s')."] catch rabbit connection exception ...\n\n");
        return false;
    }
//    catch(PhpAmqpLib\Exception\AMQPRuntimeException $runtimeException){
//        syslog(LOG_INFO,"sdcp_email [".date('Y-m-d H:i:s')."] catch rabbit runtime exception: ".$runtimeException->getMessage().'...\n');
//        return false;
//    }

    syslog(LOG_INFO,"sdcp_email [".date('Y-m-d H:i:s')."] Dequeue rabbitmq connection success...\n\n");
//            error_log("sdcp_email [".date('Y-m-d H:i:s')."] Dequeue rabbitmq connection success...\n\n");
    $channel = $connection->channel();
    $queueName = $channel->queue_declare();
//    $channel->queue_declare('myqueue', false, false, false, false);
    $channel->queue_bind($queueName[0], 'amq.direct', "sdcp1234567");
    echo date('Y-m-d H:i:s').' [*] Waiting for messages. To exit press CTRL+C', "\n";

    $callback = function($content) {
        echo date('Y-m-d H:i:s').' content:'.json_encode($content);
    };

    $channel->basic_consume($queueName[0], '', false, true, false, false, $callback);

    while(count($channel->callbacks)) {
        $channel->wait();
    }

  $channel->close();
    syslog(LOG_INFO,"sdcp_email [".date('Y-m-d H:i:s')."] end Dequeue php...\n\n");
//            error_log("sdcp_email [".date('Y-m-d H:i:s')."] end Dequeue php...\n\n");
}