<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Reader extends CI_Controller {


	public function __construct(){
		parent::__construct();
		$this->load->model('interms_model');
	}

	public function exploder(){
		$id = 1 ;
		$this->benchmark->mark('start');
		for($id=1;$id<2034;$id++){
			$this->db->where('cu_routes_id',$id);
	    	$query = $this->db->get('cu_routes');
	    	$data = $query->result();
	    	print_r(explode(",", $data[0]->cu_routes_stops));
	    	echo "<br>";
		}
		$this->benchmark->mark('stop');

		echo "Time: ".$this->benchmark->elapsed_time('start','stop')."s";
		
	}

	public function get_dist(){
		$time= 0;
		$g_time = 0;
		for ($row=1; $row<2500 ; $row+1) { 
			$this->benchmark->mark('start');
			$this->db->where('id',$row);
			$row_data = $this->db->get('cu_interms')->result();
			$point['start']=$row_data[0]->start;
			$point['end']=$row_data[0]->end;

			$this->db->select('cu_stops_lat, cu_stops_long');
			$this->db->where('cu_stops_id',$point['start']);
			$get = $this->db->get('cu_stops');
			$loc_start = $get->result()[0]->cu_stops_lat.",".$get->result()[0]->cu_stops_long;

			$this->db->select('cu_stops_lat, cu_stops_long');
			$this->db->where('cu_stops_id',$point['end']);
			$get = $this->db->get('cu_stops');
			$loc_end = $get->result()[0]->cu_stops_lat.",".$get->result()[0]->cu_stops_long;
			$this->benchmark->mark('g_start');
			$url = "http://maps.googleapis.com/maps/api/directions/json?origin=".$loc_start."&destination=".$loc_end."&mode=driving&language=en-EN&sensor=false" ;

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
			$this->benchmark->mark('g_stop');

			$res_json = json_encode($result);
				$response_data = array(
						'response'=> $res_json
					);
				$this->db->insert('cu_google_responses',$response_data);
			$empty_response = 0 ;
			$this->benchmark->mark('stop');
			$time += $this->benchmark->elapsed_time('start','g_start')+$this->benchmark->elapsed_time('g_stop','stop');
			$g_time += $this->benchmark->elapsed_time('g_start','g_stop');
			if (!empty($result->routes)){
				$distance = $result->routes[0]->legs[0]->distance->value;
				$insert_dist = array("distance"=>$distance);
				$this->db->update('cu_interms',$insert_dist,"id = ".$row);
				echo "Start: ".$loc_start." End: ".$loc_end." Distance: ".$distance."<br>";
				$empty_response = 0 ;
			}else{
				$empty_response++;
				echo "Empty response: ".$empty_response."<br>";
				if ($empty_response=10) {
					echo "Empty response<br>";
					echo "Time taken for google request: ".$g_time."s<br>";
					echo "Time taken for local queries: ".$time."s<br>";
					exit();
				}
			}
		}
		
		echo "Time taken for google request: ".$g_time."s<br>";
		echo "Time taken for local queries: ".$time."s<br>";

	}

	public function get_dist_a(){
		$empty_response = 0 ;
		for ($row=1; $row<9810 ; $row++) {
			$loc = $this->interm_locs($row);
			if ($loc['distt']==0) {
				$distance = $this->interm_dist($loc['start'],$loc['end']);
				if ($distance != "no_response") {
					$this->interms_model->insert_dist($row,$distance);
					echo "Data inserted-> Row: ".$row.", Start: ".$loc['start']['lat'].",".$loc['start']['long']." , End: ".$loc['end']['lat'].",".$loc['end']['long']." , Distance: ".$distance."<br>";
					$empty_response = 0 ;
				}else{
					$empty_response++;
					echo "Empty response: ".$empty_response."<br>";
					if ($empty_response==3) {
						echo "Empty response<br>";
						if ($empty_response==10) {
							exit();
						}
						
					}
				}
			}else{}
		}
		
		
	}

	public function lat_long(){
		for ($i=1; $i <=5938 ; $i++) { 
			$this->db->where('cu_stops_id',$i);
			$this->db->select('cu_stops_lat, cu_stops_long');
			$query = $this->db->get('cu_stops')->result()[0];
			echo $query->cu_stops_lat.','.$query->cu_stops_long.'<br>';
		}
	}

	public function interm_locs($row){
		
		$time = 0;
		$this->db->where('id',$row);
		$row_data = $this->db->get('cu_interms')->result();
		$point['start']=$row_data[0]->start;
		$point['end']=$row_data[0]->end;
		$loc['distt']=$row_data[0]->distance;
		$this->db->select('cu_stops_lat, cu_stops_long');
		$this->db->where('cu_stops_id',$point['start']);
		$get = $this->db->get('cu_stops');

		$loc['start']= array(
				'lat'=>$get->result()[0]->cu_stops_lat,
				'long'=>$get->result()[0]->cu_stops_long
			);

		$this->db->select('cu_stops_lat, cu_stops_long');
		$this->db->where('cu_stops_id',$point['end']);
		$get = $this->db->get('cu_stops');
		$loc['end']= array(
				'lat'=>$get->result()[0]->cu_stops_lat,
				'long'=>$get->result()[0]->cu_stops_long
			);
		return $loc;
	}

	public function interm_dist($loc_start,$loc_end){


		$url = "http://maps.googleapis.com/maps/api/directions/json?origin=".$loc_start['lat'].",".$loc_start['long']."&destination=".$loc_end['lat'].",".$loc_end['long']."&mode=driving&language=en-EN&sensor=false" ;
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
		echo "<br>";
		if (!empty($result->routes)) {
			$distance = $result->routes[0]->legs[0]->distance->value;
			return $distance;
		}else{
			print_r($result);
			return "no_response";
		}
	}

	public function interms_list(){
		$time= 0;
		$g_time = 0;
		for ($row=1; $row<9810 ; $row++) { 
			// $this->benchmark->mark('start');
			$this->db->where('id',$row);
			$row_data = $this->db->get('cu_interms')->result();
			$point['start']=$row_data[0]->start;
			$point['end']=$row_data[0]->end;

			$this->db->select('cu_stops_lat, cu_stops_long');
			$this->db->where('cu_stops_id',$point['start']);
			$get = $this->db->get('cu_stops');
			$loc_start = $get->result()[0]->cu_stops_lat.",".$get->result()[0]->cu_stops_long;

			$this->db->select('cu_stops_lat, cu_stops_long');
			$this->db->where('cu_stops_id',$point['end']);
			$get = $this->db->get('cu_stops');
			$loc_end = $get->result()[0]->cu_stops_lat.",".$get->result()[0]->cu_stops_long;
			// $time += $this->benchmark->elapsed_time('start','g_start')+$this->benchmark->elapsed_time('g_stop','stop');
			echo "Start: ".$loc_start." End: ".$loc_end."<br>";
		}
		// echo "Time taken for local queries: ".$time."s<br>";

	}

	public function empty_dist(){
		$this->db->select('id');
		$this->db->where(array('distance !='=> 0));
		$query = $this->db->get('cu_interms');
		$num = $query->num_rows();
		$page = '<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="refresh" content="3">
	<title></title>
</head>
<body>'.$num.'</body>
</html>';
	echo $page;
		// print_r($query->result());
	}

}

/* End of file reader.php */
/* Location: ./application/controllers/reader.php */