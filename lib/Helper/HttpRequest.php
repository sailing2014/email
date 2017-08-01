<?php

namespace App\Helper;

final class HttpRequest extends \Phalcon\Http\Request {

    private $response_array;

    function __construct() {
        $this->response_array = $this->requestJson();
    }

    function requestJson() {
        if (strpos(strtolower($this->getHeader("CONTENT_TYPE")), "json")) {
            $this->response_array = json_decode($this->getRawBody(), true);
        } else {
            $this->response_array = $this->getPost();
        }
        return $this;
    }

    function getParam($param_name, $default = '') {
        if (isset($this->response_array[$param_name])) {
            return $this->response_array[$param_name];
        } else {
            return $default;
        }
    }

}
