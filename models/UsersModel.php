<?php
namespace App\Models;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of UsersModel
 *
 * @author Administrator
 */
class UsersModel extends ModelBase {

    private $couchbase = null;
    private $caches = null;

    public function __construct() {
        parent::__construct();
        $this->couchbase = $this->getShared('db');
        $this->caches = $this->getShared('caches');
    }

    public function get_default() {
        return "adda";
    }

    //put your code here
    public function get_data() {
        //Cache arbitrary data
        $this->caches->set('my-data', array(1, 2, 3, 4, 5));
        //Get data
        var_dump($this->caches->get('my-data'));
        return $this->couchbase->get("raw_data:123535:10398:1:1:1413957971");
    }

}
