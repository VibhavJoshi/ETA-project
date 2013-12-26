<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Transfer extends CI_Controller {

	
	public function __construct(){
		parent::__construct();

		$this->load->model('transfer_model');
	}

	public function index(){
		// print_r($this->transfer_model->display_routes());
		$array_of_obects = $this->transfer_model->display_routes();
		foreach($array_of_obects as $object_row){
			print_r($object_row->id);
			echo "\t";
			print_r($object_row->stops);
			echo "<br>";
		}
	}

	public function dick(){
		$array_of_obects = $this->transfer_model->display_routes();
		foreach($array_of_obects as $object_row){
			// $this->transfer_model->insert_route($object_row->id,$object_row->stops);

			print_r($object_row->id);
			echo "\t";
			print_r($object_row->stops);
			echo "<br>";
		}

		echo "string";
	}

	public function another(){
		$array_of_obects = $this->transfer_model->display_stops();
		foreach($array_of_obects as $object_row){
			// $this->transfer_model->insert_stop($object_row->id,$object_row->name,$object_row->latitude,$object_row->longitude);


		}

		echo "string";
	}
}

/* End of file transfer.php */
/* Location: ./application/controllers/transfer.php */