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
		$this->db->select('*')
			->from('reference_material')
			->like('title',$input)
			->or_like('author',$input)
			->or_like('isbn',$input)
			->or_like('publisher',$input)
			->or_like('publication_year',$input)
			->or_like('course_code',$input);

		$searchQuery = $this->db->get();
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

		$this->db->select('borrow_limit')
			->from('users')
			->where('id',$userId);
		$userQuery = $this->db->get();
		foreach ($userQuery->result() as $row) { $userBorrowLimit = $row->borrow_limit; }

		$this->db->select('reference_material_id, borrower_id, date_reserved')
			->from('transactions')
			->where('reference_material_id',$referenceId)
			->where('borrower_id',$userId)
			->where('date_reserved IS NOT NULL');
		$transactionQuery = $this->db->get();

		$this->db->select('access_type, total_available, times_borrowed')
			->from('reference_material')
			->where('id',$referenceId);
		$referenceQuery = $this->db->get();

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
				
				$borrowArray = array('borrow_limit' => $newLimit);
				$this->db->where('id', $userId);
				$this->db->update('users', $borrowArray);

				$refArray = array('total_available' => $newTotal, 'times_borrowed' => $newTimesBorrowed);
				$this->db->where('id', $referenceId);
				$this->db->update('reference_material', $refArray);
			
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

		$this->db->select('total_available, access_type')
			->from('reference_material')
			->where('id',$referenceId);
		$bookStatus = $this->db->get();
		foreach ($bookStatus->result() as $row) {
			$book = $row->total_available;
			$accessType = $row->access_type;
		}

		$this->db->select('waitlist_limit')
			->from('users')
			->where('id',$userId);
		$waitlistStatus = $this->db->get();
		foreach ($waitlistStatus->result() as $row2) { $limit = $row2->waitlist_limit; }

		$this->db->select('reference_material_id, borrower_id, date_waitlisted')
			->from('transactions')
			->where('reference_material_id',$referenceId)
			->where('borrower_id',$userId)
			->where('date_reserved IS NOT NULL');
		$transactionQuery = $this->db->get();

		if(($transactionQuery->num_rows() > 0) || ($limit <= 0) || ($accessType == 'F' && $userType  == 'S')) return false;
		else if($book > 0) return 7;
		else{
			$this->db->select_max('waitlist_rank', 'maxRank')
				->from('transactions')
				->where('reference_material_id',$referenceId);
			$waitlistRank = $this->db->get();
			if($waitlistRank->num_rows() == 0){
				$newLimit = $limit - 1;
				$dateWaitlisted = date('Y-m-d');
				$rank = 1;

				$waitlistArray = array('waitlist_limit' => $newLimit);
				$this->db->where('id', $userId);
				$this->db->update('users', $waitlistArray);

				$data = array('reference_material_id' => $referenceId, 'borrower_id' => $userId, 'user_type' => $userType, 'waitlist_rank' => $rank, 'date_waitlisted' => $dateWaitlisted, 'date_reserved' => NULL, 'reservation_due_date' => NULL, 'date_borrowed' => NULL, 'borrow_due_date' => NULL, 'date_returned' => NULL);
				$this->db->insert('transactions', $data);
				return true;
			}
			else{
				foreach ($waitlistRank->result() as $row3) { $maxRank = $row3->maxRank; }
				$newMaxRank = $maxRank + 1;
				$newLimit = $limit - 1;
				$dateWaitlisted = date('Y-m-d');

				$waitlistArray = array('waitlist_limit' => $newLimit);
				$this->db->where('id', $userId);
				$this->db->update('users', $waitlistArray);

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
		$this->db->select('*')
			->from('users')
			->where('username',$username)
			->where('password',$password);
		$userCount = $this->db->get();
		return ($userCount->num_rows() == 1 ? true : false);
	}

	/**
	 * This function returns the id, user type and username of the user
	 * @param	username (string), password (string)
	 * @return	rows from the database
	 */
	public function get_user_data($username, $password){
		$this->db->select('id, user_type, username')
			->from('users')
			->where('username',$username)
			->where('password',$password);
		$userData = $this->db->get();
		return $userData->result();
	}

	/**
	 * This function returns the attributes of the user
	 * @param	username (string), id (int)
	 * @return	rows from the database
	 */
	public function user_profile($username, $id){
		$this->db->select('*')
			->from('users')
			->where('username',$username)
			->where('id',$id);
		$userProf = $this->db->get();
		return $userProf->row();
	}

	/**
	 * This function updates the user information
	 * @param	id (int), username (string), password (string), college_address (string), email (string), contact number (int)
	 * @return	none
	 */
	public function user_update_profile($id, $username, $password, $college_address, $email_address, $contact_number){
		$updateArray = array('username' => $username, 'password' => $password, 'college_address' => $college_address, 'email_address' => $email_address, 'contact_number' => $contact_number);
				$this->db->where('id', $id);
				$this->db->update('users', $updateArray);
	}

	/**
	 * This function returns the transactions of the user
	 * @param	id (int)
	 * @return	rows from the database
	 */
	public function user_book($id){
		$this->db->select('*')
			->from('transactions')
			->where('borrower_id',$id);
		$userBook = $this->db->get();
		return $userBook->result();
	}

	/**
	 * This function returns the attributes of the book
	 * @param	reference material id (int)
	 * @return	rows from the database
	 */
	public function user_book_reserve($reference_material_id){
		$this->db->select('*')
			->from('reference_material')
			->where('id',$reference_material_id);
		$userBookReserve = $this->db->get();
		return $userBookReserve->result();
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
		$this->db->select('student_number')
			->from('users')
			->where('student_number',$studnum);
		$studentQuery = $this->db->get();
		if($studentQuery->num_rows() > 0) return true;
		else return false;
	}
	
	/**
	 * This function checks if the employee number already exists
	 * @param	employee number(varchar)
	 * @return	true || false
	 */
	public function faculty_exists($enum){
		$this->db->select('employee_number')
			->from('users')
			->where('employee_number',$enum);
		$facultyQuery = $this->db->get();
		if($facultyQuery->num_rows() > 0) return true;
		else return false;
	}

	/**
	 * This function checks if the username already exists
	 * @param	username (varchar)
	 * @return	true || false
	 */
	public function username_exists($uname){
		$this->db->select('username')
			->from('users')
			->where('username',$uname);
		$usernameQuery = $this->db->get();
		if($usernameQuery->num_rows() > 0) return true;
		else return false;
	}

	/**
	 * This function cancels a reservation and updates the borrow_limit of the user, total_available and times_borrowed of the reference material
	 * @param	reference id (int), user id (int)
	 * @return	true
	 */
	public function cancel_reserve_reference_material($referenceId, $userId){
		$this->db->select('borrow_limit')
			->from('users')
			->where('id',$userId);
		$userQuery = $this->db->get();
		foreach ($userQuery->result() as $row) { $userBorrowLimit = $row->borrow_limit; }
		
		$this->db->select('total_available, times_borrowed')
			->from('reference_material')
			->where('id',$referenceId);
		$referenceQuery = $this->db->get();
		foreach ($referenceQuery->result() as $row) { 
		 	$totalAvailable = $row->total_available;
		 	$timesBorrowed = $row->times_borrowed;
		}

		$newLimit = $userBorrowLimit + 1;
		$newTotal = $totalAvailable + 1;
		$newTimesBorrowed = $timesBorrowed - 1;

		$cancelArray = array('borrow_limit' => $newLimit);
				$this->db->where('id', $userId);
				$this->db->update('users', $cancelArray);

		$cancelArray2 = array('total_available' => $newTotal, 'times_borrowed' => $newTimesBorrowed);
				$this->db->where('id', $referenceId);
				$this->db->update('reference_material', $cancelArray2);
		
		$this->db->where('borrower_id', $userId);
		$this->db->where('reference_material_id', $referenceId);
		$this->db->delete('transactions');
		return true;
	}

	/**
	 * This function cancels a waitlist and updates the waitlist_limit of the user and the waitlist_rank of the other users in the transactions table
	 * @param	reference id (int), user id (int)
	 * @return	true
	 */
	public function cancel_waitlist_reference_material($referenceId, $userId){
		$this->db->select('waitlist_limit')
			->from('users')
			->where('id',$userId);
		$userQuery = $this->db->get();
		foreach ($userQuery->result() as $row) { $userWaitlistLimit = $row->waitlist_limit; }

		$this->db->select('waitlist_rank')
			->from('transactions')
			->where('reference_material_id',$referenceId)
			->where('borrower_id',$userId);
		$waitlistRank = $this->db->get();
		foreach ($waitlistRank->result() as $row) { $userWaitlistRank = $row->waitlist_rank; }

		$newLimit = $userWaitlistLimit + 1;

		$cancelArray2 = array('waitlist_limit' => $newLimit);
				$this->db->where('id', $userId);
				$this->db->update('users', $cancelArray2);
		
		$this->db->query("SET @rank = '-1'"); 
		$this->db->query("UPDATE transactions SET waitlist_rank = $userWaitlistRank + (SELECT @rank := @rank + 1) WHERE waitlist_rank > '$userWaitlistRank' AND reference_material_id='$referenceId'");			
		
		$this->db->where('borrower_id', $userId);
		$this->db->where('reference_material_id', $referenceId);
		$this->db->delete('transactions');
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