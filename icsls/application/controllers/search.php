<?php

class Search extends CI_Controller{
	public function Search(){
		parent::__construct();
		//load libraries,models, helpers
		$this->load->helper('url');
		$this->load->helper('form');
		$this->load->model('user_model');
		$this->load->library('pagination');
	}

	public function index(){
		$data['title'] = "Home - ICS Library System";
		$data['header'] = "Search";
		$this->load->view("search_view", $data);
	} 
	/* Description: Calls the search_reference_material function to get search results
	   based on the User's search input */
	public function search_rm(){
		$data['title'] = "Home - ICS Library System";
		$data['header'] = "Search";

		$input = $this->input->post('input');	//gets the user input
		$userId = $this->input->post('id');		//gets the user_id

		if($input == NULL) redirect(base_url()."index.php/search");

		$sessionData = array('searchInput' => $input);
		$this->session->set_userdata($sessionData);

		$data['bookInfo'] = $this->user_model->search_reference_material($input);
		//$data['number_of_vacancies'] = count($data['vacancies']);
		//if($data['bookInfo']->num_rows() == 0) echo "HErE";
		$data['searchInput'] = $input;
		$data['flag'] = 1;
		$this->load->view("search_result_view", $data);		
	}

	public function advance_search(){
		$data["title"] = "Home - ICS Library System";
		$data['header'] = "Search";

		$booktitle="";
		$bookauthor="";
		$bookyear="";

		$temparray = array();//for keywords
		$temparrayvalues = array();//for the values

		$query = "";//for query

		if(in_array("title", $_POST['projection'])){
			$keyword_title = $this->input->post('title');
			if($keyword_title==null){
				echo "please insert title <br />";
				return;
			}

			$query .= "title like '%$keyword_title%'";
			array_push($temparray,'title');
			array_push($temparrayvalues,$keyword_title);
		}
		
		if(in_array("author", $_POST['projection'])){
			$keyword_author = $this->input->post('author');
			if($keyword_author==null){
				echo "please insert name of the author <br />";
				return;
			}

			if ( in_array('title',$temparray) ) {
				$query .= " or author like '%$keyword_author%'";
			}
			else{
				$query .= " author like '%$keyword_author%'";
			}
			array_push($temparray,'author');
			array_push($temparrayvalues,$keyword_author);
		}

		if(in_array("year_published", $_POST['projection'])){
			$keyword_year_published = $this->input->post('year_published');
			if($keyword_year_published ==null){
				echo "please insert the year published <br />";
				return;
			}

			if ( in_array('title',$temparray) || in_array('author',$temparray)) {
				$query .= " or publication_year like '%$keyword_year_published%'";
			}
			else{
				$query .= " publication_year like '%$keyword_year_published%'";
			}
			array_push($temparray,'year_published');
			array_push($temparrayvalues,$keyword_year_published);
		}
		
		if(in_array("publisher", $_POST['projection'])){
			$keyword_publisher = $this->input->post('publisher');
			if($keyword_publisher==null){
				echo "please insert the publisher <br />";
				return;
			}
			
			if ( in_array('title',$temparray) || in_array('author',$temparray) || in_array('year_published', $temparray)) {
				$query .= " or publisher like '%$keyword_publisher%'";
			}
			else{
				$query .= " publisher like '%$keyword_publisher%'";
			}
			array_push($temparray,'publisher');
			array_push($temparrayvalues,$keyword_publisher);
		}

		if(in_array('course_code',$_POST['projection'])){
	    	$keyword_course_code = $this->input->post('course_code');
	    	if($keyword_course_code==null){
				echo "please insert the course code <br />";
				return;
			}

			if ( in_array('title',$temparray) || in_array('author',$temparray) || in_array('year_published', $temparray) || in_array('publisher', $temparray)) {
				$query .= " or course_code like '%$keyword_course_code%'";
			}
			else{
				$query .= " course_code like '%$keyword_course_code%'";
			}
			array_push($temparray,'course_code');
			array_push($temparrayvalues,$keyword_course_code);
		}

		$realquery = "Select * from reference_material where {$query} order by title asc";
		$result = $this->user_model->advanced_search($realquery);
		if($result->num_rows() > 0){
			//$data['rows'] = $result->result();
			$data['bookInfo'] = $result->result();
			$data['flag'] = 1;
			$this->load->view('search_result_view', $data);
		}
	}

	/* Description: function for reserving/waitlisting */
	public function transaction(){
		$data['title'] = "Home - ICS Library System";
		$data['header'] = "Search";
		
		$referenceId = $this->input->post('id');
		$userId = $this->session->userdata('id');
		$user_type = $this->session->userdata('user_type');
		$input = $this->session->userdata('searchInput');

		//if "Reserve" button was clicked
		if(isset($_POST['reserve'])){
			$reserveStatus = $this->user_model->reserve_reference_material($referenceId, $userId, $user_type);
			
			if($reserveStatus == false){	//if conditions in reserving were not satisfied
				echo "Reserve Action Denied: <br/>";
				echo "&nbsp;&nbsp;&nbsp;&nbsp;3 possible reasons <br/>";
				echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;1. You are not allowed to access this book. <br/>";
				echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;2. You have reached the limit of borrowing books. <br/>";
				echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;3. You have reserved this book already. <br/><br/><br/>";
				$data['bookInfo'] = $this->user_model->search_reference_material($input);
				$data['flag'] = 1;
				$this->load->view('search_result_view.php', $data);

			}
			else if($reserveStatus == 1){	//successful reserve
				echo "Reference material was successfully reserved.";
				$sessionData = array('canWaitlist' => false, 'referenceId' => $referenceId, 'canReserve' => false);
				$this->session->set_userdata($sessionData);
				$data['bookInfo'] = $this->user_model->search_reference_material($input);
				$data['flag'] = 1;
				$this->load->view('search_result_view.php', $data);
			}
			else{	//if the reference material is out of stock
				$sessionData = array('canWaitlist' => true, 'referenceId' => $referenceId, 'canReserve' => false);
				$this->session->set_userdata($sessionData);
				$data['bookInfo'] = $this->user_model->search_reference_material($input);
				$data['flag'] = 0;
				$this->load->view('search_result_view.php', $data);
			}
		 //if "Waitlist" button was clicked
		}else if(isset($_POST['waitlist'])){
			$waitlistStatus = $this->user_model->waitlist_reference_material($referenceId, $userId, $user_type);
	
			if($waitlistStatus == false){	//if conditions in waitlisting were not satisfied
				echo "Waitlist Denied: <br/>";
				echo "&nbsp;&nbsp;&nbsp;&nbsp;3 possible reasons <br/>";
				echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;1. You are not allowed to access this book. <br/>";
				echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;2. You have reached the limit of maximum wait list. <br/>";
				echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;3. You have waitlisted on this book already. <br/><br/><br/>";
				$data['bookInfo'] = $this->user_model->search_reference_material($input);
				$data['flag'] = 1;
				$this->load->view('search_result_view.php', $data);
			}
			else if($waitlistStatus == 1){	//successful wait list
				echo "Reference material was successfully waitlisted.";
				$sessionData = array('canWaitlist' => false, 'referenceId' => $referenceId, 'canReserve' => false);
				$this->session->set_userdata($sessionData);
				$data['bookInfo'] = $this->user_model->search_reference_material($input);
				$data['flag'] = 1;
				$this->load->view('search_result_view.php', $data);
			}
			else{	//if the reference material is still available
				$sessionData = array('canReserve' => true, 'referenceId' => $referenceId, 'canWaitlist' => false);
				$this->session->set_userdata($sessionData);
				$data['bookInfo'] = $this->user_model->search_reference_material($input);
				$data['flag'] = 0;
				$this->load->view('search_result_view.php', $data);
			}
		 //if neither of the two buttons (Reserve, Waitlist) were clicked
		}else{
				$data['bookInfo'] = $this->user_model->search_reference_material($input);
				$data['flag'] = 1;
				$this->load->view('search_result_view.php', $data);
		}
	}
}?>