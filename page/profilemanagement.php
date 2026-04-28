<?php
date_default_timezone_set('Asia/Manila');
require "../db/dbconnection.php";
include "modals.php";

$UserID = isset($_POST["UserID"]) ? $_POST["UserID"] : "";

$initial = execsqlSRS("
					SELECT
						[EmailAddress]
						,[LastName]
						,[FirstName]
					FROM [Sys_UserAccount]
					WHERE [UserID] = :userid
				", "Select", [
	":userid" => $UserID
]);

$userdetails = execsqlSRS("
					SELECT
						[UserDetailsID]
						,[UserID]
						,[MiddleName]
						,[ExtName]
						,[DateOfBirth]
						,[CivilStatus]
						,[Sex]
						,[Nationality]
						,[MobileNumber]
						,[TelephoneNumber]
						,[Age]
						,[Religion]
						,[HmHouse]
						,[HmStreet]
						,[HmBarangay]
						,[HmCity]
						,[HmProvince]
						,[HmZip]
						,[CurHouse]
						,[CurStreet]
						,[CurBarangay]
						,[CurCity]
						,[CurProvince]
						,[CurZip]
						,[IsActive]
						,[pds_file]
						,[workexp_file]
						,[perf_file]
						,[perf_rating]
      					,[adj_rating]
					FROM [tbl_ProfUserDetails]
					WHERE [UserID] = :userid
				", "Select", [
	":userid" => $UserID
]);

$civilStatus = $userdetails[0]["CivilStatus"] ?? "";
$statuses = ["Single", "Married", "Widowed", "Separated"];

$sexSelect = $userdetails[0]["Sex"] ?? "";
$sexes = ["Male", "Female"];

$answers = execsqlSRS("
    SELECT [question_code], [answer], [answer_details]
    FROM [tbl_ProfAnswers]
    WHERE [UserID] = :userid AND [IsActive] = 0
", "Select", [
	":userid" => $UserID
]);

$answers_assoc = [];
foreach ($answers as $row) {
	$answers_assoc[$row['question_code']] = [
		'answer' => $row['answer'],
		'details' => $row['answer_details']
	];
}
?>
<style>
	/*
#main {
   margin: 50px 0;
}
   */

	#main #sumprofile_user .card {
		border: 0;
	}

	#main #sumprofile_user .card .card-header {
		border: 0;
		-webkit-box-shadow: 0 0 20px 0 rgba(213, 213, 213, 0.5);
		box-shadow: 0 0 20px 0 rgba(213, 213, 213, 0.5);
		border-radius: 2px;
		padding: 0;
	}

	#main #sumprofile_user .card .card-header .btn-header-link {
		color: #fff;
		display: block;
		text-align: left;
		background: #17a2b8;
		/*   color: #222; */
		padding: 20px;
	}

	#main #sumprofile_user .card .card-header .btn-header-link:after {
		content: "\f107";
		font-family: 'Font Awesome 5 Free';
		font-weight: 900;
		float: right;
	}

	#main #sumprofile_user .card .card-header .btn-header-link.collapsed {
		background: #07861f;
		/*   color: #fff; */
	}

	#main #sumprofile_user .card .card-header .btn-header-link.collapsed:after {
		content: "\f106";
	}

	#main #sumprofile_user .card .collapsing {

		line-height: 30px;
	}

	#main #sumprofile_user .card .collapse {
		border: 0;
	}

	#main #sumprofile_user .card .collapse.show {

		line-height: 30px;
		/*   color: #222; */
	}

	/* Focus effect */
	.form-control:focus {
		border-color: #28a745;
		box-shadow: 0 0 0 0.15rem rgba(23, 162, 184, .15);
	}

	/* Input icons */
	.input-group-text {
		background: #f4f6f9;
		border-radius: 6px 0 0 6px;
		color: #6c757d;
	}

	/* Dark mode compatibility
.dark-mode .form-control{
    background:#2b2b2b;
    border-color:#444;
    color:#fff;
}

.dark-mode .input-group-text{
    background:#3a3a3a;
    border-color:#444;
    color:#ddd;
}
*/
</style>

<div id="main">
	<div class="d-flex justify-content-center pt-2 pb-1">
		<h2>Summary Profile</h2>
	</div>

	<div id="infomacros" class='ml-3'>
	</div>

	<div class="card ml-3 mr-3">
		<div class="accordion" id="sumprofile_user" style="min-width:90%">

			<div class="card mb-1" id="personaldetailsdiv">
				<div class="card-header" id="acchead_personal">
					<a class="btn btn-header-link collapsed"
						data-toggle="collapse"
						data-target="#accbody_personal"
						aria-expanded="false"
						aria-controls="accbody_personal">
						<span class="font-weight-bold">Personal Information</span>
					</a>
				</div>

				<div id="accbody_personal"
					class="collapse"
					aria-labelledby="acchead_personal"
					data-parent="#sumprofile_user">

					<div class="card-body">

						<!-- PERSONAL INFORMATION -->
						<h6 class="text-success font-weight-bold border-bottom pb-2 mb-3">
							<i class="fas fa-user mr-2"></i>Personal Information
						</h6>

						<div class="row">

							<!-- LEFT COLUMN -->
							<div class="col-md-6">

								<div class="form-group">
									<label>Last Name <span class="text-danger">*</span></label>
									<div class="input-group">
										<div class="input-group-prepend">
											<span class="input-group-text"><i class="fas fa-user"></i></span>
										</div>
										<input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo $initial[0]["LastName"] ?? ""; ?>">
									</div>
								</div>

								<div class="form-group">
									<label>First Name <span class="text-danger">*</span></label>
									<div class="input-group">
										<div class="input-group-prepend">
											<span class="input-group-text"><i class="fas fa-user"></i></span>
										</div>
										<input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo $initial[0]["FirstName"] ?? ""; ?>">
									</div>
								</div>

								<div class="form-group">
									<label>Middle Name <span class="text-danger">*</span></label>
									<div class="input-group">
										<div class="input-group-prepend">
											<span class="input-group-text"><i class="fas fa-user"></i></span>
										</div>
										<input type="text" class="form-control" id="middle_name" name="middle_name" value="<?php echo $userdetails[0]["MiddleName"] ?? ""; ?>">
									</div>
								</div>

								<div class="form-group">
									<label>Extension <span class="text-danger">* (n/a if not applicable)</span></label>
									<div class="input-group">
										<div class="input-group-prepend">
											<span class="input-group-text"><i class="fas fa-id-badge"></i></span>
										</div>
										<input type="text" class="form-control" id="extension_name" name="extension_name" placeholder="Jr., Sr., III" value="<?php echo $userdetails[0]["ExtName"] ?? ""; ?>">
									</div>
								</div>

								<div class="form-group">
									<label>Date of Birth <span class="text-danger">* (mm/dd/yyyy)</span></label>
									<div class="input-group">
										<div class="input-group-prepend">
											<span class="input-group-text"><i class="fas fa-birthday-cake"></i></span>
										</div>
										<input type="date" class="form-control" id="birth_date" name="birth_date" value="<?php echo $userdetails[0]["DateOfBirth"] ?? ""; ?>">
									</div>
								</div>

								<div class="form-group">
									<label>Civil Status <span class="text-danger">*</span></label>
									<div class="input-group">
										<div class="input-group-prepend">
											<span class="input-group-text"><i class="fas fa-heart"></i></span>
										</div>
										<select class="form-control" id="civil_status" name="civil_status">
											<option value="" <?= $civilStatus === "" ? "selected" : "" ?>>Select Status</option>
											<hr>
											<?php foreach ($statuses as $status): ?>
												<option value="<?= $status ?>" <?= $civilStatus === $status ? "selected" : "" ?>>
													<?= $status ?>
												</option>
											<?php endforeach; ?>
										</select>
									</div>
								</div>

							</div>

							<!-- RIGHT COLUMN -->
							<div class="col-md-6">

								<div class="form-group">
									<label>Sex <span class="text-danger">*</span></label>
									<div class="input-group">
										<div class="input-group-prepend">
											<span class="input-group-text"><i class="fas fa-venus-mars"></i></span>
										</div>
										<select class="form-control" id="sex" name="sex">
											<option value="" <?= $sexSelect === "" ? "selected" : "" ?>>Select Sex</option>
											<hr>
											<?php foreach ($sexes as $sex): ?>
												<option value="<?= $sex ?>" <?= $sexSelect === $sex ? "selected" : "" ?>>
													<?= $sex ?>
												</option>
											<?php endforeach; ?>
										</select>
									</div>
								</div>

								<div class="form-group">
									<label>Nationality <span class="text-danger">*</span></label>
									<div class="input-group">
										<div class="input-group-prepend">
											<span class="input-group-text"><i class="fas fa-flag"></i></span>
										</div>
										<input type="text" class="form-control" id="nationality" name="nationality" placeholder="e.g. Filipino" value="<?php echo $userdetails[0]["Nationality"] ?? ""; ?>">
									</div>
								</div>

								<div class="form-group">
									<label>Email Address <span class="text-danger">*</span></label>
									<div class="input-group">
										<div class="input-group-prepend">
											<span class="input-group-text"><i class="fas fa-envelope"></i></span>
										</div>
										<input type="email" class="form-control" id="email_address" name="email_address" value="<?php echo $initial[0]["EmailAddress"] ?? ""; ?>">
									</div>
								</div>

								<div class="form-group">
									<label>Mobile Number <span class="text-danger">*</span></label>
									<div class="input-group">
										<div class="input-group-prepend">
											<span class="input-group-text"><i class="fas fa-mobile-alt"></i></span>
										</div>
										<input type="text" class="form-control" id="mobile_number" name="mobile_number" value="<?php echo $userdetails[0]["MobileNumber"] ?? ""; ?>">
									</div>
								</div>

								<div class="form-group">
									<label>Telephone Number</label>
									<div class="input-group">
										<div class="input-group-prepend">
											<span class="input-group-text"><i class="fas fa-phone"></i></span>
										</div>
										<input type="text" class="form-control" id="telephone_number" name="telephone_number" value="<?php echo $userdetails[0]["TelephoneNumber"] ?? ""; ?>">
									</div>
								</div>

								<div class="form-group">
									<label>Religion <span class="text-danger">*</span></label>
									<div class="input-group">
										<div class="input-group-prepend">
											<span class="input-group-text"><i class="fas fa-cross"></i></span>
										</div>
										<input type="text" class="form-control" id="religion" name="religion" value="<?php echo $userdetails[0]["Religion"] ?? ""; ?>">
									</div>
								</div>

							</div>

						</div>


						<!-- HOME ADDRESS -->
						<h6 class="text-success font-weight-bold border-bottom pb-2 mt-4 mb-3">
							<i class="fas fa-home mr-2"></i>Home Address
						</h6>

						<div class="row">

							<div class="col-md-3">
								<div class="form-group">
									<label>House No.</label>
									<input type="text" class="form-control" id="home_house" value="<?php echo $userdetails[0]["HmHouse"] ?? ""; ?>">
								</div>
							</div>

							<div class="col-md-3">
								<div class="form-group">
									<label>Street</label>
									<input type="text" class="form-control" id="home_street" value="<?php echo $userdetails[0]["HmStreet"] ?? ""; ?>">
								</div>
							</div>

							<div class="col-md-3">
								<div class="form-group">
									<label>Barangay</label>
									<input type="text" class="form-control" id="home_barangay" value="<?php echo $userdetails[0]["HmBarangay"] ?? ""; ?>">
								</div>
							</div>

							<div class="col-md-3">
								<div class="form-group">
									<label>City/Municipality <span class="text-danger">*</span></label>
									<input type="text" class="form-control" id="home_city" value="<?php echo $userdetails[0]["HmCity"] ?? ""; ?>">
								</div>
							</div>

							<div class="col-md-3">
								<div class="form-group">
									<label>Province <span class="text-danger">*</span></label>
									<input type="text" class="form-control" id="home_province" value="<?php echo $userdetails[0]["HmProvince"] ?? ""; ?>">
								</div>
							</div>

							<div class="col-md-3">
								<div class="form-group">
									<label>Zip Code <span class="text-danger">*</span></label>
									<input type="text" class="form-control" id="home_zip" value="<?php echo $userdetails[0]["HmZip"] ?? ""; ?>">
								</div>
							</div>

						</div>


						<!-- CURRENT ADDRESS -->
						<h6 class="text-success font-weight-bold border-bottom pb-2 mt-4 mb-3">
							<i class="fas fa-map-marker-alt mr-2"></i>Current Address
						</h6>

						<div class="row">

							<div class="col-md-3">
								<div class="form-group">
									<label>House No.</label>
									<input type="text" class="form-control" id="curr_house" value="<?php echo $userdetails[0]["CurHouse"] ?? ""; ?>">
								</div>
							</div>

							<div class="col-md-3">
								<div class="form-group">
									<label>Street</label>
									<input type="text" class="form-control" id="curr_street" value="<?php echo $userdetails[0]["CurStreet"] ?? ""; ?>">
								</div>
							</div>

							<div class="col-md-3">
								<div class="form-group">
									<label>Barangay</label>
									<input type="text" class="form-control" id="curr_barangay" value="<?php echo $userdetails[0]["CurBarangay"] ?? ""; ?>">
								</div>
							</div>

							<div class="col-md-3">
								<div class="form-group">
									<label>City/Municipality <span class="text-danger">*</span></label>
									<input type="text" class="form-control" id="curr_city" value="<?php echo $userdetails[0]["CurCity"] ?? ""; ?>">
								</div>
							</div>

							<div class="col-md-3">
								<div class="form-group">
									<label>Province <span class="text-danger">*</span></label>
									<input type="text" class="form-control" id="curr_province" value="<?php echo $userdetails[0]["CurProvince"] ?? ""; ?>">
								</div>
							</div>

							<div class="col-md-3">
								<div class="form-group">
									<label>Zip Code <span class="text-danger">*</span></label>
									<input type="text" class="form-control" id="curr_zip" value="<?php echo $userdetails[0]["CurZip"] ?? ""; ?>">
								</div>
							</div>

						</div>

						<div class="d-flex justify-content-center mt-3">
							<button class="btn btn-success"
								type="button"
								id="save_personaldetails"
								data-datavalue="<?php echo $UserID; ?>">
								Save Changes <i class="fa-solid fa-floppy-disk"></i>
							</button>
						</div>

					</div>
				</div>



			</div>

			<div class="card mb-1">
				<div class="card-header" id="acchead_edu">
					<a href="#" class="btn btn-header-link collapsed" data-toggle="collapse" data-target="#accbody_edu"
						aria-expanded="true" aria-controls="accbody_edu"><span class="font-weight-bold">Educational Background</span></a>
				</div>

				<div id="accbody_edu" class="collapse" aria-labelledby="acchead_edu" data-parent="#sumprofile_user">

					<button
						type="button"
						class="btn btn-success ml-3 mt-3"
						id="add_data"
						data-openmodal="#addeditmodal"
						data-openmodallabel="Add Educational Background"
						data-openmodalbody="#addeditcontent"
						data-backendurl="backend/bk_profilemanagement.php"
						data-backendrequest="addeducation">
						Add Educational Background <i class="fa-solid fa-plus"></i>
					</button>

					<div class="card-body" id="educationbody">

					</div>

				</div>
			</div>


			<div class="card mb-1">
				<div class="card-header" id="acchead_eligibility">
					<a href="#" class="btn btn-header-link collapsed" data-toggle="collapse" data-target="#accbody_eligibility"
						aria-expanded="true" aria-controls="accbody_eligibility"><span class="font-weight-bold">Eligibility</span></a>
				</div>
				<div id="accbody_eligibility" class="collapse" aria-labelledby="acchead_eligibility" data-parent="#sumprofile_user">
					<button
						type="button"
						class="btn btn-success ml-3 mt-3"
						id="add_data"
						data-openmodal="#addeditmodal"
						data-openmodallabel="Add Eligibility"
						data-openmodalbody="#addeditcontent"
						data-backendurl="backend/bk_profilemanagement.php"
						data-backendrequest="addeligibility">
						Add Eligibility <i class="fa-solid fa-plus"></i>
					</button>
					<div class="card-body" id="eligibilitybody">

					</div>
				</div>
			</div>

			<div class="card mb-1">
				<div class="card-header" id="acchead_exp">
					<a href="#" class="btn btn-header-link collapsed" data-toggle="collapse" data-target="#accbody_exp"
						aria-expanded="true" aria-controls="accbody_exp"><span class="font-weight-bold">Work Experience</span></a>
				</div>
				<div id="accbody_exp" class="collapse" aria-labelledby="acchead_exp" data-parent="#sumprofile_user">
					<button
						type="button"
						class="btn btn-success ml-3 mt-3"
						id="add_data"
						data-openmodal="#addeditmodal"
						data-openmodallabel="Add Work Experience"
						data-openmodalbody="#addeditcontent"
						data-backendurl="backend/bk_profilemanagement.php"
						data-backendrequest="addworkexperience">
						Add Work Experience <i class="fa-solid fa-plus"></i>
					</button>
					<div class="card-body" id="workexperiencebody">

					</div>
				</div>
			</div>

			<div class="card mb-1">
				<div class="card-header" id="acchead_voluntary">
					<a href="#" class="btn btn-header-link collapsed" data-toggle="collapse" data-target="#accbody_voluntary"
						aria-expanded="true" aria-controls="accbody_voluntary"><span class="font-weight-bold">Voluntary Work (Civic, Non-Government, People, Voluntary Organizations)</span></a>
				</div>
				<div id="accbody_voluntary" class="collapse" aria-labelledby="acchead_voluntary" data-parent="#sumprofile_user">
					<button
						type="button"
						class="btn btn-success ml-3 mt-3"
						id="add_data"
						data-openmodal="#addeditmodal"
						data-openmodallabel="Add Voluntary Work"
						data-openmodalbody="#addeditcontent"
						data-backendurl="backend/bk_profilemanagement.php"
						data-backendrequest="addvoluntarywork">
						Add Voluntary Work <i class="fa-solid fa-plus"></i>
					</button>
					<div class="card-body" id="voluntarybody">

					</div>
				</div>
			</div>

			<div class="card mb-1">
				<div class="card-header" id="acchead_learning">
					<a href="#" class="btn btn-header-link collapsed" data-toggle="collapse" data-target="#accbody_learning"
						aria-expanded="true" aria-controls="accbody_learning"><span class="font-weight-bold">Learning and Development</span></a>
				</div>
				<div id="accbody_learning" class="collapse" aria-labelledby="acchead_learning" data-parent="#sumprofile_user">
					<button
						type="button"
						class="btn btn-success ml-3 mt-3"
						id="add_data"
						data-openmodal="#addeditmodal"
						data-openmodallabel="Add L&D Training"
						data-openmodalbody="#addeditcontent"
						data-backendurl="backend/bk_profilemanagement.php"
						data-backendrequest="addlearningdevelopment">
						Add L&D Training <i class="fa-solid fa-plus"></i>
					</button>
					<div class="card-body" id="learningdevelopmentbody">

					</div>
				</div>
			</div>

			<div class="card mb-1">
				<div class="card-header" id="acchead_other">
					<a href="#" class="btn btn-header-link collapsed" data-toggle="collapse" data-target="#accbody_other"
						aria-expanded="true" aria-controls="accbody_other"><span class="font-weight-bold">Other Information</span></a>
				</div>
				<div id="accbody_other" class="collapse" aria-labelledby="acchead_other" data-parent="#sumprofile_user">
					<div class="card-body">

						<!-- UPPER PART -->
						<div class="row">

							<!-- LEFT COLUMN: SKILLS -->
							<div class="col-md-6 col-sm-12">

								<div class="form-group">
									<label for="skill_input">Add a Skill / Hobby</label>

									<div class="input-group" style="max-width: 500px;">

										<input type="text"
											class="form-control"
											id="skill_input"
											data-backendurl="backend/bk_profilemanagement.php"
											data-backendrequest="saveskill"
											placeholder="Skills / Hobbies...">

										<div class="input-group-append">
											<button class="btn btn-success" id="add_skill_btn" type="button" data-tooltip="Save Skill">
												<i class="fas fa-plus"></i>
											</button>
										</div>

									</div>
								</div>

								<ul id="skills_list" class="list-group list-group-flush mb-3" style="max-width: 500px;"></ul>

							</div>

							<!-- RIGHT COLUMN: COMPTETENCY -->
							<div class="col-md-6 col-sm-12">

								<div class="form-group">
									<label for="comp_input">Add a Competency</label>
									<div class="input-group" style="max-width: 500px;">

										<input type="text"
											class="form-control"
											id="comp_input"
											data-backendurl="backend/bk_profilemanagement.php"
											data-backendrequest="savecomp"
											placeholder="Comptetencies...">

										<div class="input-group-append">
											<button class="btn btn-success" id="add_comp_btn" type="button" data-tooltip="Save">
												<i class="fas fa-plus"></i>
											</button>
										</div>

									</div>
								</div>

								<ul id="comp_list" class="list-group list-group-flush mb-3" style="max-width: 500px;"></ul>
							</div>


							<!-- LEFT COLUMN: MEMBERSHIP IN ASSOCIATION/ORGANIZATION -->
							<div class="col-md-6 col-sm-12">

								<div class="form-group">
									<label for="membership_input">Add Membership in Association/Organization</label>
									<small id="membership_file_name" class="form-text text-danger" style="max-width: 500px;">
										No file selected
									</small>
									<div class="input-group" style="max-width: 500px;">

										<input type="text"
											class="form-control"
											id="membership_input"
											data-backendurl="backend/bk_profilemanagement.php"
											data-backendrequest="savemembership"
											placeholder="Membership...">

										<div class="input-group-append">
											<label class="btn btn-info mb-0" data-tooltip="Supporting Document">
												<i class="fas fa-paperclip"></i>
												<input type="file" id="membership_file" hidden>
											</label>
										</div>

										<div class="input-group-append">
											<button class="btn btn-success" id="add_membership_btn" type="button" data-tooltip="Save">
												<i class="fas fa-plus"></i>
											</button>
										</div>

									</div>
								</div>

								<ul id="membership_list" class="list-group list-group-flush mb-3" style="max-width: 500px;"></ul>
							</div>


							<!-- LEFT COLUMN: NON-ACADEMIC -->
							<div class="col-md-6 col-sm-12">

								<div class="form-group">
									<label for="ndr_input">Add Non-Academic Distinction/Recognition</label>
									<small id="ndr_file_name" class="form-text text-danger" style="max-width: 500px;">
										No file selected
									</small>

									<div class="input-group" style="max-width: 500px;">

										<input type="text"
											class="form-control"
											id="ndr_input"
											data-backendurl="backend/bk_profilemanagement.php"
											data-backendrequest="savenonacademic"
											placeholder="Non-Academic Distinction/Recognition...">

										<div class="input-group-append">
											<label class="btn btn-info mb-0" data-tooltip="Supporting Document">
												<i class="fas fa-paperclip"></i>
												<input type="file" id="ndr_file" hidden>
											</label>
										</div>

										<div class="input-group-append">
											<button class="btn btn-success" id="add_ndr_btn" type="button" data-tooltip="Save">
												<i class="fas fa-plus"></i>
											</button>
										</div>
									</div>
								</div>

								<ul id="ndr_list" class="list-group list-group-flush mb-3" style="max-width: 500px;"></ul>
							</div>

						</div>
						<!-- UPPER PART END -->

						<!-- =================================================================================================================================== -->
						<hr class="my-4" style="height:2px; background-color:#343a40;">
						<div style="border:2px solid green;">
							<div class="m-3">

								<!-- Question 34 -->
								<div class="form-group">
									<label>Are you related by consanguinity or affinity to the appointing or recommending authority, or to the chief of bureau or office or to the person who has immediate supervision over you in the Office, Bureau or Department where you will be appointed</label>
									<div class="ml-3">
										a. Within the third degree? <span class='font-weight-bold text-danger'>*</span>
										<div class="form-check">
											<input class="form-check-input question-yesno" type="radio" name="q34a" value="Yes" data-target="#q34aDetails"
												<?php echo (isset($answers_assoc['q34a']) && $answers_assoc['q34a']['answer'] === 'Yes') ? 'checked' : ''; ?>>
											<label class="form-check-label">Yes</label>
										</div>
										<div class="form-check">
											<input class="form-check-input question-yesno" type="radio" name="q34a" value="No" data-target="#q34aDetails"
												<?php echo (isset($answers_assoc['q34a']) && $answers_assoc['q34a']['answer'] === 'No') ? 'checked' : ''; ?>>
											<label class="form-check-label">No</label>
										</div>
										<div class="ml-3 mt-1 <?php echo (isset($answers_assoc['q34a']) && $answers_assoc['q34a']['answer'] === 'Yes') ? '' : 'd-none'; ?>" id="q34aDetails">
											<input type="text" class="form-control form-control-sm w-50" placeholder="Give details:"
												value="<?php echo isset($answers_assoc['q34a']) ? htmlspecialchars($answers_assoc['q34a']['details']) : ''; ?>">
										</div>

										b. Within the fourth degree (for Local Government Unit - Career Employees)? <span class='font-weight-bold text-danger'>*</span>
										<div class="form-check">
											<input class="form-check-input question-yesno" type="radio" name="q34b" value="Yes" data-target="#q34bDetails"
												<?php echo (isset($answers_assoc['q34b']) && $answers_assoc['q34b']['answer'] === 'Yes') ? 'checked' : ''; ?>>
											<label class="form-check-label">Yes</label>
										</div>
										<div class="form-check">
											<input class="form-check-input question-yesno" type="radio" name="q34b" value="No" data-target="#q34bDetails"
												<?php echo (isset($answers_assoc['q34b']) && $answers_assoc['q34b']['answer'] === 'No') ? 'checked' : ''; ?>>
											<label class="form-check-label">No</label>
										</div>
										<div class="ml-3 mt-1 <?php echo (isset($answers_assoc['q34b']) && $answers_assoc['q34b']['answer'] === 'Yes') ? '' : 'd-none'; ?>" id="q34bDetails">
											<input type="text" class="form-control form-control-sm w-50" placeholder="Give details:"
												value="<?php echo isset($answers_assoc['q34b']) ? htmlspecialchars($answers_assoc['q34b']['details']) : ''; ?>">
										</div>
									</div>
								</div>

								<!-- Question 35 -->
								<div class="form-group">
									<label>Have you ever been:</label>
									<div class="ml-3">
										a. Found guilty of any administrative offense? <span class='font-weight-bold text-danger'>*</span>
										<div class="form-check">
											<input class="form-check-input question-yesno" type="radio" name="q35a" value="Yes" data-target="#q35aDetails"
												<?php echo (isset($answers_assoc['q35a']) && $answers_assoc['q35a']['answer'] === 'Yes') ? 'checked' : ''; ?>>
											<label class="form-check-label">Yes</label>
										</div>
										<div class="form-check">
											<input class="form-check-input question-yesno" type="radio" name="q35a" value="No" data-target="#q35aDetails"
												<?php echo (isset($answers_assoc['q35a']) && $answers_assoc['q35a']['answer'] === 'No') ? 'checked' : ''; ?>>
											<label class="form-check-label">No</label>
										</div>
										<div class="ml-3 mt-1 <?php echo (isset($answers_assoc['q35a']) && $answers_assoc['q35a']['answer'] === 'Yes') ? '' : 'd-none'; ?>" id="q35aDetails">
											<input type="text" class="form-control form-control-sm w-50" placeholder="Give details:"
												value="<?php echo isset($answers_assoc['q35a']) ? htmlspecialchars($answers_assoc['q35a']['details']) : ''; ?>">
										</div>

										b. Criminally charged before any court? <span class='font-weight-bold text-danger'>*</span>
										<?php
										$q35b_date = '';
										$q35b_status = '';
										if (isset($answers_assoc['q35b']) && $answers_assoc['q35b']['answer'] === 'Yes') {
											$details = $answers_assoc['q35b']['details'];
											if ($details) {
												$parsed = json_decode($details, true);
												if ($parsed) {
													$q35b_date = $parsed['date_filed'] ?? '';
													$q35b_status = $parsed['status'] ?? '';
												}
											}
										}
										?>
										<div class="form-check">
											<input class="form-check-input question-yesno" type="radio" name="q35b" value="Yes" data-target="#q35bDetails"
												<?php echo (isset($answers_assoc['q35b']) && $answers_assoc['q35b']['answer'] === 'Yes') ? 'checked' : ''; ?>>
											<label class="form-check-label">Yes</label>
										</div>
										<div class="form-check">
											<input class="form-check-input question-yesno" type="radio" name="q35b" value="No" data-target="#q35bDetails"
												<?php echo (isset($answers_assoc['q35b']) && $answers_assoc['q35b']['answer'] === 'No') ? 'checked' : ''; ?>>
											<label class="form-check-label">No</label>
										</div>
										<div class="ml-3 mt-1 <?php echo (isset($answers_assoc['q35b']) && $answers_assoc['q35b']['answer'] === 'Yes') ? '' : 'd-none'; ?>" id="q35bDetails">
											<input type="text" class="form-control form-control-sm w-50 mb-1" placeholder="Date filed:" value="<?php echo htmlspecialchars($q35b_date); ?>">
											<input type="text" class="form-control form-control-sm w-50" placeholder="Status of Case/s:" value="<?php echo htmlspecialchars($q35b_status); ?>">
										</div>
									</div>
								</div>

								<!-- Question 36 -->
								<div class="form-group">
									<label>Have you ever been convicted of any crime or violation of any law, decree, ordinance or regulation by any court or tribunal? <span class='font-weight-bold text-danger'>*</span></label>
									<div class="ml-3">
										<div class="form-check">
											<input class="form-check-input question-yesno" type="radio" name="q36" value="Yes" data-target="#q36Details"
												<?php echo (isset($answers_assoc['q36']) && $answers_assoc['q36']['answer'] === 'Yes') ? 'checked' : ''; ?>>
											<label class="form-check-label">Yes</label>
										</div>
										<div class="form-check">
											<input class="form-check-input question-yesno" type="radio" name="q36" value="No" data-target="#q36Details"
												<?php echo (isset($answers_assoc['q36']) && $answers_assoc['q36']['answer'] === 'No') ? 'checked' : ''; ?>>
											<label class="form-check-label">No</label>
										</div>
									</div>
									<div class="ml-3 mt-1 <?php echo (isset($answers_assoc['q36']) && $answers_assoc['q36']['answer'] === 'Yes') ? '' : 'd-none'; ?>" id="q36Details">
										<input type="text" class="form-control form-control-sm w-50" placeholder="Give details:"
											value="<?php echo isset($answers_assoc['q36']) ? htmlspecialchars($answers_assoc['q36']['details']) : ''; ?>">
									</div>
								</div>

								<!-- Question 37 -->
								<div class="form-group">
									<label>Have you ever been separated from the service in any of the following modes: resignation, retirement, dropped from the rolls, dismissal, termination, end of term, finished contract or phased out (abolition) in the public or private sector? <span class='font-weight-bold text-danger'>*</span></label>
									<div class="ml-3">
										<div class="form-check">
											<input class="form-check-input question-yesno" type="radio" name="q37" value="Yes" data-target="#q37Details"
												<?php echo (isset($answers_assoc['q37']) && $answers_assoc['q37']['answer'] === 'Yes') ? 'checked' : ''; ?>>
											<label class="form-check-label">Yes</label>
										</div>
										<div class="form-check">
											<input class="form-check-input question-yesno" type="radio" name="q37" value="No" data-target="#q37Details"
												<?php echo (isset($answers_assoc['q37']) && $answers_assoc['q37']['answer'] === 'No') ? 'checked' : ''; ?>>
											<label class="form-check-label">No</label>
										</div>
									</div>
									<div class="ml-3 mt-1 <?php echo (isset($answers_assoc['q37']) && $answers_assoc['q37']['answer'] === 'Yes') ? '' : 'd-none'; ?>" id="q37Details">
										<input type="text" class="form-control form-control-sm w-50" placeholder="Give details:"
											value="<?php echo isset($answers_assoc['q37']) ? htmlspecialchars($answers_assoc['q37']['details']) : ''; ?>">
									</div>
								</div>

								<!-- Question 38 -->
								<div class="form-group">
									<label>Have you:</label>
									<div class="ml-3">
										a. Ever been a candidate in a national or local election held within the last year (except Barangay election)? <span class='font-weight-bold text-danger'>*</span>
										<div class="form-check">
											<input class="form-check-input question-yesno" type="radio" name="q38a" value="Yes" data-target="#q38aDetails"
												<?php echo (isset($answers_assoc['q38a']) && $answers_assoc['q38a']['answer'] === 'Yes') ? 'checked' : ''; ?>>
											<label class="form-check-label">Yes</label>
										</div>
										<div class="form-check">
											<input class="form-check-input question-yesno" type="radio" name="q38a" value="No" data-target="#q38aDetails"
												<?php echo (isset($answers_assoc['q38a']) && $answers_assoc['q38a']['answer'] === 'No') ? 'checked' : ''; ?>>
											<label class="form-check-label">No</label>
										</div>
										<div class="ml-3 mt-1 <?php echo (isset($answers_assoc['q38a']) && $answers_assoc['q38a']['answer'] === 'Yes') ? '' : 'd-none'; ?>" id="q38aDetails">
											<input type="text" class="form-control form-control-sm w-50" placeholder="Give details:"
												value="<?php echo isset($answers_assoc['q38a']) ? htmlspecialchars($answers_assoc['q38a']['details']) : ''; ?>">
										</div>

										b. Resigned from the government service during the three (3)-month period before the last election to promote/actively campaign for a national or local candidate? <span class='font-weight-bold text-danger'>*</span>
										<div class="form-check">
											<input class="form-check-input question-yesno" type="radio" name="q38b" value="Yes" data-target="#q38bDetails"
												<?php echo (isset($answers_assoc['q38b']) && $answers_assoc['q38b']['answer'] === 'Yes') ? 'checked' : ''; ?>>
											<label class="form-check-label">Yes</label>
										</div>
										<div class="form-check">
											<input class="form-check-input question-yesno" type="radio" name="q38b" value="No" data-target="#q38bDetails"
												<?php echo (isset($answers_assoc['q38b']) && $answers_assoc['q38b']['answer'] === 'No') ? 'checked' : ''; ?>>
											<label class="form-check-label">No</label>
										</div>
										<div class="ml-3 mt-1 <?php echo (isset($answers_assoc['q38b']) && $answers_assoc['q38b']['answer'] === 'Yes') ? '' : 'd-none'; ?>" id="q38bDetails">
											<input type="text" class="form-control form-control-sm w-50" placeholder="Give details:"
												value="<?php echo isset($answers_assoc['q38b']) ? htmlspecialchars($answers_assoc['q38b']['details']) : ''; ?>">
										</div>
									</div>
								</div>

								<!-- Question 39 -->
								<div class="form-group">
									<label>Have you acquired the status of an immigrant or permanent resident of another country? <span class='font-weight-bold text-danger'>*</span></label>
									<div class="ml-3">
										<div class="form-check">
											<input class="form-check-input question-yesno" type="radio" name="q39" value="Yes" data-target="#q39Details"
												<?php echo (isset($answers_assoc['q39']) && $answers_assoc['q39']['answer'] === 'Yes') ? 'checked' : ''; ?>>
											<label class="form-check-label">Yes</label>
										</div>
										<div class="form-check">
											<input class="form-check-input question-yesno" type="radio" name="q39" value="No" data-target="#q39Details"
												<?php echo (isset($answers_assoc['q39']) && $answers_assoc['q39']['answer'] === 'No') ? 'checked' : ''; ?>>
											<label class="form-check-label">No</label>
										</div>
									</div>
									<div class="ml-3 mt-1 <?php echo (isset($answers_assoc['q39']) && $answers_assoc['q39']['answer'] === 'Yes') ? '' : 'd-none'; ?>" id="q39Details">
										<input type="text" class="form-control form-control-sm w-50" placeholder="Give details (country):"
											value="<?php echo isset($answers_assoc['q39']) ? htmlspecialchars($answers_assoc['q39']['details']) : ''; ?>">
									</div>
								</div>

								<!-- Question 40 -->
								<div class="form-group">
									<label>Pursuant to: (a) Indigenous People's Act (RA 8371); (b) Magna Carta for Disabled Persons (RA 7277, as amended); and (c) Expanded Solo Parents Welfare Act (RA 11861), please answer the following items:</label>
									<div class="ml-3">
										Are you a member of any indigenous group? <span class='font-weight-bold text-danger'>*</span>
										<div class="form-check">
											<input class="form-check-input question-yesno" type="radio" name="q40a" value="Yes" data-target="#q40aDetails"
												<?php echo (isset($answers_assoc['q40a']) && $answers_assoc['q40a']['answer'] === 'Yes') ? 'checked' : ''; ?>>
											<label class="form-check-label">Yes</label>
										</div>
										<div class="form-check">
											<input class="form-check-input question-yesno" type="radio" name="q40a" value="No" data-target="#q40aDetails"
												<?php echo (isset($answers_assoc['q40a']) && $answers_assoc['q40a']['answer'] === 'No') ? 'checked' : ''; ?>>
											<label class="form-check-label">No</label>
										</div>
										<div class="ml-3 mt-1 <?php echo (isset($answers_assoc['q40a']) && $answers_assoc['q40a']['answer'] === 'Yes') ? '' : 'd-none'; ?>" id="q40aDetails">
											<input type="text" class="form-control form-control-sm w-50" placeholder="Please specify:"
												value="<?php echo isset($answers_assoc['q40a']) ? htmlspecialchars($answers_assoc['q40a']['details']) : ''; ?>">
										</div>

										Are you a person with disability? <span class='font-weight-bold text-danger'>*</span>
										<div class="form-check">
											<input class="form-check-input question-yesno" type="radio" name="q40b" value="Yes" data-target="#q40bDetails"
												<?php echo (isset($answers_assoc['q40b']) && $answers_assoc['q40b']['answer'] === 'Yes') ? 'checked' : ''; ?>>
											<label class="form-check-label">Yes</label>
										</div>
										<div class="form-check">
											<input class="form-check-input question-yesno" type="radio" name="q40b" value="No" data-target="#q40bDetails"
												<?php echo (isset($answers_assoc['q40b']) && $answers_assoc['q40b']['answer'] === 'No') ? 'checked' : ''; ?>>
											<label class="form-check-label">No</label>
										</div>
										<div class="ml-3 mt-1 <?php echo (isset($answers_assoc['q40b']) && $answers_assoc['q40b']['answer'] === 'Yes') ? '' : 'd-none'; ?>" id="q40bDetails">
											<input type="text" class="form-control form-control-sm w-50" placeholder="Please specify ID no:"
												value="<?php echo isset($answers_assoc['q40b']) ? htmlspecialchars($answers_assoc['q40b']['details']) : ''; ?>">
										</div>

										Are you a solo parent? <span class='font-weight-bold text-danger'>*</span>
										<div class="form-check">
											<input class="form-check-input question-yesno" type="radio" name="q40c" value="Yes" data-target="#q40cDetails"
												<?php echo (isset($answers_assoc['q40c']) && $answers_assoc['q40c']['answer'] === 'Yes') ? 'checked' : ''; ?>>
											<label class="form-check-label">Yes</label>
										</div>
										<div class="form-check">
											<input class="form-check-input question-yesno" type="radio" name="q40c" value="No" data-target="#q40cDetails"
												<?php echo (isset($answers_assoc['q40c']) && $answers_assoc['q40c']['answer'] === 'No') ? 'checked' : ''; ?>>
											<label class="form-check-label">No</label>
										</div>
										<div class="ml-3 mt-1 <?php echo (isset($answers_assoc['q40c']) && $answers_assoc['q40c']['answer'] === 'Yes') ? '' : 'd-none'; ?>" id="q40cDetails">
											<input type="text" class="form-control form-control-sm w-50" placeholder="Please specify ID no:"
												value="<?php echo isset($answers_assoc['q40c']) ? htmlspecialchars($answers_assoc['q40c']['details']) : ''; ?>">
										</div>
										<!-- q40d -->
										<div class="form-group mt-2">
											Pregnant? <span class='font-weight-bold text-danger'>*</span>
											<div class="form-check">
												<input class="form-check-input" type="radio" name="q40d" value="Yes"
													<?php echo (isset($answers_assoc['q40d']) && $answers_assoc['q40d']['answer'] === 'Yes') ? 'checked' : ''; ?>>
												<label class="form-check-label">Yes</label>
											</div>

											<div class="form-check">
												<input class="form-check-input" type="radio" name="q40d" value="No"
													<?php echo (!isset($answers_assoc['q40d']) || $answers_assoc['q40d']['answer'] === 'No') ? 'checked' : ''; ?>>
												<label class="form-check-label">No</label>
											</div>
										</div>

										<!-- q40e -->
										<div class="form-group mt-2">
											Senior Citizen? <span class='font-weight-bold text-danger'>*</span>
											<div class="form-check">
												<input class="form-check-input" type="radio" name="q40e" value="Yes"
													<?php echo (isset($answers_assoc['q40e']) && $answers_assoc['q40e']['answer'] === 'Yes') ? 'checked' : ''; ?>>
												<label class="form-check-label">Yes</label>
											</div>

											<div class="form-check">
												<input class="form-check-input" type="radio" name="q40e" value="No"
													<?php echo (!isset($answers_assoc['q40e']) || $answers_assoc['q40e']['answer'] === 'No') ? 'checked' : ''; ?>>
												<label class="form-check-label">No</label>
											</div>
										</div>

									</div>
								</div>

								<div class="mt-3 mb-3">
									<button type="button"
										class="btn btn-success"
										id="save_answers"
										data-backendurl="backend/bk_profilemanagement.php"
										data-backendrequest="saveanswers">
										Save Answers <i class="fa-solid fa-floppy-disk"></i>
									</button>
								</div>

								<!-- File Uploads -->
								<hr class="my-4" style="height:2px; background-color:#343a40;">

								<!-- PDS File -->
								<div class="form-group" id="pds_file_div">
									<label for="pds_file">Personal Data Sheet (CS Form No. 212, revised 2017) <span class='font-weight-bold text-danger'>*</span></label>
									<?php
									if (!empty($userdetails[0]["pds_file"])) {
										echo '<button 	class="btn btn-info btn-sm"
								type="button"
								id="view_attachment"
								data-datavalue="' . $userdetails[0]["pds_file"] . '"
								>
						<i class="fa-solid fa-paperclip"></i> View
					</button>';
									}
									?>
									<input type="file" class="form-control-file" id="pds_file" name="pds_file" accept=".pdf" style="width: 300px; margin-top: 5px;">
									<button type="button"
										class="btn btn-success mt-2"
										id="upload_pdsfile"
										data-fileinput="pds_file"
										data-backendurl="backend/bk_profilemanagement.php"
										data-targetdiv="pds_file_div">
										Save PDS File
									</button>
								</div>

								<hr class="my-4" style="height:2px; background-color:#343a40;">
								<!-- Work Experience File -->
								<div class="form-group" id="workexp_file_div">
									<label for="workexp_file">Separate Work Experience Sheet (CS Form 212) <span class='font-weight-bold text-danger'>*</span></label>
									<?php
									if (!empty($userdetails[0]["workexp_file"])) {
										echo '<button 	class="btn btn-info btn-sm"
								type="button"
								id="view_attachment"
								data-datavalue="' . $userdetails[0]["workexp_file"] . '"
								>
						<i class="fa-solid fa-paperclip"></i> View
					</button>';
									}
									?>
									<input type="file" class="form-control-file" id="workexp_file" name="workexp_file" accept=".pdf" style="width: 300px; margin-top: 5px;">
									<button type="button"
										class="btn btn-success mt-2"
										id="upload_workexpfile"
										data-fileinput="workexp_file"
										data-backendurl="backend/bk_profilemanagement.php"
										data-targetdiv="workexp_file_div">
										Save Work Experience
									</button>
								</div>

								<hr class="my-4" style="height:2px; background-color:#343a40;">

								<!-- Performance Rating File -->
								<div class="form-group" id="perf_file_div">
									<label for="perf_file">Performance rating (last rating period, if applicable)</label>
									<?php
									if (!empty($userdetails[0]["perf_file"])) {
										echo '<button 	class="btn btn-info btn-sm"
								type="button"
								id="view_attachment"
								data-datavalue="' . $userdetails[0]["perf_file"] . '"
								>
						<i class="fa-solid fa-paperclip"></i> View
					</button>';
									}
									?>
									<input type="file" class="form-control-file mb-3" id="perf_file" name="perf_file" accept=".pdf" style="width: 300px; margin-top: 5px;">
									<label for='perf_rating'>Performance Rating (last rating period): </label>
									<input
										id='perf_rating'
										name='perf_rating'
										class='form-control mb-3'
										style='max-width: 300px;'
										value="<?= htmlspecialchars($userdetails[0]['perf_rating'] ?? '') ?>">

									<label for='adj_rating'>Adjectival Rating (last rating period): </label>
									<input
										id='adj_rating'
										name='adj_rating'
										class='form-control'
										style='max-width: 300px;'
										value="<?= htmlspecialchars($userdetails[0]['adj_rating'] ?? '') ?>">
									<div>
										<button type="button"
											class="btn btn-success mt-2"
											id="upload_perffile"
											data-fileinput="perf_file"
											data-backendurl="backend/bk_profilemanagement.php"
											data-targetdiv="perf_file_div">
											Save Performance File and Details
										</button>
									</div>
								</div>

								<!-- File Uploads End -->

							</div>
						</div>
						<!-- =================================================================================================================================== -->

					</div>


				</div>
			</div>
		</div>

	</div>
</div>
</div>


<script>
	$(document).ready(function() {
		DivLoader(
			'educationbody',
			'backend/bk_profilemanagement.php', {
				request: 'vieweducation',
				userid: UserInfo['UserID']
			}
		);
		DivLoader(
			'eligibilitybody',
			'backend/bk_profilemanagement.php', {
				request: 'vieweligibility',
				userid: UserInfo['UserID']
			}
		);
		DivLoader(
			'workexperiencebody',
			'backend/bk_profilemanagement.php', {
				request: 'viewworkexperience',
				userid: UserInfo['UserID']
			}
		);
		DivLoader(
			'voluntarybody',
			'backend/bk_profilemanagement.php', {
				request: 'viewvoluntarywork',
				userid: UserInfo['UserID']
			}
		);
		DivLoader(
			'learningdevelopmentbody',
			'backend/bk_profilemanagement.php', {
				request: 'viewlearningdevelopment',
				userid: UserInfo['UserID']
			}
		);
		DivLoader(
			'skills_list',
			'backend/bk_profilemanagement.php', {
				request: 'viewskills',
				userid: UserInfo['UserID']
			}
		);
		DivLoader(
			'ndr_list',
			'backend/bk_profilemanagement.php', {
				request: 'viewndr',
				userid: UserInfo['UserID']
			}
		);
		DivLoader(
			'membership_list',
			'backend/bk_profilemanagement.php', {
				request: 'viewmembership',
				userid: UserInfo['UserID']
			}
		);
		DivLoader(
			'comp_list',
			'backend/bk_profilemanagement.php', {
				request: 'viewcomp',
				userid: UserInfo['UserID']
			}
		);
	});

	$('#ndr_file').on('change', function() {
		let fileName = this.files.length ? this.files[0].name : 'No file selected';
		$('#ndr_file_name').text(fileName);
	});

	$('#membership_file').on('change', function() {
		let fileName = this.files.length ? this.files[0].name : 'No file selected';
		$('#membership_file_name').text(fileName);
	});

	$(document).ready(function() {
		$(".custom-file-input").on("change", function() {
			var fileName = $(this).val().split("\\").pop();
			$(this).siblings(".custom-file-label").addClass("selected").html(fileName);
		});
	});

	$(document).on('change', '.question-yesno', function() {
		const target = $(this).data('target');
		if ($(this).val() === "Yes") {
			$(target).removeClass('d-none');
		} else {
			$(target).addClass('d-none');
		}
	});

	$(document).ready(function() {

		$.ajax({
			url: 'backend/bk_macroloader.php',
			type: 'POST',
			data: {
				request: 'loadmacros',
				datavalue: 15
			},

			success: function(dataResult) {
				$('#infomacros').html(dataResult);
			},

			error: function(xhr, status, error) {

				console.error('AJAX Error:', error);
			}
		});
	});
</script>
