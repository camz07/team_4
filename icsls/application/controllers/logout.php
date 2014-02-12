<?php
/**
 * Logout Class
 *
 * @Package	icsls
 * @Author	Raquel Abigail Bunag
 */
class Logout extends CI_Controller{
	/**
	* Logout function
	* 
	* @access	public
	* @param	none
	* @return	none
	*/
	public function Logout(){
		parent::__construct();
	}
	
	/**
	* Index function of Logout
	* 
	* @access	public
	* @param	none
	* @return	none
	*/
	public function index(){ 
		//destroy session
		$this->session->sess_destroy(); 
		
		//title
		$data["title"] = "Home - ICS Library System"; 
		// load login page
		redirect(base_url()."index.php/login"); 
	}
}

?>