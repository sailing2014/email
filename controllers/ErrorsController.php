<?php
namespace App\Controllers;
class ErrorsController extends ControllerBase {

    public function show400Action() {
        echo $this->resData(array("code" => 400, "hd" => true));
    }

    public function show401Action() {
        echo $this->resData(array("code" => 401, "hd" => true));
    }

    public function show404Action() {
        echo $this->resData(array("code" => 404, "hd" => true));
    }

    public function show500Action() {
        echo $this->resData(array("code" => 500, "hd" => true));
    }
    
    
}
