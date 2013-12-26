<?php 
class Transfer_model extends CI_Model {

    function __construct()
    {
        parent::__construct();
    }

    function display_routes(){

    	$query = $this->db->get('routes');
    	$data = $query->result();

    	return $data ;

    }

    function display_stops(){

    	$query = $this->db->get('stops');
    	$data = $query->result();

    	return $data;

    }

    public function insert_route($id,$stops){
		$data = array(
				'cu_routes_id' => $id,
				'cu_routes_stops' => $stops
			);
		$this->db->insert('cu_routes',$data);
		return 1 ;
	}

	public function insert_stop($id,$name,$lat,$long){
		$data = array(
				'cu_stops_id' => $id,
				'cu_stops_name' => $name,
				'cu_stops_lat' => $lat,
				'cu_stops_long' => $long
			);
		$this->db->insert('cu_stops',$data);
		return 1 ;
	}
}