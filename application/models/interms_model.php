<?php

class Interms_model extends CI_Model {

    function __construct()
    {
        parent::__construct();
    }


    function insert_dist($row,$dist){
    	$data = array(
    			'distance'=>$dist
    		);
    	$this->db->update('cu_interms',$data, 'id = '.$row);
    }
}