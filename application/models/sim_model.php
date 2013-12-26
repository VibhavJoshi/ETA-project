<?php 
class Sim_model extends CI_Model {

    function __construct()
    {
        parent::__construct();
    }

    function stops(){
    	$this->db->select('cu_stops_name');
    	$query = $this->db->get('cu_stops');
    	$data = $query->result();

    	return $data;

    }

    function get_bus_loc(){
    	$this->db->select_max('cu_bus_location_id');
    	$query = $this->db->get('cu_sim_bus_locations');
    	$max_id = $query->result()[0]->cu_bus_location_id ;
    	$this->db->where('cu_bus_location_id',$max_id);
    	$row = $this->db->get('cu_sim_bus_locations');
    	$lat = $row->result()[0]->cu_bus_location_lat;
    	$long = $row->result()[0]->cu_bus_location_long;
    	return $lat.",".$long;
    }



    function put_bus_loc($id,$lat,$long,$last_stop){
    	$data = array(
    			'cu_bus_lat'=>floatval($lat),
    			'cu_bus_long'=>floatval($long),
                'cu_bus_last_stop' => $last_stop
    		);
        $this->db->where('cu_bus_id',$id);
    	$this->db->update('cu_sim_bus',$data);
    	
    	return 1;
    }

    function get_data($term)
    {
        $this->db->select('cu_stops_id, cu_stops_name');
        $this->db->like('cu_stops_name',$term);
        $this->db->limit(10);
        $sql = $this->db->get('cu_stops');

        return $sql->result();
    }

    function get_routes_from_stops($bp,$dp){
        $this->db->select('cu_routes_id');
        $this->db->like('cu_routes_stops',','.$bp.',');
        $this->db->like('cu_routes_stops',','.$dp.',');
        $routes = $this->db->get('cu_routes')->result();
        $routes_array= array();
        foreach ($routes as $row) {
            array_push($routes_array, $row->cu_routes_id);
        }
        return $routes_array;
    }

    function get_stops_on_route($route_id){
        $this->db->select('cu_routes_stops');
        $this->db->where('cu_routes_id',$route_id);
        $stops = explode(',', $this->db->get('cu_routes')->result()[0]->cu_routes_stops);
        $stops = array_slice($stops, 1, -1);

        return $stops;
    }

    function get_buses_on_route($route){
        // $this->db->select('cu_bus_id,cu_bus_lat,cu_bus_long,cu_bus_last_stop');
        $this->db->where('cu_bus_route',$route);
        $query = $this->db->get('cu_sim_bus')->result();

        return $query;
    }

    function get_stat_dist_by_bus($bus_id,$bp){
        $this->db->select('cu_bus_route,cu_bus_last_stop');
        $this->db->where('cu_bus_id',$bus_id);
        $bus = $this->db->get('cu_sim_bus')->result()[0];
        $route_id = $bus->cu_bus_route;
        $last_stop = $bus->cu_bus_last_stop;

        $stops = $this->get_stops_on_route($route_id);
        $last_stop_key = array_search($last_stop, $stops);
        $bp_key = array_search($bp, $stops);
        $stat_dist = 0;
        for ($i=1; $i < $bp_key-$last_stop_key; $i++) { 
            $this->db->select('distance');
            $this->db->where(array('start'=>$stops[$last_stop_key+$i],'end'=>$stops[$last_stop_key+$i+1]));
            $query = $this->db->get('cu_interms')->result()[0]->distance;
            $stat_dist += $query ;
        }
        return $stat_dist;
    }



}