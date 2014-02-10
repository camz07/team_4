<html>
	<!--
		create_account view
		Form: First Name, Middle Name, Last Name
			Email address, College address, Contact Number
			Username, Password
			If the user is a student: Student number, College, Degree
			If the user is a faculty: Employee number
	-->
	<head>
		<title>Register</title>
		<script src=<?php echo base_url("themes/jquery-2.0.3.min.js"); ?>></script>
		<script type="text/javascript">	

			/* When this page is loaded, these are the functions*/
			window.onload = function(){
					createForm.password.onblur = validatePassword;
					createForm.repass.onblur = validateRePass;
					$("#stdno").hide();
					$("#student_number").hide();
					$(".collegeDropdown").hide();
					$(".degreeDropdown").hide();
					$("#enum").hide();
					$("#employee_number").hide();
					$("#employee_number").attr("disabled", "disabled");
					$("#student_number").attr("disabled", "disabled");
			}

			/* Parameters: None
				Description: Checks the password and identify if it is weak, medium or strong
				Return value: Message - Weak, Medium or Strong
			*/
			function validatePassword(){
				msg = "Invalid input!  ";
				str = createForm.password.value;
				
				if(str == "") msg+="Password is required. ";
				if(str.match(/^[a-z]$/)) msg="Strength: Weak";
				else if(str.match(/^[0-9]+$/)) msg="Strength: Weak";
				else if(str.match(/^[a-z0-9]+$/)) msg="Strength: Medium";
				else if(str.match(/^[a-zA-Z0-9]+$/)) msg="Strength: Strong";
				
				if(msg == "Invalid input!  ") msg="";
				document.getElementsByName("helppass")[0].innerHTML=msg;
				
			}
			
			/* Parameters: None
				Description: Checks the retyped password if it matched the first one
				Return value: Message - Passwords match. or Passwords does not match.
			*/
			function validateRePass(){
				msg = "Invalid input!  ";
				str = createForm.repass.value;
				
				if(str != createForm.password.value) msg+="Passwords does not match. ";
				else msg="Passwords match.";
				
				if(msg == "Invalid input!  ") msg="";
				document.getElementsByName("helprepass")[0].innerHTML=msg;
				if(msg == "") return true;
				
			}	
		</script>
	</head>

	<body>
		<div id="main">
		<!-- Start of the form -->
		<form method="post" action="http://localhost/icsls/index.php/create_account/account_created" accept-charset="utf-8" name="createForm">

			First Name: <input type="text" id="first_name" name = "first_name" pattern = "([A-Za-z]{2,32}\s*)+" required/><br/><br/>
			
			Middle Name: <input type="text" id="middle_name" name = "middle_name" pattern = "[A-Za-z]{1,2}"required/><br/><br/>
			
			Last Name: <input type="text" id="last_name" name = "last_name" pattern = "[A-Za-z]+" required/><br/><br/>
			
			Email address: <input type="text" id="email_address" name = "email_address" pattern = "^([a-z0-9._]{2,}@[a-zA-Z0-9.-]{2,}\.[a-zA-Z]{2,})(\.[a-zA-Z])*$" required/><br/><br/>
			
			College address: <input type="text" id="college_address" name = "college_address" required/><br/><br/>
			
			Contact Number: <input type="text" id="contact_number" name = "contact_number" pattern = "[0-9]{11}" required/><br/><br/>
			
			Username: <input type="text" id="username" name = "username"pattern = "[A-Za-z_.0-9]+" required/><br/><br/>
			
			Password: <input type="password" id="password" name = "password" required/>
				<span name="helppass"></span><br/><br/>
			
			Re-type Password: <input type="password" id="repass" name = "repass" required/>
				<span name="helprepass"></span><br/><br/>

			<select name="usertype" class="typeDropdown">
				<option value="">Select User Type</option>
				<option value="S"> Student </option>
				<option value="F"> Faculty </option>
			</select>

			<script type="text/javascript">
				/*Javascript for the dropdown - Type of User*/
				$(".typeDropdown").change(function() {
					var type=$(this).val();
					if(type == 'S'){			//if the user is a student
						/*	show: studno(label), student_number(textfield)
								degreedropdown, collegedropdown
						 	hide: enum(label), employee number(textfield) */
						$("#stdno").show();
						$("#student_number").show();
						$(".degreeDropdown").show();
						$(".collegeDropdown").show();
						$("#enum").hide();
						$("#employee_number").hide();
						$("#student_number").removeAttr("disabled");
					} else if(type == 'F'){		//if the user is a faculty
						/*  show: enum(label), employee number(textfield)
							hide: studno(label), student_number(textfield)
								degreedropdown, collegedropdown */
						$("#stdno").hide();
						$("#student_number").hide();
						$(".degreeDropdown").hide();
						$(".collegeDropdown").hide();
						$("#enum").show();
						$("#employee_number").show();
						$("#employee_number").removeAttr("disabled");
						$("#student_number").removeAttr("enabled");
					}
				});
			</script>

			<label id = 'stdno'> Student Number: </label>
				<input type="text" id="student_number" name = "student_number" pattern = "^[0-9]{4}[-]{1}[0-9]{5}$" required/>
					
			<select name="college" class="collegeDropdown">
				<option value="">Select College</option>
				<option value="CA"> College of Agriculture </option>
				<option value="CAS"> College of Arts and Sciences </option>
				<option value="CA-CAS"> College of Agriculture - College of Arts and Sciences</option>
				<option value="CDC"> College of Development Communication </option>
				<option value="CEM"> College of Economics and Management </option>
				<option value="CEAT"> College of Engineering and Agro-industrial Technology </option>
				<option value="CFNR"> College of Forestry and Natural Resources </option>
				<option value="CHE"> College of Human Ecology </option>
				<option value="CVM"> College of Veterenary Medicine </option>
				<option value="GS"> Graduate School </option>
			</select>

			<select name="degree" class="degreeDropdown">
				<option value="">Select Degree</option>
			</select>

			<script type="text/javascript">
				/*Javascript for the dropdown - Select College*/
				$(".collegeDropdown").change(function() {
					var col=$(this).val();
					
					/* Parameters: colDegOption - degree option for selected college
						Description: Appends degree to the 2nd dropdown
					*/
					function fillDegreeDropdown(colDegOption) {
						$.each(colDegOption, function(val, text) {
							$('.degreeDropdown').append($('<option></option>').val(val).html(text));
						});
					}
					
					/* Parameters: None
						Description: Removes all the degrees in the 2nd dropdown
					*/							
					function clearDegreeDropdown() {
						$('.degreeDropdown option:gt(0)').remove();
					}

					/* if else statements for the different colleges and courses/degrees offered in each */
					if(col == 'CA') {
						clearDegreeDropdown();
															
						var CaOption={
							BSA:'BS Agriculture',
							BSABT: 'BS Agricultural Biotechnology',
							BSFT: 'BS Food Technology'
						};
					fillDegreeDropdown(CaOption);
					} else if(col == 'CAS'){
						clearDegreeDropdown();
															
						var CasOption={
							BACA:'BA Communication Arts',
							BAS: 'BA Sociology',
							BAP: 'BA Philosophy',
							BSAM: 'BS Applied Mathematics',
							BSAP: 'BS Applied Physics',
							BSB: 'BS Biology',
							BSC: 'BS Chemistry',
							BSCS: 'BS Computer Science',
							BSM: 'BS Mathematics',
							BSMST: 'BS Mathematics and Science Teaching',
							BSS: 'BS Statistics'
						};
					fillDegreeDropdown(CasOption);
					} else if(col == 'CA-CAS'){
						clearDegreeDropdown();
															
						var CaCasOption={
							BSAC: 'BS Agricultural Chemistry'
						};
					fillDegreeDropdown(CaCasOption);
					} else if(col == 'CDC'){
						clearDegreeDropdown();
															
						var CdcOption={
							BSDC: 'BS Development Communication'
						};
					fillDegreeDropdown(CdcOption);
					} else if(col == 'CEAT'){
						clearDegreeDropdown();
															
						var CeatOption={
							BSAeng: 'BS Agricultural and Biosystems Engineering',
							BSChE: 'BS Chemical Engineering',
							BSCE: 'BS Civil Engineering',
							BSEE: 'BS Electrical Engineering',
							BSIE: 'BS Industrial Engineering'
						};
					fillDegreeDropdown(CeatOption);
					} else if(col == 'CEM'){
						clearDegreeDropdown();
															
						var CemOption={
							BSAE: 'BS Agricultural Economics',
							BSABM: 'BS Agribusiness Management',
							BSE: 'BS Economics'
						};
					fillDegreeDropdown(CemOption);
					} else if(col == 'CFNR'){
						clearDegreeDropdown();
															
						var CfnrOption={
							BSF: 'BS Forestry'
						};
					fillDegreeDropdown(CfnrOption);
					} else if(col == 'CHE'){
						clearDegreeDropdown();
															
						var CheOption={
							BSHE: 'BS Human Ecology',
							BSN: 'BS Nutrition'
						};
					fillDegreeDropdown(CheOption);
					} else if(col == 'CVM'){
						clearDegreeDropdown();
															
						var CvmOption={
							DVM: 'Doctor of Veterenary Medicine'
						};
					fillDegreeDropdown(CvmOption);
					} else if(col == 'GS'){
						clearDegreeDropdown();
															
						var GsOption={
							MSAC: 'MS Agricultural Chemistry',
							MSAE: 'MS Agricultural Economics',
							MSAEd: 'MS Agricultural Education',
							MSAg: 'MS Agrometeorology',
							MSAgr: 'MS Agronomy',
							MSAS: 'MS Animal Science',
							MSAN: 'MS Applied Nutrition',
							MSBC: 'MS Biochemistry',
							MSB: 'MS Botany',
							MSCE: 'MS Chemical Engineering',
							MSC: 'MS Chemistry',
							MSCS: 'MS Computer Science',
							MSCo: 'MS Community',
							MSD: 'MS Development',
							MSDC: 'MS Development Communication',
							MSDMG: 'MS Development Management and Governance',
							MSE: 'MS Economics',
							MSEn: 'MS Entomology',
							MSES: 'MS Environmental Science',
							MSExE: 'MS Extension Education',
							MSFRM: 'MS Family Resource Management',
							MSFS: 'MS Food Science',
							MSFBS: 'MS Forestry: Forest Biological Sciences',
							MSFRM: 'MS Forestry: Forest Resources Management',
							MSFSFI: 'MS Forestry: Silviculture and Forest Influences',
							MSFSF: 'MS Forestry: Social Forestry',
							MSGH: 'MS Genetics Horticulture',
							MSM: 'MS Mathematics',
							MSMB: 'MS Microbiology',
							MSMBB: 'MS Molecular Biology and Biotechnology',
							MSNRC: 'MS Natural Resources Conservation',
							MSPB: 'MS Plant Breeding',
							MSPGRC: 'MS Plant Genetics Resources Conservation and Management',
							MSPP: 'MS Plant Pathology',
							MSRS: 'MS Rural Sociology',
							MSSS: 'MS Social Science',
							MSS: 'MS Statistics',
							MSVM: 'MS Veterenary Medicine',
							MSWS: 'MS Wildlife Studies',
							MSZ: 'MS Zoology',
							MF: 'Master of Forestry',
							MIT: 'Master of Information Technology',
							MACA: 'MA Communication Arts',
							MAS: 'MA Sociology',
							MAg: 'Master of Agriculture',
							MMAbm: 'Master of Management - Agribusiness Management and Entrepreneurship',
							MMBM: 'Master of Management - Business Management',
							MMCM: 'Master of Management - Cooperative Management',
							PAC: 'PhD Agricultural Chemistry',
							PAE: 'PhD Agricultural Economics',
							PAEd: 'PhD Agricultural Education',
							PAEng: 'PhD Agricultural Engineering',
							PAgr: 'PhD Agronomy',
							PAS: 'PhD Animal Science',
							PBC: 'PhD Biochemistry',
							PSB: 'PhD Botany',
							PCS: 'PhD Computer Science',
							PCD: 'PhD Community Development',
							PDC: 'PhD Development Communication',
							PDS: 'PhD Development Studies',
							PEn: 'PhD Entomology',
							PES: 'PhD Environmental Science',
							PExE: 'PhD Extension Education',
							PFS: 'PhD Food Science',
							PFBS: 'PhD Forestry: Forest Biological Sciences',
							PFRM: 'PhD Forestry: Forest Resources Management',
							PFSFI: 'PhD Forestry: Silviculture and Forest Influences',
							PSFWT: 'PhD Forestry: Wood Science and Technology',
							PGH: 'PhD Genetics Horticulture',
							PHN: 'PhD Human Nutrition',
							PMB: 'PhD Microbiology',
							PMBB: 'PhD Molecular Biology and Biotechnology',
							PPB: 'PhD Plant Breeding',
							PPP: 'PhD Plant Pathology',
							PSS: 'PhD Social Science',
							PSS: 'PhD Statistics',
						};
					fillDegreeDropdown(GsOption);
					}
				});
			</script>

			<label id = 'enum'> Employee Number: </label>
				<input type="text" id="employee_number" name = "employee_number" pattern="[0-9]{9}" required/>

			<input type="submit" value="Submit"/>
		</form> 	<!-- End of form -->
		</div>
	</body>
</html>