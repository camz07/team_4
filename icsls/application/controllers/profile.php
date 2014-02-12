<?php
	/**
	* Profile
	* 
	* @Package	icsls
	* @Author	Raquel Abigail Bunag
	*/
	class Profile extends CI_Controller{
		/**
		* Profile function
		* 
		* @access	public
		* @param	none
		* @return	none
		*/
		public function Profile(){
			parent::__construct();
			//load model
			$this->load->model('user_model');
			//load library
			$this->load->library('session');
			//load helper
			$this->load->helper('url');
			$this->load->helper('form');
		}
		/**
		* Index function of Profile
		* 
		* @access	public
		* @param	none
		* @return	none
		*/
		public function index(){ 
			//title
			$data["title"] = "Profile - ICS Library System";
			
			// get username from session
			$username = $this->session->userdata('username'); 
			//get user id from session
			$id = $this->session->userdata('id'); 
			
			//query for user table
			$data['query_user'] = $this->user_model->user_profile($username, $id); 
			//query for transactions table
			$data['query_book'] = $this->user_model->user_book($id); 
			
			//save message
			$data['save_message'] = ""; 
			//username exist message
			$data['username_exist'] = "";
			
			//load view
			$this->load->view("profile_view", $data); 
			
		}
		
		/**
		* Save Modification to Profile function
		* 
		* @access	public
		* @param	none
		* @return	none
		*/
		public function save(){ 
			//get username from session
			$username = $this->session->userdata('username'); 
			//get user id from session
			$id = $this->session->userdata('id'); 
			//get user type from session
			$user_type = $this->session->userdata('user_type'); 
			
			//if submit is clicked 
			if(isset($_POST['submit'])){ 
				//if password should change
				if($_POST["password"]!="") 
					$password=md5($_POST["password"]);
				//if password is the same as before
				else { 
					//get password from database
					$query = $this->user_model->user_profile($username, $id);
					foreach($query as $row){
						$password = $row->password;
					}
				}
				
				//if inputed username exist
				if($this->user_model->username_exists($_POST["username"])){
					//get username from session
					$username = $this->session->userdata('username'); 
					//set message
					$data['username_exist'] = "Username already exist!";
				}else{
					//entered username is unique
					$username =$_POST["username"];
					$data['username_exist'] = "";
				}
				
				//get values from form
				$college_address=$_POST["college_address"];
				$email_address=$_POST["email_address"];
				$contact_number=$_POST["contact_number"];
				
			}
			
			//update user profile
			$this->user_model->user_update_profile($id, $username, $password, $college_address, $email_address, $contact_number);
			//save message
			$data['save_message'] = "Update Saved!";
			
			//title of page
			$data["title"] = "Profile - ICS Library System"; 
			
			//reset user data on session
			$sessionData = array('loggedIn' => true, 'id' => $id, 'user_type' => $user_type, 'username' => $username); 
			$this->session->set_userdata($sessionData);
			
			//user query read from database again
			$data['query_user'] = $this->user_model->user_profile($username, $id); 
			//query for transactions table
			$data['query_book'] = $this->user_model->user_book($id); 
			
			//load view
			$this->load->view("profile_view", $data);
		}
		
	}
?>