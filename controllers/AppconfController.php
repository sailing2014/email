<?php

namespace App\Controllers;

class AppconfController extends ControllerBase {

    public $http_request = null;

    public function beforeExecuteRoute() {
        $this->http_request = $this->getDI()->getShared("http.request");
        $this->http_response = $this->getDI()->getShared("http.response");
    }
    
    public function setAction()
    {
        $file_name = $this->http_request->getParam("file_name");
        $content = $this->http_request->getParam("content");
        if(!$file_name || !$content)
        {
            echo $this->resData(array("code" => 400, "hd" => true));
            return;
        }
        $filename = CONF_PATH.'app/'.$file_name.".ini";        
        $ret = file_put_contents($filename, $content);       
        if($ret) {
            echo $this->http_response->res(200);
        }else
        {
            echo $this->http_response->res(500);
        }
        return;
    }
}
