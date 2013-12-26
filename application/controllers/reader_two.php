<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Reader_two extends CI_Controller {

	
	// public function __construct(){
	// 	parent::__construct();
	// 	$this->load->model('interms_model');
	// }

	public function get_dist_a($row){
		$loc = $this->interm_locs($row);
		$distance = 0 ;
		// $distance = $this->interm_dist($loc['start'],$loc['end']);
		$this->interms_model->insert_dist($row,$distance);
		echo "abc";
		echo "Data inserted-> Start: ".$loc_start['lat'].",".$loc_start['long']." , End: ".$loc_end['lat'].",".$loc_end['long']." , Distance: ".$distance."<br>";
		
	}
}

/* End of file accounts.php */
/* Location: ./application/controllers/accounts.php */