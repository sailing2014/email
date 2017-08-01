<?php

namespace App\Helper;

final class HttpResponse extends \Phalcon\Http\Response {

    private $di = null;

    function __construct($di) {
        parent::__construct();
        $this->di = $di;
    }

    /**
     * 输出json返回值
     * @param mixed $response 返回的信息。
     * 如果是数组，可以有三个字段. msg:用于返回的信息，code:用于返回的状态码，hd:是否更改http头的code与message
     * 如果是字符或数字，从配置文件读出的指定code的返回信息
     * @param mixed $data
     * @return string
     */
    function res($response, $data = array()) {
        if (is_array($response)) {
            if (!isset($response["msg"])) {
                $msg = $this->getConfig("code.config", "code_msg");
                $message = $msg[$response["code"]];
            } else {
                $message = $response["msg"];
            }
            $code = $response["code"];
            if ($response["hd"] == true) {
                $this->di->getShared("response")->setStatusCode($code, $message);
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
        return urldecode(json_encode($this->arrayRecursive($var_array, "urlencode", true)));
    }

    protected function arrayRecursive($array, $function, $apply_to_keys_also = false) {
        static $recursive_counter = 0;
        if (++$recursive_counter > 1000) {
            die('possible deep recursion attack');
        }
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = $this->arrayRecursive($array[$key], $function, $apply_to_keys_also);
            } else {
                $array[$key] = $function($value);
            }

            if ($apply_to_keys_also && is_string($key)) {
                $new_key = $function($key);
                if ($new_key != $key) {
                    $array[$new_key] = $array[$key];
                    unset($array[$key]);
                }
            }
        }
        $recursive_counter--;
        return $array;
    }

    /**
     * 取配置文件
     * @param string $name 配置名
     * @param string $group 组名
     * @return type
     */
    protected function getConfig($name, $group) {
        try {
            $config = $this->di->getShared($name);
        } catch (Phalcon\Exception $e) {
            die($e->getMessage());
        }
        return $config->$group;
    }

}
