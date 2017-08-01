<?php

namespace App\Controllers;

class ConfigController extends ControllerBase {

    public $api_key = '';   
    public $api_token = "";
    public $time = 0;
    public $comm = array();
     public $http_request = null;
    public $http_response = null;   
    
    public function beforeExecuteRoute() {
        $this->http_request = $this->getDI()->getShared("http.request");
        $this->http_response = $this->getDI()->getShared("http.response");
        $this->api_key = $this->http_request->getParam("api_key");         
        $this->api_token = $this->http_request->getParam("api_token");  
        $this->time = $this->http_request->getParam("time");  
        $this->comm = array("api_key"=>  $this->api_key,"api_token"=>  $this->api_token,"time"=>  $this->time);
    }
    
    public function indexAction(){
        echo json_encode(array("email internal config"));
    }   
    
    public function addAction(){
        $host=$this->http_request->getParam('host');   
        $username=$this->http_request->getParam('username');       
        $password= $this->http_request->getParam('password');
        $from = $this->http_request->getParam('from');
        $from_name = $this->http_request->getParam('from_name');       
        $type = $this->http_request->getParam('type');
        if(!$type){ 
            $type = 1;
        }
        if(!$host || !$username || !$password ||!$from || !$from_name)
        {
            echo $this->http_response->res(400);
            return;
        }               
        
        $data = array("host"=>$host,"username"=>$username,"password"=>$password,
                        "from"=>$from,"from_name"=>$from_name,"type"=>$type
                      );
        $doc_id = "email:config:".strtolower($this->api_key).":".$type;
        
        $param = array("doc_id"=>$doc_id,"data"=>$data) + $this->comm;
        $ret = $this->postApi("devicedata", "data_set", $param);
        
         if($ret){
            echo $this->http_response->res(200);
        }else{
            echo $this->http_response->res(10211);
        }
    }
    
    public function getAction(){
        $type = $this->http_request->getParam("type");
        if(! $type){
            $type = 1;
        }
        
        $doc_id = "email:config:".strtolower($this->api_key).":".$type;
        $param = array("doc_id"=>$doc_id)+ $this->comm;
        $ret = $this->postApi("devicedata", "data_get", $param);
        if($ret){
            echo $this->http_response->res(200, array("data"=>$ret));
        }else{
            echo $this->http_response->res(10213);
        }
    }
    
    public function deleteAction(){
        $type = $this->http_request->getParam("type");
        if(! isset($type) || empty($type)){
            $type = 1;
        }
        
        $doc_id = "email:config:".strtolower($this->api_key).":".$type;
        $param = array("doc_id"=>$doc_id) + $this->comm;
        $ret = $this->postApi("devicedata", "data_delete", $param);
        if($ret){
            echo $this->http_response->res(200);
        }else{
            echo $this->http_response->res(10214);
        }
    }
    
  
       
}