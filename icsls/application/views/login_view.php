<html>
	<head>
		<title><?=$title?></title>
		<!-- Link stylesheets here -->
	</head>
	<body>

	<div>
		<?php if($this->session->userdata('loggedIn')){ //if logged in?>
				<!--Optional: Session data-->
				<?=$this->session->userdata('loggedIn')?><br/>
				<?=$this->session->userdata('id')?><br/>
				<?=$this->session->userdata('user_type')?><br/>
				
				<!--Username from session-->
				Hi <?=$this->session->userdata('username')?>! <br/>
				
			<div>
				<!--View Profile Anchor-->
				<a href="<?=base_url()."index.php/profile"?>">
					View Profile
				</a>
			</div>
			<br/>
			<!--Logout Anchor-->
			<a href="<?=base_url().'index.php/logout'?>">
				<button>Logout</button>
			</a>
		<?php }else{ //if not logged in?>
			<fieldset>
			<legend>Login</legend>
			<br/>
			<!--Login Form-->
			<form action="<?=base_url().'index.php/login/login_user'?>" method="post">
			
				<input type="text" name="username" placeholder="Username" required />
				<input type="password" name="password" placeholder="Password" required />
				<br/>
				<br/>
				<!--Error Message-->
				<?=$error_message?>
				<br/>
				<br/>
				<input type="submit" name="login" style="color: #fffff" value="Log in"/>
				
			</form>
		</fieldset>
		<?php } ?>
	</div>
	