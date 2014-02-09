<?php
	class Profile extends CI_Controller{
		public function Profile(){
			parent::__construct();
			$this->load->model('user_model');
			$this->load->library('session');
			$this->load->helper('url');
			$this->load->helper('form');
		}
		/* Description: Calls the user_profile to get the User info from the database;
		   Calls the user_book to get the User's reserved and waitlisted books; All of
		   the info will be displayed in the User's profile. */
		public function index(){
			$data["title"] = "Profile - ICS Library System";

			$username = $this->session->userdata('username'); // get username from session
			$id = $this->session->userdata('id'); //get user id from session
			
			$data['query_user'] = $this->user_model->user_profile($username, $id); //query for user table
			$data['query_book'] = $this->user_model->user_book($id); //query for transactions table
			
			$data['save_message'] = "";
			$data['username_exist'] = "";
			$this->load->view("profile_view", $data);
		}

		/* Description: Updates the User's profile by calling the user_update_profile.
		   All the updated info will be displayed again in the User's profile. */
		public function save(){
			$username = $this->session->userdata('username');
			$id = $this->session->userdata('id');
			$user_type = $this->session->userdata('user_type');

			if(isset($_POST['submit'])){ //if submit is clicked 
				if($_POST["password"]!="") //if password was changed
					$password=md5($_POST["password"]);
				else { //if password is the same as before
					$query = $this->user_model->user_profile($username, $id);
					//foreach($query as $row){
						$password = $query->password;
					//}
				}

				if($this->user_model->username_exists($_POST["username"])){
					$username = $this->session->userdata('username'); //get username from session
					$data['username_exist'] = "Username already exist!";
				}else{
					$username =$_POST["username"];
					$data['username_exist'] = "";
				}	

				$college_address=$_POST["college_address"];
				$email_address=$_POST["email_address"];
				$contact_number=$_POST["contact_number"];
			}
			
			//update user profile
			$this->user_model->user_update_profile($id, $username, $password, $college_address, $email_address, $contact_number);
			$data["title"] = "Profile - ICS Library System";
			
			//reset user data on session
			$sessionData = array('loggedIn' => true, 'id' => $id, 'user_type' => $user_type, 'username' => $username); 
			$this->session->set_userdata($sessionData);
			
			//user query read from database again
			$data['query_user'] = $this->user_model->user_profile($username, $id); 
			$data['query_book'] = $this->user_model->user_book($id);
			
			$data['save_message'] = "Update Saved!";//save message
			$this->load->view("profile_view", $data);
		}

		/* Description: function for cancelling reserved/waitlisted books*/
		public function cancel_transaction(){
			$referenceId = $this->input->post('ref_id');
			$id = $this->session->userdata('id');
			// if "Cancel Reservation" was clicked
			if(isset($_POST['cancel_reserve'])){
				$cancelStatus = $this->user_model->cancel_reserve_reference_material($referenceId, $id);
				$data["title"] = "Profile - ICS Library System";

				$username = $this->session->userdata('username'); // get username from session
				$id = $this->session->userdata('id'); //get user id from session
				
				$data['query_user'] = $this->user_model->user_profile($username, $id); //query for user table
				$data['query_book'] = $this->user_model->user_book($id); //query for transactions table
				
				$data['save_message'] = "";
				redirect(base_url()."index.php/profile");
			}
			// if "Cancel Waitlist" was clicked
			if(isset($_POST['cancel_waitlist'])){
				$cancelStatus = $this->user_model->cancel_waitlist_reference_material($referenceId, $id);
				$data["title"] = "Profile - ICS Library System";

				$username = $this->session->userdata('username'); // get username from session
				$id = $this->session->userdata('id'); //get user id from session
				
				$data['query_user'] = $this->user_model->user_profile($username, $id); //query for user table
				$data['query_book'] = $this->user_model->user_book($id); //query for transactions table
				
				$data['save_message'] = "";
				redirect(base_url()."index.php/profile");
			}
		}
		
	}
?>