<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once __DIR__ . '/../web/init/loader.php';
use PhpAmqpLib\Connection\AMQPConnection;
use App\Models\EmailModel;

date_default_timezone_set('Asia/Shanghai');
ini_set('memory_limit', '192M');
//$Stime=microtime(true);

syslog(LOG_INFO,"sdcp_email [".date('Y-m-d H:i:s')."] start Dequeue php...\n");
//error_log("sdcp_email [".date('Y-m-d H:i:s')."] start Dequeue php...\n");
$rabbitmq_file = "/home/email/conf/rabbitmq.config.ini";
$config = false;

syslog(LOG_INFO,"sdcp_email [".date('Y-m-d H:i:s')."] start Dequeue get config...\n");
//error_log("sdcp_email [".date('Y-m-d H:i:s')."] start Dequeue get config...\n\n");

if (file_exists($rabbitmq_file))
    {   
        
        $config = parse_ini_file($rabbitmq_file);
        syslog(LOG_INFO,"sdcp_email [".date('Y-m-d H:i:s')."] successfully Dequeue get config ...\n");
//        error_log("sdcp_email [".date('Y-m-d H:i:s')."] successfully Dequeue get config ...\n\n");
    }

    syslog(LOG_INFO,"sdcp_email [".date('Y-m-d H:i:s')."] end  Dequeue get config...\n\n");
//error_log("sdcp_email [".date('Y-m-d H:i:s')."] end  Dequeue get config...\n\n");

if($config)
    {   
//        error_log("sdcp_email [".date('Y-m-d H:i:s')."] start sleeping for  3 seconds ...\n\n");
        sleep(3);
        error_log("sdcp_email [".date('Y-m-d H:i:s')."] end  sleeping for  3 seconds ...\n\n");          
        
        try{
                syslog(LOG_INFO,"sdcp_email [".date('Y-m-d H:i:s')."] start Dequeue AMQP connection, "
                    . "if no success disappear,then rabbitmq connection failed! ...\n\n");
//                error_log("sdcp_email [".date('Y-m-d H:i:s')."] start Dequeue AMQP connection, "
//                . "if no success disappear,then rabbitmq connection failed! ...\n\n");

                $connection = new AMQPConnection($config["ip"], $config["port"], $config["name"], $config["pwd"],$config["vhost"]);                 
            }
        catch (PhpAmqpLib\Exception\AMQPProtocolConnectionException $amqpException)
            {
                syslog(LOG_INFO,"sdcp_email [".date('Y-m-d H:i:s')."] catch rabbit connection exception ...\n\n");
//                error_log("sdcp_email [".date('Y-m-d H:i:s')."] catch rabbit connection exception ...\n\n");
                return false;
            }

            syslog(LOG_INFO,"sdcp_email [".date('Y-m-d H:i:s')."] Dequeue rabbitmq connection success...\n\n");
//            error_log("sdcp_email [".date('Y-m-d H:i:s')."] Dequeue rabbitmq connection success...\n\n");
            $channel = $connection->channel();                
            $channel->queue_declare('email', false, true, false, false);
            echo date('Y-m-d H:i:s').' [*] Waiting for messages. To exit press CTRL+C', "\n";

                $callback = function($msg) {
                  $model = new EmailModel(); 
                  $msg_arr = json_decode($msg->body);
                  if($msg_arr) {
                      $address = $msg_arr->email;
                      $title = $msg_arr->title;
                      $content = $msg_arr->content;
                      $api_key = $msg_arr->api_key;
                      $type = $msg_arr->type;

                      syslog(LOG_INFO, "sdcp_email [" . date('Y-m-d H:i:s') . "] start Dequeue sendEmail...\n\n");
//                    error_log("sdcp_email [".date('Y-m-d H:i:s')."] start Dequeue sendEmail...\n\n");
                      $ret = $model->sendEmail($address, $title, $content, $api_key, $type);
                  }else{
                      syslog(LOG_INFO, "sdcp_email [" . date('Y-m-d H:i:s') . "] Dequeue fail as msg is empty...\n\n");
                  }

                  if($ret){
                      syslog(LOG_INFO,"sdcp_email [".date('Y-m-d H:i:s')."] successfully Dequeue send email ".
                          $address .",title:". $title .",api_key:".$api_key .",type:".$type ."\n\n");
//                      error_log("sdcp_email [".date('Y-m-d H:i:s')."] successfully Dequeue send email ".
//                              $address .",title:". $title .",api_key:".$api_key .",type:".$type ."\n\n");
                      echo date('Y-m-d H:i:s')." [x] Send ", $address, " successful!\n";
                  }else{
                      syslog(LOG_INFO,"sdcp_email [".date('Y-m-d H:i:s')."] failed Dequeue sending email ".
                          $address .",title:". $title.",api_key:".$api_key .",type:".$type ."\n\n");
//                      error_log("sdcp_email [".date('Y-m-d H:i:s')."] failed Dequeue sending email ".
//                              $address .",title:". $title.",api_key:".$api_key .",type:".$type ."\n\n");
                      echo date('Y-m-d H:i:s')." [x] Send ", $address, " failed!\n";
                  }

                };

            $channel->basic_consume('email', '', false, true, false, false, $callback);

            while(count($channel->callbacks)) {
                    $channel->wait();
            }
            syslog(LOG_INFO,"sdcp_email [".date('Y-m-d H:i:s')."] end Dequeue php...\n\n");
//            error_log("sdcp_email [".date('Y-m-d H:i:s')."] end Dequeue php...\n\n");
    }