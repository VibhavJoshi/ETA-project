<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Simadmin extends CI_Controller {

	
	public function __construct(){
		parent::__construct();
		$this->load->model('sim_model');
	}

	public function index()
	{
		$this->load->view('simadmin/input');
	}

	public function put_bus_location(){
		$id = $this->input->post('bus_id');
		$loc = $this->input->post('loc');
		$last_stop = $this->input->post('last_stop');
		$lat = substr($loc, 0,strpos($loc,','));
		$long = substr($loc, strpos($loc, ',')+1,strlen($loc)-strpos($loc, ',')-1);
		$this->sim_model->put_bus_loc($id,$lat,$long,$last_stop);

		echo "Data inserted<br>Bus ID: ".$id."<br>Latitude: ".$lat."<br>Longitude: ".$long."<br>Last stop:".$last_stop;

	}
}

/* End of file accounts.php */
/* Location: ./application/controllers/accounts.php */