<?php

namespace App\Plugins;

use Phalcon\Events\Event;
use Phalcon\Mvc\User\Plugin;
use Phalcon\Mvc\Dispatcher;

/**
 * SecurityPlugin
 *
 * This is the security plugin which controls that users only have access to the modules they're assigned to
 */
class SecurityPlugin extends Plugin {

    public function beforeDispatch(Event $event, Dispatcher $dispatcher) {

        $controller = $dispatcher->getControllerName();        
        if ($controller !== "errors") {
            $http_request = $this->getDI()->getShared("http.request");
            $time = $http_request->getParam("time");

            if ($controller == "appconf") {                
                $service_key = $http_request->getParam("service_key");              
                $service_token = $http_request->getParam("service_token");
                if (!$this->service_validate($service_key, $service_token, $time)) {                   
                    $dispatcher->forward(array(
                        'controller' => 'errors',
                        'action' => 'show401'
                    ));
                    return false;
                }
            } else {

                $api_key = $http_request->getParam("api_key");
                $api_token = $http_request->getParam("api_token");               
                if (!$this->app_validate($api_key, $api_token, $time)) {
                    $dispatcher->forward(array(
                        'controller' => 'errors',
                        'action' => 'show401'
                    ));
                    return false;
                }
            }
        }
    }

    private function app_validate($api_key, $api_token, $time) {
        if (!$api_key) {
            return false;
        }        
        $config_file = CONF_PATH . "app/" . $api_key . ".ini";        
        if (!file_exists($config_file)) {
            $config_file = $this->get_app_config($api_key);
            if(!$config_file){
                return false;
            }
        }
        $application_config = parse_ini_file($config_file);        
        if (!$application_config) {
            return false;
        }
        $api_secret = $application_config['api_secret'];        
//        echo sha1($api_secret . $time) ; exit();
        if ($api_token != sha1($api_secret . $time)) {
            return false;
        }
        return true;
    }

    private function service_validate($service_key, $service_token, $time) {     
       
        if ($service_key != SERVICE_KEY) {
            return false;
        }  
        
        if ($service_token != sha1(SERVICE_SECRET . $time)) {
            return false;
        }      
        
        return true;
    }
    
    private function get_app_config($api_key){    
        
        $service_ret = $this->post_application_service($api_key);
        
        if(!$service_ret) {
            return false;            
        }
        
        $file_name = $service_ret->file_name;
        $content = $service_ret->content;
       
        $filename = CONF_PATH.'app/'.$file_name.".ini";        
        $ret = file_put_contents($filename, $content);      
       
        if($ret) {
            chgrp($filename, "program");
            return $filename;
        }else
        {
            return false;
        }        
    }
    
    private function post_application_service($api_key){
                  
//        $api_uri = "http://192.168.8.110:60020/v1/application/manage/getappconfig";
        $app_config_file = CONF_PATH . "api.config.ini";        
        $api_config = parse_ini_file($app_config_file);       
        $api_uri = $api_config["get_app_config"];      
        $time = time();
        $service_token = sha1(SERVICE_SECRET . $time);
        $param = array("api_key" => $api_key, "service_key" => SERVICE_KEY, "service_token" => $service_token, 'time' => $time);        
        $response = json_decode(\Httpful\Request::post($api_uri, $param, 'json')->expectsType('json')->timeoutIn(3)->send());        
        if(isset($response) && $response->_status->_code == 200)
        {
            return $response;
        }else{
            return false;
        }
        
    }
    

}
