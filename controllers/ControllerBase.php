<?php

namespace App\Controllers;

use Phalcon\Mvc\Controller;

class ControllerBase extends Controller {

    protected function checkMethod($method) {
        if ($this->request->getMethod() !== strtoupper($method)) {
            $this->dispatcher->forward(array(
                'controller' => 'errors',
                'action' => 'show400'
            ));
            return false;
        } else {
            return true;
        }
    }

    /**
     * 输出json返回值
     * @param mixed $response 返回的信息。
     * 如果是数组，可以有三个字段. msg:用于返回的信息，code:用于返回的状态码，hd:是否更改http头的code与message
     * 如果是字符或数字，从配置文件读出的指定code的返回信息
     * @param mixed $data
     * @return type
     */
    protected function resData($response, $data = array()) {
        if (is_array($response)) {
            if (!isset($response["msg"])) {
                $msg = $this->getConfig("code.config", "code_msg");
                $message = $msg[$response["code"]];
            } else {
                $message = $response["msg"];
            }
            $code = $response["code"];
            if ($response["hd"] == true) {
                $this->response->setStatusCode($code, $message);
            }
        } else {
            $msg = $this->getConfig("code.config", "code_msg");
            $message = $msg[$response];
            $code = $response;
        }
        $var_array = array("_status" => array("_code" => $code, "_message" => $message));
        if (!empty($data)) {

            $var_array = $var_array + (is_array($data) ? $data : array("data" => $data));
        }
        return json_encode($var_array);
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

    /**
     * 取配置文件
     * @param string $name 配置名
     * @param string $group 组名
     * @return type
     */
    protected function getConfig($name, $group) {
        try {            
            $config = $this->getDI()->getShared($name);            
        } catch (Phalcon\Exception $e) {
            die($e->getMessage());
        }
        return $config->$group;
    }

}
