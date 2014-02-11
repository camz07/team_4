<?php
/**
 * User_model
 *
 * @package	icsls
 * @category	Model
 * @author	CMSC128 - AB-5L Team 4
 */
class User_model extends CI_Model{
	/**
	 * This function gets the reference material info that matched the search input
	 * @param	search input (string)
	 * @return	rows from database
	 */
	public function search_reference_material($input){
		$searchQuery = $this->db->query("SELECT * FROM reference_material WHERE title LIKE '%$input%' OR author LIKE '%$input%' OR isbn LIKE '%$input%' OR publisher LIKE '%$input%' OR publication_year LIKE '%$input%' OR course_code LIKE '%$input%'");
		return $searchQuery->result();
	}

	/**
	 * This function returns the query result
	 * @param	query (string)
	 * @return	rows from database
	 */
	public function advanced_search($query){
		return $this->db->query($query);
	}

	/**
	 * This function checks if the User can reserve, cannot reserve, cannot reserve but can waitlist OR has reserved the reference material already
	 * @param	referenceId (int), userId (int), userType (char)
	 * @return	true || false || constant number (7)
	 */
	public function reserve_reference_material($referenceId, $userId, $userType){
		date_default_timezone_set("Asia/Manila");	//timezone here in the Philippines

		 $userQuery = $this->db->query("SELECT borrow_limit FROM users WHERE id = '$userId'");
		 foreach ($userQuery->result() as $row) { $userBorrowLimit = $row->borrow_limit; }

		 $transactionQuery = $this->db->query("SELECT reference_material_id, borrower_id, date_reserved FROM transactions WHERE reference_material_id = '$referenceId' AND borrower_id = '$userId' AND date_reserved IS NOT NULL");

		 $referenceQuery = $this->db->query("SELECT access_type, total_available, times_borrowed FROM reference_material WHERE id = '$referenceId'");
		 foreach ($referenceQuery->result() as $row) { 
		 	$accessType = $row->access_type;
		 	$totalAvailable = $row->total_available;
		 	$timesBorrowed = $row->times_borrowed;
		 }

		 if(($transactionQuery->num_rows() > 0) || ($userBorrowLimit <= 0) || ($accessType == 'F' && $userType  == 'S')) return false;
		 else if($totalAvailable == 0) return 7;
		 else{
		 		$newLimit = $userBorrowLimit - 1;
		 		$newTotal = $totalAvailable - 1;
		 		$newTimesBorrowed = $timesBorrowed + 1;
		 		$dateReserved = date('Y-m-d');
		 		$dateParts = explode('-', $dateReserved);
		 		$reserveDue = date('Y-m-d', mktime(0,0,0, $dateParts[1], $dateParts[2] + 3, $dateParts[0]));	//adds 3 days to the day of reservation
				$this->db->query("UPDATE users SET borrow_limit = '$newLimit' WHERE id ='$userId'");
				$this->db->query("UPDATE reference_material SET total_available = '$newTotal', times_borrowed = '$newTimesBorrowed' WHERE id ='$referenceId'");
				
				$data = array('reference_material_id' => $referenceId, 'borrower_id' => $userId, 'user_type' => $userType, 'waitlist_rank' => NULL, 'date_waitlisted' => NULL, 'date_reserved' => $dateReserved, 'reservation_due_date' => $reserveDue, 'date_borrowed' => NULL, 'borrow_due_date' => NULL, 'date_returned' => NULL);
				$this->db->insert('transactions', $data);
				return true;
		 }
	}

	/**
	 * This function checks if the User can waitlist, cannot waitlist, can still reserve OR has waitlisted in the reference material already
	 * @param	referenceId (int), userId (int), userType (char)
	 * @return	true || false || constant number (7)
	 */
	public function waitlist_reference_material($referenceId, $userId, $userType){
		date_default_timezone_set("Asia/Manila");	//timezone in the Philippines

		$bookStatus = $this->db->query("SELECT total_available, access_type FROM reference_material WHERE id='$referenceId'");
		foreach ($bookStatus->result() as $row) {
			$book = $row->total_available;
			$accessType = $row->access_type;
		}

		$waitlistStatus = $this->db->query("SELECT waitlist_limit FROM users WHERE id='$userId'");
		foreach ($waitlistStatus->result() as $row2) { $limit = $row2->waitlist_limit; }

 		$transactionQuery = $this->db->query("SELECT reference_material_id, borrower_id, date_waitlisted FROM transactions WHERE reference_material_id='$referenceId' AND borrower_id='$userId' AND date_waitlisted IS NOT NULL");

		if(($transactionQuery->num_rows() > 0) || ($limit <= 0) || ($accessType == 'F' && $userType  == 'S')) return false;
		else if($book > 0) return 7;
		else{
			$waitlistRank = $this->db->query("SELECT MAX(waitlist_rank) as maxRank FROM transactions WHERE reference_material_id='$referenceId'");
			if($waitlistRank->num_rows() == 0){
				$newLimit = $limit - 1;
				$dateWaitlisted = date('Y-m-d');
				$rank = 1;

				$this->db->query("UPDATE users SET waitlist_limit = '$newLimit' WHERE id ='$userId'");
				$data = array('reference_material_id' => $referenceId, 'borrower_id' => $userId, 'user_type' => $userType, 'waitlist_rank' => $rank, 'date_waitlisted' => $dateWaitlisted, 'date_reserved' => NULL, 'reservation_due_date' => NULL, 'date_borrowed' => NULL, 'borrow_due_date' => NULL, 'date_returned' => NULL);
				$this->db->insert('transactions', $data);
				return true;
			}
			else{
				foreach ($waitlistRank->result() as $row3) { $maxRank = $row3->maxRank; }
				$newMaxRank = $maxRank + 1;
				$newLimit = $limit - 1;
				$dateWaitlisted = date('Y-m-d');

				$this->db->query("UPDATE users SET waitlist_limit = '$newLimit' WHERE id ='$userId'");
				$data = array('reference_material_id' => $referenceId, 'borrower_id' => $userId, 'user_type' => $userType, 'waitlist_rank' => $newMaxRank, 'date_waitlisted' => $dateWaitlisted, 'date_reserved' => NULL, 'reservation_due_date' => NULL, 'date_borrowed' => NULL, 'borrow_due_date' => NULL, 'date_returned' => NULL);
				$this->db->insert('transactions', $data);
				return true;
				}
			}
	}

	/**
	 * This function check if the user is registered
	 * @param	username (string), password (string)
	 * @return	true || false
	 */
	public function user_exists($username, $password){
		$userCount = $this->db->query("SELECT * FROM users WHERE username='$username' AND password='$password'")->num_rows();
		return ($userCount == 1 ? true : false);
	}

	/**
	 * This function returns the id, user type and username of the user
	 * @param	username (string), password (string)
	 * @return	rows from the database
	 */
	public function get_user_data($username, $password){
		return $this->db->query("SELECT id, user_type, username FROM users WHERE username='$username' AND password='$password'")->result();
	}

	/**
	 * This function returns the attributes of the user
	 * @param	username (string), id (int)
	 * @return	rows from the database
	 */
	public function user_profile($username, $id){
		return $this->db->query("SELECT * FROM users WHERE username='$username' AND id='$id'")->row();
	}

	/**
	 * This function updates the user information
	 * @param	id (int), username (string), password (string), college_address (string), email (string), contact number (int)
	 * @return	none
	 */
	public function user_update_profile($id, $username, $password, $college_address, $email_address, $contact_number){
		$this->db->query("UPDATE users SET username='$username', password='$password', college_address='$college_address', email_address='$email_address', contact_number='$contact_number' WHERE id='$id'");
	}

	/**
	 * This function returns the transactions of the user
	 * @param	id (int)
	 * @return	rows from the database
	 */
	public function user_book($id){
		return $this->db->query("SELECT * FROM transactions WHERE borrower_id='$id'")->result();
	}

	/**
	 * This function returns the attributes of the book
	 * @param	reference material id (int)
	 * @return	rows from the database
	 */
	public function user_book_reserve($reference_material_id){
		return $this->db->query("SELECT * FROM reference_material WHERE id='$reference_material_id'")->result();
	}

	/**
	 * This function inserts the account to the database
	 * @param	table name (string), data (array)
	 * @return	none
	 */
	public function insert_account($table_name, $data){
		$snum = $this->input->post('student_number');
		$enum = $this->input->post('employee_number');
		$lname = $this->input->post('last_name');
		$fname = $this->input->post('first_name');
		$mname = $this->input->post('middle_name');
		$usertype = $this->input->post('usertype');
		$username = $this->input->post('username');
		$password = md5($_POST['password']);
		$collegeAdd = $this->input->post('college_address');
		$email = $this->input->post('email_address');
		$contactNum = $this->input->post('contact_number');
		$college = $this->input->post('college');
		$degree = $this->input->post('degree');

		$data = array('employee_number' => $enum, 'student_number' => $snum, 'last_name' => $lname, 'first_name' => $fname, 'middle_name' => $mname, 'user_type' => $usertype, 'username' => $username, 'password' => $password, 'college_address' => $collegeAdd, 'email_address' => $email, 'contact_number' => $contactNum, 'borrow_limit' => 3, 'waitlist_limit' => 5, 'college' => $college, 'degree' => $degree);
		$this->db->insert($table_name, $data);
	}

	/**
	 * This function checks if the student number already exists
	 * @param	student number(varchar)
	 * @return	true || false
	 */
	public function student_exists($studnum){
		$studentQuery = $this->db->query("SELECT student_number FROM users WHERE student_number = '$studnum'");
		if($studentQuery->num_rows() > 0) return true;
		else return false;
	}
	
	/**
	 * This function checks if the employee number already exists
	 * @param	employee number(varchar)
	 * @return	true || false
	 */
	public function faculty_exists($enum){
		$facultyQuery = $this->db->query("SELECT employee_number FROM users WHERE employee_number = '$enum'");
		if($facultyQuery->num_rows() > 0) return true;
		else return false;
	}

	/**
	 * This function checks if the username already exists
	 * @param	username (varchar)
	 * @return	true || false
	 */
	public function username_exists($uname){
		$usernameQuery = $this->db->query("SELECT username FROM users WHERE username = '$uname'");
		if($usernameQuery->num_rows() > 0) return true;
		else return false;
	}

	/**
	 * This function cancels a reservation and updates the borrow_limit of the user, total_available and times_borrowed of the reference material
	 * @param	reference id (int), user id (int)
	 * @return	true
	 */
	public function cancel_reserve_reference_material($referenceId, $userId){
		$userQuery = $this->db->query("SELECT borrow_limit FROM users WHERE id = '$userId'");
		foreach ($userQuery->result() as $row) { $userBorrowLimit = $row->borrow_limit; }
		
		$referenceQuery = $this->db->query("SELECT total_available, times_borrowed FROM reference_material WHERE id = '$referenceId'");
		 foreach ($referenceQuery->result() as $row) { 
		 	$totalAvailable = $row->total_available;
		 	$timesBorrowed = $row->times_borrowed;
		}

		$newLimit = $userBorrowLimit + 1;
		$newTotal = $totalAvailable + 1;
		$newTimesBorrowed = $timesBorrowed - 1;
		$this->db->query("UPDATE users SET borrow_limit = '$newLimit' WHERE id ='$userId'");
		$this->db->query("UPDATE reference_material SET total_available = '$newTotal', times_borrowed = '$newTimesBorrowed' WHERE id ='$referenceId'");
			
		$this->db->query("DELETE FROM transactions WHERE borrower_id ='$userId' AND reference_material_id ='$referenceId'");
		return true;
	}

	/**
	 * This function cancels a waitlist and updates the waitlist_limit of the user and the waitlist_rank of the other users in the transactions table
	 * @param	reference id (int), user id (int)
	 * @return	true
	 */
	public function cancel_waitlist_reference_material($referenceId, $userId){
		$userQuery = $this->db->query("SELECT waitlist_limit FROM users WHERE id = '$userId'");
		foreach ($userQuery->result() as $row) { $userWaitlistLimit = $row->waitlist_limit; }
		$waitlistRank = $this->db->query("SELECT waitlist_rank FROM transactions WHERE reference_material_id='$referenceId' AND borrower_id ='$userId'");
		foreach ($waitlistRank->result() as $row) { $userWaitlistRank = $row->waitlist_rank; }

		$newLimit = $userWaitlistLimit + 1;
		$this->db->query("UPDATE users SET waitlist_limit = '$newLimit' WHERE id ='$userId'");			
		$this->db->query("SET @rank = '-1'"); 
		$this->db->query("UPDATE transactions SET waitlist_rank = $userWaitlistRank + (SELECT @rank := @rank + 1) WHERE waitlist_rank > '$userWaitlistRank' AND reference_material_id='$referenceId'");			
		$this->db->query("DELETE FROM transactions WHERE borrower_id ='$userId' AND reference_material_id ='$referenceId'");
		return true;
	}

	/**
	 * This function changes the user's profile picture
	 * @param	username (varchar)
	 * @return	none
	 */
	function upload_picture($username){
		$userImageDirectory = 'img/user_images/';
		$defaultImage = '0.jpg';

		$config['upload_path'] = $userImageDirectory;
		$config['allowed_types'] = 'jpg|png';
		$config['max_size']	= '2048 KB';
		$config['max_width'] = '1024';
		$config['max_height'] = '1024';
		$this->upload->initialize($config);

		if($this->session->userdata('username') == $username){
			$this->db->select('profile_picture')
			->from('users')
			->where('username',$username);
			$currentImage = $this->db->get()->result()[0]->profile_picture;

			if($currentImage != $defaultImage){
				unlink($currentImage);
			}
		}

		if($this->upload->do_upload('profile_picture')){
			$uploadData = $this->upload->data('profile_picture');
			$fullPath = $userImageDirectory . $uploadData['orig_name'];
			$newPicture = $this->session->userdata('id') . $uploadData['file_ext'];
			rename($fullPath, $userImageDirectory . $newPicture);
			$this->db->where('username', $username);
			$this->db->update('users',array('profile_picture' => $newPicture));

		}

	}
}

?>