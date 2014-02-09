<?=$this->load->view("includes/header")?>

<!DOCTYPE html>
<html>
	<head>
		<title><?=$title?></title>
		<script src=<?php echo base_url("themes/jquery-2.0.3.min.js"); ?>></script>
	</head>

	<body>
		<h1><?=$header?></h1>
		<br/>
		<div id = "search-whole">
			<!-- Search Form -->
			<div id = "search-shown">
				<form method="post" accept-charset="utf-8" action="http://localhost/icsls/index.php/search/search_rm">
					<input type="text" name="input" size = "50"/>
					<input type="submit" name="search1" value="Search"/>
				</form>
				<a href="#" class = "search-toggle">Advanced Search</a>
			</div>
			<!-- Advance Search Form -->
			<div class = "search-hidden">
				<form method="post" accept-charset="utf-8" action="http://localhost/icsls/index.php/search/advance_search">
				<table>
					<tr><td><input value="title" type="checkbox" name="projection[]" checked="true">Title:</td><td><input type="text" name="title" size = "30"><br/></td></tr>
					<tr><td><input value="author" type="checkbox" name="projection[]">Author:</td><td><input type="text" name="author" size = "30"><br/></td></tr>
					<tr><td><input value="year_published" type="checkbox" name="projection[]">Year Published:</td><td><input type="text" name="year_published" size = "30"><br/></td></tr>
					<tr><td><input value="publisher" type="checkbox" name="projection[]">Publisher:</td><td><input type="text" name="publisher" size = "30"><br/></td></tr>
					<tr><td><input value="course_code" type="checkbox" name="projection[]">Subject:</td><td><input type="text" name="course_code" size = "30"><br/></td></tr>
					<tr>
						<td>Category:</td>
						<td>
							<select>
								<option value="B">Book</option>
								<option value="J">Journal</option>
								<option value="T">Thesis</option>
								<option value="D">CD</option>
								<option value="C">Catalog</option>
							</select><br/>
						</td>
					</tr>	
				</table>
				<input type="submit" value="Advanced Search" />	
				</form>		
			</div>
		</div>

	<script type="text/javascript">
	//javascript for hiding/showing the advance search form
		$(document).ready( function(){
			var i = 0;
			$('.search-hidden').hide();
			$('.search-toggle').click(function() {
			    $('.search-hidden').slideToggle();
			    if(i == 0){
					$('.search-toggle').html('Hide Advanced Search');
					i = 1;
				}else{
					 $('.search-toggle').html('Advanced Search');
					 i = 0;
				}
			});
		});
	</script>		
	</body>
</html>
	
<?=$this->load->view("includes/footer")?>