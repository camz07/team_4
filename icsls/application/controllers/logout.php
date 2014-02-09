<?php

class Logout extends CI_Controller{
	public function Logout(){
		parent::__construct();
	}

	public function index(){
		$this->session->sess_destroy();	//destroys all session variables

		$data["title"] = "Home - ICS Library System";
		redirect(base_url()."index.php/login"); //redirects to login page 
	}
}

?>