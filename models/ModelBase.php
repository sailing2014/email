<?php

namespace App\Models;

use Phalcon\Config\Adapter\Ini as ConfigIni;

/**
 * @author wws
 */
class ModelBase extends \Phalcon\DI implements \Phalcon\DiInterface {

    
    public function __construct() {
        parent::__construct();
        $this->setConfigDi("api.config");
        $this->setShared("security", function (){return new \Phalcon\Security();});
    }

     /**
     * 提交对外部网络的API的访问请求
     * @param string $service 服务名称
     * @param string $api_name API名称
     * @param array $data 发送的数据
     * @return array 接受的数据
     */
    protected function postApi($service, $api_name, $data) {
        $ret = true;
        $api_config = $this->getConfig("api.config", $service);
        $api_uri = $api_config[$api_name];
        try {
            $response = \Httpful\Request::post($api_uri, $data, 'form')->timeoutIn(10)->expectsJson()->send()->body;
        } catch (\Httpful\Exception $e) {
            die($e->getMessage());
        }     
    
        if($response->_status->_code == 200){
            if(isset($response->data) && !empty($response->data)){
                $ret =  json_decode(json_encode($response->data),TRUE);
            }
        }else{
            $ret = false;
        }
        
        return $ret;        
    }

    protected function setConfigDi($di_name) {
        $this->setShared($di_name, function() use($di_name) {
            return new ConfigIni("/home/email/conf/" . $di_name . ".ini");
        });
    }

    /**
     * 取配置文件
     * @param string $name 配置名
     * @param string $group 组名
     * @return type
     */
    protected function getConfig($name, $group) {
        try {
            $config = $this->getShared($name);
        } catch (Phalcon\Exception $e) {
            die($e->getMessage());
        }
        return $config->$group;
    }
    
    public function validate_application($api_key,$api_token,$time)
    {
        
        if(!$api_key)
        {
            return false;
        }
        $api_key_arr = explode(":", $api_key,1);
         
        $application_name = $api_key_arr[0];
        
        $application_config = $this->getConfig("application.config", $application_name);
      
        if(!$application_config)
        {
            return false;
        }
        $api_secret = $application_config['api_secret'];
        if($api_token != sha1($api_secret.$time))
        {
            return false;
        }
        return true;
        
    }

}
