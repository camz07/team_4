<?php
/**
* Index function of Profile
* 
* @Package	icsls
* @Author	Raquel Abigail Bunag
*/
class Login extends CI_Controller{
	/**
	* Login function 
	* 
	* @access	public
	* @param	none
	* @return	none
	*/
	public function Login(){
		parent::__construct();
		$this->load->model('user_model');
		$this->load->library('session');
	}

	/**
	* Index function of Login
	* 
	* @access	public
	* @param	none
	* @return	none
	*/
	public function index(){ 
		// title
		$data["title"] = "Login - ICS Library System"; 
		//error message
		$data["error_message"] = ""; 
		//load login view
		$this->load->view("login_view", $data); 
	}
	
	/**
	* User able to login function 
	* 
	* @access	public
	* @param	none
	* @return	none
	*/
	public function login_user(){ 
		//username from form
		$username = $_POST["username"]; 
		//password from form
		$password = md5($_POST["password"]); 
		
		// if the user exist
		if($this->user_model->user_exists($username, $password)){ 
			//get user info query
			$query=$this->user_model->get_user_data($username, $password); 
			// read results
			foreach($query as $row){ 
				$id = $row->id;
				$user_type = $row->user_type;
				break;
			}
			
			//assign data to session
			$sessionData = array('loggedIn' => true, 'id' => $id, 'user_type' => $user_type, 'username' => $username); 
			$this->session->set_userdata($sessionData); 
			
			//title
			$data["title"] = "Login - ICS Library System"; 
			//load login view 
			$this->load->view("login_view", $data); 
		}
		//if user doesn't exist
		else{ 
			//title
			$data["title"] = "ICS Library System"; 
			//error message
			$data["error_message"] = "Error"; 
			//load view
			$this->load->view("login_view", $data); 
		}
		
	}
}
?>