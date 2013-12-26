<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Sim extends CI_Controller {

	
	public function __construct(){
		parent::__construct();
		$this->load->model('sim_model');
	}

	public function index()
	{
		$this->load->view('sim/input');
	}

	public function user_input(){

		$this->benchmark->mark('start');

		$bp = $this->input->post('bp');
		$dp = $this->input->post('dp');
		$routes_array = array(); // stores route IDs
		$buses = array(); // stores array of buses in each element with route ID as key
		$min_dist = array(); // stores min bus distance in elements with route ID as key
		$distance = array();
		$bp = str_replace(' ', '', $bp);
		while (strpos($bp, ',') != 0) {
			$bp = substr($bp, strpos($bp, ',')+1);
		}
		$dp = str_replace(' ', '', $dp);
		while (strpos($dp, ',') != 0) {
			$dp = substr($dp, strpos($dp, ',')+1);
		}
		$routes_array = $this->sim_model->get_routes_from_stops($bp,$dp);
		foreach ($routes_array as $route_id) {
			$bus_temp = $this->sim_model->get_buses_on_route($route_id);
			if (sizeof($bus_temp)) {
				$buses[$route_id] = $bus_temp;
				$stops_array = $this->sim_model->get_stops_on_route($route_id);
				foreach($buses[$route_id] as $bus){
					if(array_search($bus->cu_bus_last_stop, $stops_array) >= array_search($bp,$stops_array)){
						unset($buses[$route_id][array_search($bus, $buses[$route_id])]);
					}else{
						$distance[$route_id][$bus->cu_bus_id] = $this->get_stat($bus->cu_bus_id,$bp);
					}
				}
				if (array_key_exists($route_id, $distance)) {
					$distance[$route_id]['min'] = array("bus_id"=>array_search(min($distance[$route_id]),$distance[$route_id]),"distance"=>min($distance[$route_id]));
					$min_dist[$route_id] = min($distance[$route_id]);
				}
			
			}
			
		}
		asort($min_dist, SORT_NUMERIC);
		$min_dist = array_slice($min_dist, 0 , 4, TRUE);
		foreach ($min_dist as $s_route => $s_dist) {
			$s_bus_id = $distance[$s_route]['min']['bus_id'];
			$this->db->where('cu_bus_id',$s_bus_id);
			$s_bus[$s_route] = $this->db->get('cu_sim_bus')->result()[0];
			$dyn = $this->get_dyn($s_bus[$s_route]);
			$distance[$s_route][$s_bus[$s_route]->cu_bus_id] += $dyn;
			

		}
		// echo "Boarding point: ".$bp;
		// echo "<br>";
		// echo "Dropping point: ".$dp;
		// echo "<br>";
		// if (sizeof($routes_array)) {
		// 	echo "Total routes: ".sizeof($routes_array)."<br>";
		// }else{
		// 	echo "No routes found<br>";
		// }
		foreach ($routes_array as $route_id) {
			if (array_key_exists($route_id,$buses) && sizeof($buses[$route_id])) {
				// echo sizeof($buses[$route_id])." bus(es) on route ".$route_id;
				// echo "<br>";
			}
			
		}
		foreach ($routes_array as $route_id) {
			if (array_key_exists($route_id, $buses)) {
				
				foreach ($buses[$route_id] as $bus) {
					// echo "Bus ".$bus->cu_bus_id." on route ".$route_id." is ".$distance[$route_id][$bus->cu_bus_id]."m away<br>";
				}
			}
			
		}
		if (sizeof($distance)) {
			foreach ($distance as $route_id => $value) {
				$output[$route_id] = $value[$value['min']['bus_id']];
			}	
		}else{
			$output[0] = "No routes found";
		}

		$this->benchmark->mark('stop');
		// echo $this->benchmark->elapsed_time('start','stop')."s";
		
		// print_r($distance);
		// echo "<br>";
		// print_r($buses);
		// echo "<br>";
		// print_r($min_dist);
		header('Access-Control-Allow-Origin:*');
		header('Content-Type: application/json');
		echo json_encode($output);
	}

	public function user_input_b(){
		$bp = $this->input->post('bp');
		$dp = $this->input->post('dp');
		$routes_array = array();
		$buses = array();
		$buses_dist = array();
		$bp = str_replace(' ', '', $bp);
		while (strpos($bp, ',') != 0) {
			$bp = substr($bp, strpos($bp, ',')+1);
		}
		$dp = str_replace(' ', '', $dp);
		while (strpos($dp, ',') != 0) {
			$dp = substr($dp, strpos($dp, ',')+1);
		}

		$routes_array = $this->sim_model->get_routes_from_stops($bp,$dp);


		function cmp($a,$b){
			if ($a->bus_dist == $b->bus_dist) {
        		return 0;
    		}
    		return ($a->bus_dist < $b->bus_dist) ? -1 : 1;
		}
		foreach ($routes_array as $route_id) {
			$bus_temp = $this->sim_model->get_buses_on_route($route_id);
			$stops_array = $this->sim_model->get_stops_on_route($route_id);
			
			foreach ($bus_temp as $key => $bus ) {
				if (array_search($bus->cu_bus_last_stop, $stops_array) >= array_search($bp, $stops_array)) {
					unset($bus_temp[$key]);
				}else{
					$bus->bus_dist = $this->get_stat($bus->cu_bus_id,$bp);
				}
				
			}
			$buses = array_merge($buses,$bus_temp);
		}
		foreach ($buses as $bus) {
			$temp_one = array($bus->cu_bus_id,$bus->bus_dist);
			array_push($buses_dist, $temp_one);
		}
		
		usort($buses,"cmp");
		header('Content-Type: application/json');
		echo json_encode($buses);
	}

	public function random_speeds_time($x){
		$kms = floor($x/1000) ;
		$mts = $x-$kms*1000;

		$time = 0 ;
		$cs = 0 ;

		for($i=0;$i<=$kms;$i++){
			$cs = mt_rand(1,60);
			$cs = $cs*5/18 ;
			$time += 1000/$cs ;
		}

		$cs = mt_rand(1,60);
		$cs = $cs*5/18 ;
		$time += $mts/$cs ;
		echo floor($time);
		return floor($time);

	}

	public function congested_speeds_time($x){
		$kms = floor($x/1000) ;
		$mts = $x-$kms*1000;

		$time = 0 ;
		$cs = 0 ;
		for($i=0;$i<=$kms;$i++){
			$prob = mt_rand(1,100);
			if ($prob <= 54) {
				$cs = mt_rand(1,10);
			}else{
				$cs = mt_rand(1,60);
			}
			$cs = $cs*5/18 ;
			$time += 1000/$cs ;
		}
		

		$prob = mt_rand(1,100);
		if ($prob <= 54) {
			$cs = mt_rand(1,10);
		}else{
			$cs = mt_rand(1,60);
		}
		$cs = $cs*5/18 ;
		$time += 1000/$cs ;
		echo floor($time);
		return floor($time);

	}

	public function slow_speeds_time($x){
		$kms = floor($x/1000) ;
		$mts = $x-$kms*1000;

		$time = 0 ;
		$cs = 0 ;
		for($i=0;$i<=$kms;$i++){
			$prob = mt_rand(1,10);
			if ($prob <= 4) {
				$cs = mt_rand(11,30);
			}else{
				$cs = mt_rand(1,60);
			}
			$cs = $cs*5/18 ;
			$time += 1000/$cs ;
		}
		

		$prob = mt_rand(1,10);
		if ($prob <= 4) {
			$cs = mt_rand(11,30);
		}else{
			$cs = mt_rand(1,60);
		}
		$cs = $cs*5/18 ;
		$time += 1000/$cs ;
		echo floor($time);
		return floor($time);
	}

	public function free_flow_speeds_time($x){
		$kms = floor($x/1000) ;
		$mts = $x-$kms*1000;

		$time = 0 ;
		$cs = 0 ;
		for($i=0;$i<=$kms;$i++){
			$prob = mt_rand(1,10);
			if ($prob <= 4) {
				$cs = mt_rand(41,60);
			}else{
				$cs = mt_rand(1,60);
			}
			$cs = $cs*5/18 ;
			$time += 1000/$cs ;
		}
		

		$prob = mt_rand(1,10);
		if ($prob <= 4) {
			$cs = mt_rand(41,60);
		}else{
			$cs = mt_rand(1,60);
		}
		$cs = $cs*5/18 ;
		$time += 1000/$cs ;
		echo floor($time);
		return floor($time);
	}

	
	function stops_array_bp(){
		$term = $this->input->get('term');
        $data = array();
        $rows = $this->sim_model->get_data($term);
            foreach( $rows as $row ){
                $data[] = array(
                    'label' => $row->cu_stops_name.', '. $row->cu_stops_id,
                    'value' => $row->cu_stops_name.' ,'. $row->cu_stops_id);
            }
        echo json_encode($data);
        flush();
	}

	function get_stat($bus_id,$bp){
		return $this->sim_model->get_stat_dist_by_bus($bus_id,$bp);
	}

	function get_dyn($bus){
		$this->benchmark->mark('start');
// get bus location
		$bus_lat = $bus->cu_bus_lat ;
		$bus_long = $bus->cu_bus_long ;
// get next stop location
		$stops = $this->sim_model->get_stops_on_route($bus->cu_bus_route);
		$last_stop_key = array_search($bus->cu_bus_last_stop, $stops);
		$next_stop['id'] = $stops[$last_stop_key+1];
		$this->db->where('cu_stops_id',$next_stop['id']);
		$next_stop['details'] = $this->db->get('cu_stops')->result()[0];
// store both locations
		$bus_loc = $bus->cu_bus_lat.','.$bus->cu_bus_long;
		$n_s = $next_stop['details']->cu_stops_lat.','.$next_stop['details']->cu_stops_long;
// get distance
		$this->benchmark->mark('g');
		$url = "http://maps.googleapis.com/maps/api/directions/json?origin=".$bus_loc."&destination=".$n_s."&mode=driving&language=en-EN&sensor=false" ;
		$cURL = curl_init();
		curl_setopt($cURL, CURLOPT_URL, $url);
		curl_setopt($cURL, CURLOPT_HTTPGET, true);
		curl_setopt($cURL,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($cURL, CURLOPT_HTTPHEADER, array(
		    'Content-Type: application/json',
		    'Accept: application/json'
		));
		$result = json_decode(curl_exec($cURL));
		curl_close($cURL);
		$distance = $result->routes[0]->legs[0]->distance->value;
		$this->benchmark->mark('stop');
		// echo "Time taken locally: ".$this->benchmark->elapsed_time('start','g')."<br>";
		// echo "Time taken by google: ".$this->benchmark->elapsed_time('g','stop')."<br>";
// return distance
		return $distance;
	}
}

/* End of file sim.php */
/* Location: ./application/controllers/sim.php */