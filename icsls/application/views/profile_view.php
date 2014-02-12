<html>
<head></head>
<?=$this->load->view("includes/header")?>
<body>
	<?php if($this->session->userdata('loggedIn')){ //if logged in?>
	<div>
		<form action="<?=base_url()."index.php/profile/save"?>" method="post"><!--Profile Form-->
		<?=$save_message?>  <br/><!--Save Message-->
		<?=$username_exist?><!--Username Message-->
		<br/>
		<br/>
		<?php foreach($query_user as $row){ ?><!--Profile-->
			<h1><?=$row->last_name?>, <?=$row->first_name?> <?=$row->middle_name?></h1>
			
			<?php if($row->user_type=='S') //if user is student
				echo "Student No.: $row->student_number<br/>"; 
			else	echo "Employee No.: $row->employee_number<br/>" ; // if user is employee
			?>				
			Username: <input type="text" name="username" value="<?=$row->username?>"><br/><!--Username-->
			Password: <input type="password" name="password" placeholder="password"><br/><!--Enter new password-->
			College Address: <input type="text" name="college_address" value="<?=$row->college_address?>"><br/><!--College address-->
			Email Address: <input type="text" name="email_address" value="<?=$row->email_address?>"><br/><!--Email Address-->
			Contact No.: <input type="text" name="contact_number" value="<?=$row->contact_number?>"><br/><!--Contact No-->
		<?php }//end of foreach?>
		
		<input type='submit' name='submit' value='submit'/>
		</form>
	</div>
	<div>
		<br/>
		<br/>
		<h4>Borrowed Books: </h4> <!--Borrowed books-->
		<?php foreach($query_book as $row){ //transaction query
			$reference_material_id=$row->reference_material_id;
			$query_ref_mat = $this->user_model->user_book_reserve($reference_material_id); //query for table reference_material
			foreach($query_ref_mat as $row2){ 
				if($row->date_returned != null) continue; // if book is returned
				if($row->waitlist_rank ==null) { // if do not have a rank on waitlist
			?>
				Title: <?=$row2->title?><br/><!--Title-->
				Author: <?=$row2->author?><br/><!--Author-->
				ISBN: <?=$row2->isbn?><br/><!--ISBN-->
				Category: <?=$row2->category?><br/><!--Category-->
				Description: <?=$row2->description?><br/><!--Description-->
				Publication: <?=$row2->publisher?> <?=$row2->publication_year?><br/><!--Publication-->
				Access Type: <?=$row2->access_type?><br/> <!--Access Type-->
				Course Code: <?=$row2->course_code?><br/> <!--Course Code-->
				<br/>
				<?php }//end if?>
			<?php }//end of foreach ?>
		<?php }//end of foreach?>
	</div>
	
	<div>
		<br/>
		<br/>
		<h4>Waitlisted Books: </h4> <!--Waitlisted Books->
		<?php foreach($query_book as $row){ //transaction query
			$reference_material_id=$row->reference_material_id;
			$query_ref_mat = $this->user_model->user_book_reserve($reference_material_id);//query for table reference_material
			foreach($query_ref_mat as $row2){ 
				if($row->waitlist_rank == null || $row->waitlist_rank <= 0) continue; // if there is no waitlist rank
			?>
				Title: <?=$row2->title?><br/><!--Title-->
				Author: <?=$row2->author?><br/><!--Author-->
				ISBN: <?=$row2->isbn?><br/><!--ISBN-->
				Category: <?=$row2->category?><br/><!--Category-->
				Description: <?=$row2->description?><br/><!--Description-->
				Publication: <?=$row2->publisher?> <?=$row2->publication_year?><br/><!--Publication-->
				Access Type: <?=$row2->access_type?><br/> <!--Access Type-->
				Course Code: <?=$row2->course_code?><br/> <!--Course Code-->
				<br/>
			<?php } // end of foreach?>
		<?php }//end of foreach?>
	</div>
	<?php }else{ //if not logged in?>
		<h1>Please login first</h1>
	<?php } ?>
	
<?=$this->load->view("includes/footer")?>
</body>
</html>