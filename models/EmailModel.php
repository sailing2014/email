<?php
namespace App\Models;
use Mail\PHPMailer;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of EmailModel
 *
 * @author Administrator
 */
class EmailModel extends ModelBase {

  
    public function __construct() {
        parent::__construct();
    }

    public function sendEmail($address,$title, $content,$api_key,$type=1){
                    $mailconf = $this->getMailConf($api_key, $type);                   
                    if(!$mailconf){
                        return false;
                    }           
                    
		//Create a new PHPMailer instance
		$mail = new PHPMailer();		
		$mail->isSMTP();                                      // Set mailer to use SMTP
		$mail->Host = $mailconf['host'];  // Specify main and backup SMTP servers
		$mail->SMTPAuth = true;                               // Enable SMTP authentication
		$mail->Username = $mailconf['username'];                 // SMTP username
		$mail->Password = $mailconf['password'];                           // SMTP password
		//$mail->SMTPSecure = 'ssl';                      // Enable encryption, 'ssl' also acc
		$mail->SMTPDebug  = 0; 
		$mail->Port       = 25;

		//if email host is smtp.zoho.com,then smtpsecure is needed,and port changes to 587
        if( ($mailconf['host'] == 'smtp.zoho.com') || ($mailconf['host'] == 'smtp.office365.com')){
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;
        }

		$mail->CharSet = "utf-8";
		//Set who the message is to be sent from
		$mail->setFrom($mailconf['from'], $mailconf['from_name']);
		//Set an alternative reply-to address
		//$mail->addReplyTo('lyellow.net@qq.com', 'qbbt');
		//Set who the message is to be sent to
		$mail->addAddress($address,"");
		//Set the subject line
		$mail->Subject = $title;
		//Read an HTML message body from an external file, convert referenced images to embedded,
		//convert HTML into a basic plain-text alternative body
		
		$mail->msgHTML($content);		
		
		return $mail->send();
    }
    
    protected function getMailConf($api_key,$type){
            $conf = $this->getFromFile($api_key, $type);
            if(!$conf){
                $conf = $this->getFromDevicedata($api_key, $type);
            }
            return $conf;
    }
    
    protected function getFromDevicedata($api_key,$type){      
        $doc_id = "email:config:".strtolower($api_key).":".$type;
        
        $api_param = $this->create_api_param($api_key);
        $param = array("doc_id"=>$doc_id) + $api_param;
        
        $ret = $this->postApi("devicedata", "data_get", $param);
        error_log("sdcp_email [".date('Y-m-d H:i:s')."] devicedata ret:".json_encode($ret)."\n\n",3,"/home/email/log/sys.log");
        
        return $ret;
    }
    
     private function create_api_param($api_key) {
        $param =array();
         
        $config_file = "/home/email/conf/app/" . $api_key . ".ini";        
        if (file_exists($config_file)) {
           $application_config = parse_ini_file($config_file);  
            if ($application_config) {
                $time = time();
                $api_secret = $application_config['api_secret'];
                $api_token = sha1($api_secret . $time);
                
                $param ["time"] = $time;
                $param["api_key"] = $api_key;
                $param["api_token"] = $api_token;
            }
        }             
       
        return $param;
    }
  
     protected function getFromFile($api_key,$type){         
         $data = array();       
         
        $smtp_file = "/home/email/conf/smtp.config.ini";
        if (file_exists($smtp_file)) {
             $ret = parse_ini_file($smtp_file);

             if( isset($ret[$api_key."_".$type."_host"]) && ($ret[$api_key."_".$type."_host"]) && 
                isset($ret[$api_key."_".$type."_username"]) && ($ret[$api_key."_".$type."_username"]) &&
                isset($ret[$api_key."_".$type."_password"]) && ($ret[$api_key."_".$type."_password"]) &&
                isset($ret[$api_key."_".$type."_from"]) && ($ret[$api_key."_".$type."_from"]) && 
                isset($ret[$api_key."_".$type."_from_name"]) && ($ret[$api_key."_".$type."_from_name"]) ){
                 
                 $data["host"] = $ret[$api_key."_".$type."_host"];
                 $data["username"] = $ret[$api_key."_".$type."_username"];
                 $data["password"] = $ret[$api_key."_".$type."_password"];
                 $data["from"] = $ret[$api_key."_".$type."_from"];
                 $data["from_name"] = $ret[$api_key."_".$type."_from_name"];
             }    
             
        }
        
        error_log("sdcp_email [".date('Y-m-d H:i:s')."] file ret:".json_encode($data)."\n\n",3,"/home/email/log/sys.log");
        return $data;        
    }
}
