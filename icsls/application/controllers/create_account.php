<?php

class Create_account extends CI_Controller{
	public function Create_account(){
		parent::__construct();
		$this->load->library('javascript');
		$this->load->helper('url');
		$this->load->model('user_model');
		$this->load->library('encrypt');
	}

	public function index(){
		$data['title'] = "Home - ICS Library System";
		$data['header'] = "Create Account";
		$this->load->view('create_account_view', $data);
	}

	/*	Description: Calls the insert_account function in the user_model if the student 
		number/employee number/username of the account to be created does not exist	*/
	public function account_created(){
		$data['title'] = "Home - ICS Library System";
		$data["error_message"] = "";
		//if User is a student
		if($_POST['usertype'] == 'S'){
			$studnum = $_POST['student_number'];
			$uname = $_POST['username'];

			$studentQuery = $this->user_model->student_exists($studnum);
			$usernameQuery = $this->user_model->username_exists($uname);

			if($studentQuery == true || $usernameQuery == true){
				$this->load->view('create_account_view');
					echo "Account already exists.";
			}else {	
				$this->load->model('user_model');
				$this->user_model->insert_account('users', $_POST);
				
				echo "Account succesfully created.";
				$this->load->view('home_view', $data);
			}
		}
		//if User is a faculty
		else if($_POST['usertype'] == 'F'){
			$enum = $_POST['employee_number'];
			$uname = $_POST['username'];

			$facultyQuery = $this->user_model->faculty_exists($enum);
			$usernameQuery = $this->user_model->username_exists($uname);

			if($facultyQuery == true || $usernameQuery == true){
				$this->load->view('create_account_view');
				echo "Account already exists.";
			}else {
				$this->load->model('user_model');
				$this->user_model->insert_account('users', $_POST);
				
				echo "Account succesfully created.";
				$this->load->view('home_view', $data);
			}
		}
		
	}
}

?>