<?php

class Home extends CI_Controller{
	public function Home(){
		parent::__construct();
	}

	public function index(){
		$data['title'] = "Login - ICS Library System";
		$data["error_message"] = "";
		$this->load->view("home_view", $data);
	}
}

?>