<?php

namespace App\Controllers;
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;
class EmailController extends ControllerBase {

    public $api_key = '';   
    public $http_request = null;
    public $http_response = null;   
    
    public function beforeExecuteRoute() {
        $this->http_request = $this->getDI()->getShared("http.request");
        $this->http_response = $this->getDI()->getShared("http.response");
        $this->api_key = $this->http_request->getParam("api_key");        
        
    }

    public function testAction() {
        echo "test action";
        //echo phpinfo();
        var_dump($this->request->isPost());
    }
    
  
	public function sendAction()
	{
                error_log("sdcp_email [".date('Y-m-d H:i:s')."] start send email Action...\n\n");
                ignore_user_abort(true); 
                if(!$this->checkMethod('POST'))
                {
                    return;
                }                
		
                $email=$this->http_request->getParam('email');        
                $title=$this->http_request->getParam('title');       
                $content= $this->http_request->getParam('content');
                $type = $this->http_request->getParam('type');
                if(!$email || !$title || !$content)
                {
                    echo $this->http_response->res(400);
                    return;
                }  
                
                $type = empty($type)?1:$type;
		
//                $model = new EmailModel();
//                
//                $ret = $model->sendEmail($email, $title, $content);
                $msg_arr = array(
                                "email"=>$email,
                                "title"=>$title,
                                "content"=>$content,
                                "api_key"=>  $this->api_key,
                                "type"=>$type
                                );
                $msg = json_encode($msg_arr);
                
                error_log("sdcp_email sdcp_email [".date('Y-m-d H:i:s')."] start send email msg to queue...\n\n");
		$this->enQueue($msg);
		
                $ret = true;
                
		if($ret)
		{
                        error_log("sdcp_email [".date('Y-m-d H:i:s')."] end send email msg to queue successfully...\n\n");
			echo $this->http_response->res(200);
		}else{
                        error_log("sdcp_email [".date('Y-m-d H:i:s')."] end send email msg to queue failed...\n\n");
                        echo $this->http_response->res(10211);
                }
		
                error_log("sdcp_email [".date('Y-m-d H:i:s')."] end send email Action...\n\n\n\n");
		return;
	}
        
        
        
           protected  function enQueue($msg)
	{       
                error_log("sdcp_email [".date('Y-m-d H:i:s')."] enter enQueue internal method ...\n\n");
                $config = $this->getConfig('rabbitmq.config', 'rabbitmq');         
                
                error_log("sdcp_email [".date('Y-m-d H:i:s')."] start enQueue AMQPConnection,"
                        . " config:\n ip: ". $config["ip"]. ", port: " .$config["port"] . 
                        ", name: " .$config["name"] .", pwd: ". $config["pwd"]. 
                        ", vhost: ". $config["vhost"] ." ...\n\n "
                        . "if no enQueue rabbit connection success appear,then connection failed!\n\n");
                try
                {
                        $connection = new AMQPConnection($config["ip"], $config["port"], $config["name"], $config["pwd"],$config["vhost"]); 
                }
                catch (PhpAmqpLib\Exception\AMQPProtocolConnectionException $amqpException)
                {
                        error_log("sdcp_email [".date('Y-m-d H:i:s')."] catch rabbit connection exception $amqpException...\n\n");
                        return false;
                }
                
                error_log("sdcp_email [".date('Y-m-d H:i:s')."] enQueue rabbit connection success ...\n\n");
                $channel = $connection->channel();
                $channel->queue_declare('email', false, true, false, false);
                $msg_obj = new AMQPMessage($msg);
                $channel->basic_publish($msg_obj, '', 'email');
        //                echo " [x] Sent". $msg."\n";
                $channel->close();
                $connection->close();     
                error_log("sdcp_email [".date('Y-m-d H:i:s')."] exit from enQueue internal method ...\n\n");
                
	}
       
}