<?php

class Login extends CI_Controller{
	public function Login(){
		parent::__construct();
		$this->load->model('user_model');
		$this->load->library('session');
	}

	public function index(){
		$data["title"] = "Login - ICS Library System";
		$data["error_message"] = "";
		$this->load->view("home_view", $data);
	}
	/* Description: Calls the user_exists function in the user_model to check if the User
	   exists in the database. If yes, the User will be redirected to search_view. Else, 
	   an error message will be displayed. */
	public function login_user(){
		$data["header"] = "Search";
		$username = $_POST["username"]; //username from form
		$password = md5($_POST["password"]); //password from form
		
		if($this->user_model->user_exists($username, $password)){ // if the user exist
			$query=$this->user_model->get_user_data($username, $password); //get user info query
			foreach($query as $row){ // read results
				$id = $row->id;
				$user_type = $row->user_type;
				break;
			}
			
			$sessionData = array('loggedIn' => true, 'id' => $id, 'user_type' => $user_type, 'username' => $username); //assign data to session
			$this->session->set_userdata($sessionData); 
			
			$data["title"] = "Home - ICS Library System";
			$this->load->view("search_view", $data);
		}else{ //if user doesn't exist
			$data["title"] = "ICS Library System";
			$data["error_message"] = "Account does not exist. Please check your username/password.";
			$this->load->view("home_view", $data);
		}
		
	}
}
?>