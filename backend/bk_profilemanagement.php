<?php
date_default_timezone_set('Asia/Manila');
include "../db/dbconnection.php";

$request = isset($_POST["request"]) ? $_POST["request"] : "";

$currentdt = date("Y-m-d H:i:s");

switch ($request) {

	case "savepersonaldetails":

		$data = $_POST["fields"];
		$datavalue = isset($_POST["datavalue"]) ? intval($_POST["datavalue"]) : 0;

		$email = isset($data["email_address"]) ? strtolower(trim($data["email_address"])) : "";

		$checkemail = execsqlSRS("
			SELECT [EmailAddress]
			FROM [Sys_UserAccount]
			WHERE LOWER([EmailAddress]) = :email
			  AND UserID != :userid
		", "Select", [
			":email"  => $email,
			":userid" => $datavalue
		]);

		if ($checkemail && count($checkemail) > 0) {
			echo json_encode([
				"status" => "error",
				"message" => "Email is already registered to another account."
			]);
			exit;
		}

		$optionalFields = [
			"home_house",
			"home_barangay",
			"home_street",
			"curr_house",
			"curr_street",
			"curr_barangay",
			"telephone_number"
		];

		$errors = [];
		foreach ($data as $key => $value) {
			if (in_array($key, $optionalFields)) continue;
			if (trim($value) === "") $errors[] = $key;
		}

		if (!empty($errors)) {
			echo json_encode([
				"status" => "error",
				"message" => "Please fill up all required fields."
			]);
			exit;
		}

		$birthDate = new DateTime($data["birth_date"]);
		$today = new DateTime();
		$age = $birthDate->diff($today)->y;

		$useraccountupdate = execsqlSRS("
			UPDATE [Sys_UserAccount]
			SET [EmailAddress] = :email,
				[LastName] = :lastname,
				[FirstName] = :firstname
			WHERE UserID = :userid
		", "Update", [
			":email"     => $email,
			":lastname"  => $data["last_name"],
			":firstname" => $data["first_name"],
			":userid"    => $datavalue
		]);

		$checkuserdetailsid = execsqlSRS("
			SELECT [UserDetailsID]
			FROM [tbl_ProfUserDetails]
			WHERE UserID = :userid
		", "Select", [
			":userid" => $datavalue
		]);

		if ($checkuserdetailsid && count($checkuserdetailsid) > 0) {
			$updateDetails = execsqlSRS("
				UPDATE [tbl_ProfUserDetails]
				SET [MiddleName]      = :middlename,
					[ExtName]         = :extname,
					[DateOfBirth]     = :dateofbirth,
					[CivilStatus]     = :civilstatus,
					[Sex]             = :sex,
					[Nationality]     = :nationality,
					[MobileNumber]    = :mobilenumber,
					[TelephoneNumber] = :telephonenumber,
					[Age]             = :age,
					[Religion]        = :religion,
					[HmHouse]         = :hmhouse,
					[HmStreet]        = :hmstreet,
					[HmBarangay]      = :hmbarangay,
					[HmCity]          = :hmcity,
					[HmProvince]      = :hmprovince,
					[HmZip]           = :hmzip,
					[CurHouse]        = :curhouse,
					[CurStreet]       = :curstreet,
					[CurBarangay]     = :curbarangay,
					[CurCity]         = :curcity,
					[CurProvince]     = :curprovince,
					[CurZip]          = :curzip
				WHERE UserID = :userid
			", "Update", [
				":middlename"      => $data["middle_name"],
				":extname"         => $data["extension_name"],
				":dateofbirth"     => $data["birth_date"],
				":civilstatus"     => $data["civil_status"],
				":sex"             => $data["sex"],
				":nationality"     => $data["nationality"],
				":mobilenumber"    => $data["mobile_number"],
				":telephonenumber" => $data["telephone_number"],
				":age"             => $age,
				":religion"        => $data["religion"],
				":hmhouse"         => $data["home_house"],
				":hmstreet"        => $data["home_street"],
				":hmbarangay"      => $data["home_barangay"],
				":hmcity"          => $data["home_city"],
				":hmprovince"      => $data["home_province"],
				":hmzip"           => $data["home_zip"],
				":curhouse"        => $data["curr_house"],
				":curstreet"       => $data["curr_street"],
				":curbarangay"     => $data["curr_barangay"],
				":curcity"         => $data["curr_city"],
				":curprovince"     => $data["curr_province"],
				":curzip"          => $data["curr_zip"],
				":userid"          => $datavalue
			]);
		} else {
			$insertdetails = execsqlSRS("
				INSERT INTO [tbl_ProfUserDetails]
				(
					[UserID], [MiddleName], [ExtName], [DateOfBirth], [CivilStatus],
					[Sex], [Nationality], [MobileNumber], [TelephoneNumber], [Age], [Religion],
					[HmHouse], [HmStreet], [HmBarangay], [HmCity], [HmProvince], [HmZip],
					[CurHouse], [CurStreet], [CurBarangay], [CurCity], [CurProvince], [CurZip], [IsActive]
				)
				VALUES
				(
					:userid, :middlename, :extname, :dateofbirth, :civilstatus,
					:sex, :nationality, :mobilenumber, :telephonenumber, :age, :religion,
					:hmhouse, :hmstreet, :hmbarangay, :hmcity, :hmprovince, :hmzip,
					:curhouse, :curstreet, :curbarangay, :curcity, :curprovince, :curzip, '0'
				)
			", "", [
				":userid"          => $datavalue,
				":middlename"      => $data["middle_name"],
				":extname"         => $data["extension_name"],
				":dateofbirth"     => $data["birth_date"],
				":civilstatus"     => $data["civil_status"],
				":sex"             => $data["sex"],
				":nationality"     => $data["nationality"],
				":mobilenumber"    => $data["mobile_number"],
				":telephonenumber" => $data["telephone_number"],
				":age"             => $age,
				":religion"        => $data["religion"],
				":hmhouse"         => $data["home_house"],
				":hmstreet"        => $data["home_street"],
				":hmbarangay"      => $data["home_barangay"],
				":hmcity"          => $data["home_city"],
				":hmprovince"      => $data["home_province"],
				":hmzip"           => $data["home_zip"],
				":curhouse"        => $data["curr_house"],
				":curstreet"       => $data["curr_street"],
				":curbarangay"     => $data["curr_barangay"],
				":curcity"         => $data["curr_city"],
				":curprovince"     => $data["curr_province"],
				":curzip"          => $data["curr_zip"]
			]);
		}

		echo json_encode([
			"status" => "success",
			"message" => "Personal details saved successfully."
		]);

		break;

	case "addeducation":

		echo '

				<div class="card border border-info shadow">
					<div class="card-title ml-3 mt-3 mb-3 mr-3">
						<i class="fa-solid fa-circle-question text-info"></i>
							<span class="font-weight-bold text-info">Directions:</span>
							<span>Please fill in the required information <span class="text-danger">*</span> for your education details. File Attachments should not exceed 10MB in size.</span>
					</div>
				</div>

		<div class="p-3 bg-light border rounded border-success shadow" id="educationdiv">

			<!-- Education Level -->
			<div class="form-group">
				<label for="edu_level">Education Level <span class="text-danger">*</span></label>
				<select class="form-control border-success" id="edu_level">
					<option value="">--Select Education Level--</option>
					<hr>';

		$eduLevels = execsqlSRS("
						SELECT [edu_id]
							,[edu_desc]
							,[IsActive]
						FROM [tbl_ProfEducationLevels]
						WHERE [IsActive] = 0
						", "Select", array());
		foreach ($eduLevels as $edu) {
			echo '<option value="' . $edu['edu_id'] . '">' . $edu['edu_desc'] . '</option>';
		}
		echo    '</select>
			</div>

			<!-- Degree -->
			<div class="form-group">
				<label for="degree_name">Degree <span class="text-danger">* (n/a if not applicable)</span></label>
				<input type="text" class="form-control border-success" id="degree_name" name="degree_name" placeholder="Enter degree">
			</div>

			<!-- Major -->
			<div class="form-group">
				<label for="major_name">Major <span class="text-danger">* (n/a if not applicable)</span></label>
				<input type="text" class="form-control border-success" id="major_name" name="major_name" placeholder="Enter major">
			</div>

			<!-- School Name -->
			<div class="form-group">
				<label for="school_name">School Name <span class="text-danger">*</span></label>
				<input type="text" class="form-control border-success" id="school_name" name="school_name" placeholder="Enter school name">
			</div>

			<!-- School Address -->
			<div class="form-group">
				<label for="school_address">School Address <span class="text-danger">*</span></label>
				<input type="text" class="form-control border-success" id="school_address" name="school_address" placeholder="Enter school address">
			</div>

			<!-- Start Date -->
			<div class="form-group">
				<label for="start_date">Start Date <span class="text-danger">*</span></label>
				<input type="date" class="form-control border-success" id="start_date" name="start_date">
			</div>

			<!-- End Date -->
			<div class="form-group">
				<label for="end_date">End Date <span class="text-danger">*</span></label>
				<input type="date" class="form-control border-success" id="end_date" name="end_date">
			</div>

			<!-- AWARD SECTION WRAPPER -->
			<div id="award_section">

				<!-- Add Award Button -->
				<div class="form-group">
					<button type="button" class="btn btn-info" id="add_award_btn">
						Add Proof/Supporting Documents <i class="fa-solid fa-plus"></i>
					</button>
					<span class="text-danger">*</span>
				</div>

				<!-- Awards Container -->
				<div id="award_container"></div>

			</div>

			<!-- Save Button -->
			<div class="form-group mt-3 d-flex justify-content-center">
				<button type="button"
						class="btn btn-success"
						id="save_educationdetails">
					Save <i class="fa-solid fa-plus"></i>
				</button>
			</div>

		</div>

		<script>

		var awardIndex = 0;

		// Hide award section initially
		$("#award_section").hide();

		// When the Education Level changes
		$("#edu_level").on("change", function(){
			var edu_id = $(this).val();

			// Clear awards
			$("#award_container").empty();
			awardIndex = 0;

			if(!edu_id){
				$("#award_section").hide();
				Swal.fire({
					icon: "info",
					title: "Oops!",
					text: "Please select a valid Education Level.",
					showConfirmButton: false
				});
				return;
			}

			// Hide if Elementary (value = 2)
			if(edu_id == "2"){
				$("#award_section").hide();
			} else {
				$("#award_section").show();
			}
		});

		// Add a new award+file row
		$("#add_award_btn").on("click", function(){
			var edu_id = $("#edu_level").val();
			if(!edu_id){
				Swal.fire({
					icon: "warning",
					title: "Oops!",
					text: "Please select an Education Level first!",
					showConfirmButton: false
				});
				return;
			}

			awardIndex++;

			// AJAX to fetch awards options
			$.ajax({
				url: "backend/bk_profilemanagement.php",
				type: "POST",
				data: { request: "getawardsbyedu", edu_id: edu_id },
				success: function(options){
					var row = `
						<div class="form-group d-flex align-items-center mb-2" id="award_row_${awardIndex}">
							<select class="form-control border-success mr-2" style="flex: 1;"
									id="award_${awardIndex}"
									name="awards[${awardIndex}][id]">
								${options}
							</select>
							<input type="file" class="form-control-file border-success mr-2"
								style="flex: 1;"
								id="award_file_${awardIndex}"
								name="awards[${awardIndex}][file]"
								accept="image/jpeg,image/gif,image/png,application/pdf,image/x-eps">
							<button type="button" class="btn btn-danger btn-sm remove_award_btn"
									data-row="award_row_${awardIndex}">
								<i class="fa-solid fa-trash"></i>
							</button>
						</div>
					`;
					$("#award_container").append(row);
				},
				error: function(){
					Swal.fire({
						icon: "error",
						title: "Oops...",
						text: "Failed to load awards.",
						showConfirmButton: false
					});
				}
			});
		});

		// Remove award row
		$(document).on("click", ".remove_award_btn", function(){
			var rowId = $(this).data("row");
			$("#" + rowId).remove();
		});

		</script>

		';

		break;

	case "getawardsbyedu":
		$edu_id = isset($_POST['edu_id']) ? $_POST['edu_id'] : "";

		if ($edu_id) {
			$awards = execsqlSRS("
                SELECT awards_id, awards_desc
                FROM tbl_ProfAwards
                WHERE edu_id = ? AND IsActive = 0
            ", "Select", array($edu_id));

			echo "<option value=''>--Select Document Type--</option><hr>";
			foreach ($awards as $award) {
				echo "<option value='" . $award['awards_id'] . "'>" . $award['awards_desc'] . "</option>";
			}
		} else {
			echo "<option value=''>--Select Document Type--</option>";
		}
		break;

	case "saveeducation":

		$timestamp = date("Ymd_His");

		$edu_level      = $_POST["edu_level"] ?? "";
		$degree_name    = $_POST["degree_name"] ?? "";
		$major_name     = $_POST["major_name"] ?? "";
		$school_name    = $_POST["school_name"] ?? "";
		$school_address = $_POST["school_address"] ?? "";
		$start_date     = $_POST["start_date"] ?? "";
		$end_date       = $_POST["end_date"] ?? "";
		$userid         = $_POST["userid"] ?? "";

		if (!$edu_level || !$school_name || !$school_address || !$start_date || !$end_date) {
			echo json_encode([
				"status" => "error",
				"message" => "Please fill in all required fields."
			]);
			exit;
		}

		if ($edu_level != "2") {

			$hasFile = false;

			if (isset($_FILES['awards']['name'])) {
				foreach ($_FILES['awards']['name'] as $index => $fileData) {
					if (!empty($fileData['file'])) {
						$hasFile = true;
						break;
					}
				}
			}

			if (!$hasFile) {
				echo json_encode([
					"status" => "error",
					"message" => "Supporting document is required for this education level."
				]);
				exit;
			}
		}

		$awards      = $_POST['awards'] ?? [];
		$awardFiles  = $_FILES['awards'] ?? null;
		$uploadDir   = "../uploads/credentials/";

		if (!is_dir($uploadDir)) {
			mkdir($uploadDir, 0777, true);
		}

		$allowedTypes  = ['pdf', 'jpg', 'jpeg', 'png'];
		$uploadedFiles = [];

		foreach ($awards as $index => $award) {

			$award_id = $award['id'] ?? "";

			if (
				isset($awardFiles['name'][$index]['file']) &&
				$awardFiles['error'][$index]['file'] === 0
			) {

				$fileName = $awardFiles['name'][$index]['file'];
				$tmpName  = $awardFiles['tmp_name'][$index]['file'];
				$fileSize = $awardFiles['size'][$index]['file'];

				$ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

				if (!in_array($ext, $allowedTypes)) continue;
				if ($fileSize > 10 * 1024 * 1024) {
					echo json_encode([
						"status" => "error",
						"message" => "File size must not exceed 10MB."
					]);
					exit;
				}

				$uniqueName   = date("Ymd_His") . "_" . uniqid() . "_" . $fileName;
				$destination  = $uploadDir . $uniqueName;

				if (move_uploaded_file($tmpName, $destination)) {
					$uploadedFiles[] = [
						"award_id" => $award_id,
						"file"     => $uniqueName,
						"path"     => $destination
					];
				}
			}
		}

		$insertEducation = execsqlSRS(
			"
        INSERT INTO tbl_ProfEducation (
            degree_name,
            major_name,
            school_name,
            school_address,
            start_date,
            end_date,
            UserID,
            edu_id,
            IsActive,
            IsLocked
        ) VALUES (
            :degree_name,
            :major_name,
            :school_name,
            :school_address,
            :start_date,
            :end_date,
            :userid,
            :edu_level,
            '0',
            '1'
        )
        ",
			"Insert",
			[
				":degree_name"    => $degree_name,
				":major_name"     => $major_name,
				":school_name"    => $school_name,
				":school_address" => $school_address,
				":start_date"     => $start_date,
				":end_date"       => $end_date,
				":userid"         => $userid,
				":edu_level"      => $edu_level
			]
		);

		$insertedIdResult = execsqlSRS(
			"
        SELECT TOP 1 education_id
        FROM tbl_ProfEducation
        WHERE degree_name = ?
          AND major_name = ?
          AND school_name = ?
          AND school_address = ?
          AND start_date = ?
          AND end_date = ?
          AND UserID = ?
          AND edu_id = ?
          AND IsActive = 0
          AND IsLocked = 1
        ORDER BY education_id DESC
        ",
			"Select",
			[
				$degree_name,
				$major_name,
				$school_name,
				$school_address,
				$start_date,
				$end_date,
				$userid,
				$edu_level
			]
		);

		$inserted_id = $insertedIdResult[0]['education_id'] ?? null;

		if (!$inserted_id) {
			echo json_encode([
				"status"  => "error",
				"message" => "Failed to save education record."
			]);
			exit;
		}

		foreach ($uploadedFiles as $awardFile) {

			$award_id  = $awardFile['award_id'];
			$fileName  = $awardFile['file'];
			$filePath  = $awardFile['path'];

			$attachmentInsert = execsqlSRS(
				"
            INSERT INTO tbl_Attachment (
                att_filename,
                att_filepath,
                att_dt,
                UserID,
                IsActive
            ) VALUES (
                :att_filename,
                :att_filepath,
                :att_dt,
                :userid,
                '0'
            )
            ",
				"Insert",
				[
					":att_filename" => $fileName,
					":att_filepath" => $filePath,
					":att_dt"       => $currentdt,
					":userid"       => $userid
				]
			);

			$attachIdResult = execsqlSRS(
				"
            SELECT TOP 1 attach_id
            FROM tbl_Attachment
            WHERE att_filename = ?
              AND att_filepath = ?
              AND att_dt = ?
              AND UserID = ?
              AND IsActive = 0
            ORDER BY attach_id DESC
            ",
				"Select",
				[
					$fileName,
					$filePath,
					$currentdt,
					$userid
				]
			);

			$attach_id = $attachIdResult[0]['attach_id'] ?? null;

			if ($attach_id) {
				$awardInsert = execsqlSRS(
					"
                INSERT INTO tbl_ProfEducationAwards (
                    education_id,
                    awards_id,
                    attach_id,
                    IsActive
                ) VALUES (
                    :education_id,
                    :awards_id,
                    :attach_id,
                    '0'
                )
                ",
					"Insert",
					[
						":education_id" => $inserted_id,
						":awards_id"    => $award_id,
						":attach_id"    => $attach_id
					]
				);
			}
		}

		echo json_encode([
			"status"  => "success",
			"message" => "Education details saved successfully!"
		]);

		break;

	case "vieweducation":

		$userid = isset($_POST['userid']) ? intval($_POST['userid']) : 0;

		if ($userid <= 0) {
			echo "<div class='text-danger p-3'>Invalid User ID.</div>";
			break;
		}

		$educations = execsqlSRS("
			SELECT   ed.[education_id]
					,ed.[degree_name]
					,ed.[major_name]
					,ed.[school_name]
					,ed.[school_address]
					,ed.[start_date]
					,ed.[end_date]
					,ed.[IsActive]

					,lev.[edu_desc]
			FROM [tbl_ProfEducation] ed


			LEFT JOIN [tbl_ProfEducationLevels] lev
			ON lev.[edu_id] = ed.[edu_id]

			WHERE [UserID] = ?
			ORDER BY [end_date]
		", "Select", array($userid));

		if (!$educations || count($educations) == 0) {
			echo "<div class='p-3 text-danger font-weight-bold'>No education records found.</div>";
			break;
		}

		echo "<div class='row'>";

		foreach ($educations as $edu) {
			$start = !empty($edu['start_date']) ? date("M Y", strtotime($edu['start_date'])) : 'N/A';
			$end   = !empty($edu['end_date'])   ? date("M Y", strtotime($edu['end_date'])) : 'Present';

			$attachment = execsqlSRS("
				SELECT 	award.[attach_id]

				FROM [tbl_ProfEducationAwards] award

				WHERE award.[education_id] = ?
			", "Select", array($edu['education_id']));


			echo "
			<div class='col-lg-4 col-md-6 col-sm-12 fade-in mb-4'>
				<div class='card h-100'>
					<div class='card-header d-flex align-items-center justify-content-center pl-2 pr-2 pt-3 pb-2 bg-success'>
						<span class='h5 text-center font-weight-bold'>{$edu['edu_desc']}</span>
					</div>
					<div class='card-body d-flex flex-column'>

						<div class='d-flex mb-1'>
							<div style='width:20px;text-align:center;'><i class='fas fa-school'></i></div>
							<div><strong class='ml-1'>School:</strong> {$edu['school_name']}</div>
						</div>

						<div class='d-flex mb-1'>
							<div style='width:20px;text-align:center;'><i class='fas fa-map-marker-alt'></i></div>
							<div><strong class='ml-1'>Address:</strong> {$edu['school_address']}</div>
						</div>

						<div class='d-flex mb-1'>
							<div style='width:20px;text-align:center;'><i class='fas fa-graduation-cap'></i></div>
							<div><strong class='ml-1'>Degree:</strong> {$edu['degree_name']}</div>
						</div>

						<div class='d-flex mb-1'>
							<div style='width:20px;text-align:center;'><i class='fas fa-chalkboard'></i></div>
							<div><strong class='ml-1'>Major:</strong> {$edu['major_name']}</div>
						</div>

						<div class='d-flex'>
							<div style='width:20px;text-align:center;'><i class='fas fa-calendar-alt'></i></div>
							<div><strong class='ml-1'>Duration:</strong> {$start} - {$end}</div>
						</div>";

			echo "	<div class='d-flex'>
							<div style='width:20px;text-align:center;'><i class='fas fa-paperclip'></i></div>
							<div><strong class='ml-1'>Attachment/s: </strong>";

			foreach ($attachment as $attach) {
				echo "<button class='btn btn-info btn-sm mr-1'
											  type='button'
											  id='view_attachment'
											  data-datavalue='{$attach['attach_id']}'
											  data-openmodallabel='View Attachment'
											  >
											<i class='fa-solid fa-paperclip'></i> View
									  </button>";
			}

			echo "			</div>
						</div>
					</div>
					<div class='card-footer'>
						<button class='btn btn-danger btn-block btn-delete-education mt-1'
								data-datavalue='{$edu['education_id']}'
								data-backendurl='backend/bk_profilemanagement.php'
								data-backendrequest='deleteeducation'
								id='delete_education'>
							<i class='fas fa-trash'></i> Delete
						</button>
					</div>
				</div>
			</div>
			";
		}

		echo "</div>";

		break;

	case "deleteeducation":

		$education_id = isset($_POST['datavalue']) ? intval($_POST['datavalue']) : 0;

		if ($education_id <= 0) {
			echo json_encode([
				"status" => "error",
				"message" => "Invalid education record."
			]);
			exit;
		}

		$delete = execsqlSRS("
			DELETE FROM [tbl_ProfEducation]
			WHERE [education_id] = :education_id
		", "Delete", [":education_id" => $education_id]);

		echo json_encode([
			"status" => "success",
			"message" => "Education record deleted successfully."
		]);

		break;

	case "addeligibility":

		echo '

				<div class="card border border-info shadow">
					<div class="card-title ml-3 mt-3 mb-3 mr-3">
						<i class="fa-solid fa-circle-question text-info"></i>
							<span class="font-weight-bold text-info">Directions:</span>
							<span>Please fill in the required information <span class="text-danger">*</span> for your eligibility details. File Attachments should not exceed 10MB in size.</span>
							<!-- If your eligibility type is not in the list, click on the <button class="btn btn-info btn-sm"><i class="fa-solid fa-circle-question"></i></button> button.
				-->	</div>
				</div>

		<div class="p-3 bg-light border rounded border-success shadow" id="eligibilitydiv">

			<!-- Education Level -->
			<div class="form-group" id="eligibility-group">
				<label for="eligibility">
					Eligibility Type <span class="text-danger">*</span>
				<!--	<button class="btn btn-info btn-sm"
							data-tooltip="Eligibility not in this list?"
							id="toggleEligibilityBtn">
						<i class="fas fa-question-circle"></i>
					</button> -->
				</label>

				<!-- Dropdown -->
				<select class="form-control border-success" id="eligibility" name="eligibility_select">
					<option value="">--Select Eligibility Type--</option><hr>';

		$eligibility = execsqlSRS("
						SELECT [eligibility_id]
								,[eligibility_desc]
								,[IsActive]
						FROM [tbl_ProfEligibilityLibrary]
						WHERE [IsActive] = 0
						ORDER BY [eligibility_desc]
						", "Select", array());
		foreach ($eligibility as $eli) {
			echo '<option value="' . htmlspecialchars($eli['eligibility_id']) . '">' . htmlspecialchars($eli['eligibility_desc']) . '</option>';
		}
		echo    '</select>

				<!-- Hidden input -->
				<input type="text" class="form-control border-success" id="eligibilityInput" name="eligibility_custom" placeholder="Enter eligibility type" style="display:none;">
			</div>

			<!-- Degree -->
			<div class="form-group">
				<label for="elig_rating">Rating <span class="text-danger">* (n/a if not applicable)</span></label>
				<input type="text" class="form-control border-success" id="elig_rating" name="elig_rating" placeholder="Enter rating">
			</div>

			<!-- Major -->
			<div class="form-group">
				<label for="exam_date">Date of Examination/Conferment <span class="text-danger">*</span></label>
				<input type="date" class="form-control border-success" id="exam_date" name="exam_date">
			</div>

			<!-- School Name -->
			<div class="form-group">
				<label for="exam_place">Place of Examination/Conferment <span class="text-danger">*</span></label>
				<input type="text" class="form-control border-success" id="exam_place" name="exam_place" placeholder="Enter place of examination">
			</div>

			<!-- School Address -->
			<div class="form-group">
				<label for="license_number">License Number <span class="text-danger">* (n/a if not applicable)</span></label>
				<input type="text" class="form-control border-success" id="license_number" name="license_number" placeholder="Enter license number">
			</div>

			<div class="form-group">
				<label for="valid_until">Valid Until <span class="text-danger">* (leave blank if indefinitely)</span></label>
				<input type="date" class="form-control border-success" id="valid_until" name="valid_until">
			</div>

			<div class="form-group">
				<label for="proof_eligibility">
					Proof of Eligibility
					<span class="text-danger">*</span>
				</label>
				<input type="file" class="form-control-file" id="proof_eligibility" name="proof_eligibility" accept="image/jpeg,image/gif,image/png,application/pdf,image/x-eps">
			</div>

			<!-- Save Button -->
			<div class="form-group mt-3 d-flex justify-content-center">
				<button type="button"
						class="btn btn-success"
						id="save_eligibilitydetails">
					Save <i class="fa-solid fa-plus"></i>
				</button>
			</div>

		</div>

		<script>
			$(document).off("click", "#toggleEligibilityBtn").on("click", "#toggleEligibilityBtn", function() {
				var $group = $(this).closest("#eligibility-group");
				var $select = $group.find("#eligibility");
				var $input = $group.find("#eligibilityInput");

				if ($input.is(":visible")) {
					$input.slideUp(200, function() {
						$input.val("");
						$select.slideDown(200);
					});
				} else {
					$select.slideUp(200, function() {
						$select.val("");
						$input.slideDown(200, function() { $input.focus(); });
					});
				}
			});
		</script>

		';

		break;

	case "saveeligibility":

		$userid = isset($_POST['userid']) ? intval($_POST['userid']) : 0;

		$eligibility_id     = isset($_POST['eligibility']) ? trim($_POST['eligibility']) : '';
		$eligibility_custom = isset($_POST['eligibilityInput']) ? trim($_POST['eligibilityInput']) : '';

		$rating         = isset($_POST['elig_rating']) ? trim($_POST['elig_rating']) : null;
		$exam_date      = isset($_POST['exam_date']) ? $_POST['exam_date'] : '';
		$exam_place     = isset($_POST['exam_place']) ? trim($_POST['exam_place']) : '';
		$license_number = isset($_POST['license_number']) ? trim($_POST['license_number']) : null;
		$valid_until    = isset($_POST['valid_until']) ? $_POST['valid_until'] : null;

		if ($userid <= 0) {
			echo json_encode(["status" => "error", "message" => "Invalid user."]);
			exit;
		}

		if (empty($eligibility_id) && empty($eligibility_custom)) {
			echo json_encode(["status" => "error", "message" => "Please select or enter Eligibility Type."]);
			exit;
		}

		if (!empty($eligibility_id) && !empty($eligibility_custom)) {
			echo json_encode(["status" => "error", "message" => "Choose only one Eligibility Type."]);
			exit;
		}

		if (empty($exam_date) || empty($exam_place)) {
			echo json_encode(["status" => "error", "message" => "Please fill in required fields."]);
			exit;
		}

		if (!isset($_FILES['proof_eligibility']) || $_FILES['proof_eligibility']['error'] != 0) {
			echo json_encode([
				"status" => "error",
				"message" => "Supporting document is required."
			]);
			exit;
		}

		$fileSize = $_FILES['proof_eligibility']['size'];

		if ($fileSize > 10 * 1024 * 1024) {
			echo json_encode([
				"status" => "error",
				"message" => "File size must not exceed 10MB."
			]);
			exit;
		}

		$final_eligibility_id = null;
		$final_type           = null;

		if (!empty($eligibility_id)) {
			$final_eligibility_id = $eligibility_id;
			$final_type = null;
		} else {
			$final_eligibility_id = null;
			$final_type = $eligibility_custom;
		}

		$attach_id = null;

		if (isset($_FILES['proof_eligibility']) && $_FILES['proof_eligibility']['error'] == 0) {

			$uploadDir = "../uploads/eligibilities/";

			if (!is_dir($uploadDir)) {
				mkdir($uploadDir, 0777, true);
			}

			$originalName = $_FILES['proof_eligibility']['name'];
			$tmpName      = $_FILES['proof_eligibility']['tmp_name'];

			$newFileName = date("YmdHis") . "_" . preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
			$filePath = $uploadDir . $newFileName;

			if (move_uploaded_file($tmpName, $filePath)) {
				$currentdt = date("Y-m-d H:i:s");
				execsqlSRS(
					"INSERT INTO tbl_Attachment ([att_filename], [att_filepath], [att_dt], [UserID], [IsActive]) VALUES (?, ?, ?, ?, 0)",
					"Insert",
					array($newFileName, $filePath, $currentdt, $userid)
				);

				$getID = execsqlSRS(
					"SELECT TOP 1 [attach_id] FROM tbl_Attachment WHERE [UserID] = ? ORDER BY [attach_id] DESC",
					"Select",
					array($userid)
				);

				$attach_id = $getID[0]['attach_id'] ?? 0;
			}
		}

		$insertQuery = "
			INSERT INTO [tbl_ProfEligibility]
			([eligibility_id], [rating], [exam_date], [exam_place], [license_number], [license_validity], [type], [UserID], [attach_id], [IsActive])
			VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0)
		";

		$params = array(
			$final_eligibility_id,
			!empty($rating) ? $rating : null,
			$exam_date,
			$exam_place,
			!empty($license_number) ? $license_number : null,
			!empty($valid_until) ? $valid_until : null,
			$final_type,
			$userid,
			$attach_id
		);

		$result = execsqlSRS($insertQuery, "Insert", $params);

		echo json_encode([
			"status" => "success",
			"message" => "Eligibility record successfully saved."
		]);

		break;

	case "vieweligibility":

		$userid = isset($_POST['userid']) ? intval($_POST['userid']) : 0;

		$eligibilities = execsqlSRS("
			SELECT  el.[eli_id],
					el.[eligibility_id],
					lib.[eligibility_desc],
					el.[rating],
					el.[exam_date],
					el.[exam_place],
					el.[license_number],
					el.[license_validity],
					el.[type],
					el.[UserID],
					el.[attach_id],
					att.[att_filename],
					el.[IsActive]
			FROM [tbl_ProfEligibility] el

			LEFT JOIN [tbl_Attachment] att
				ON att.[attach_id] = el.[attach_id]

			LEFT JOIN [tbl_ProfEligibilityLibrary] lib
				ON lib.[eligibility_id] = el.[eligibility_id]

			WHERE el.[UserID] = ?

			ORDER BY el.[exam_date]
		", "Select", array($userid));

		if (!$eligibilities || count($eligibilities) == 0) {
			echo "<div class='p-3 text-danger font-weight-bold'>No eligibility records found.</div>";
			break;
		}

		echo "<div class='row'>";

		foreach ($eligibilities as $eligibility) {

			$eligibility_name = !empty($eligibility['eligibility_id'])
				? $eligibility['eligibility_desc']
				: $eligibility['type'];

			$rating = !empty($eligibility['rating']) ? $eligibility['rating'] : 'N/A';
			$license_number = !empty($eligibility['license_number']) ? $eligibility['license_number'] : 'N/A';
			$valid_until = !empty($eligibility['license_validity'])
				? date("M d, Y", strtotime($eligibility['license_validity']))
				: 'Indefinite';

			$exam_date = !empty($eligibility['exam_date'])
				? date("M d, Y", strtotime($eligibility['exam_date']))
				: 'N/A';

			echo "
			<div class='col-lg-4 col-md-6 col-sm-12 fade-in mb-4'>
				<div class='card h-100'>

					<div class='card-header d-flex align-items-center justify-content-center pl-2 pr-2 pt-3 pb-2 bg-success'>
						<span class='h5 text-center font-weight-bold text-white'>{$eligibility_name}</span>
					</div>

					<div class='card-body d-flex flex-column'>

						<div class='d-flex mb-1'>
							<div style='width:20px;text-align:center;'><i class='fas fa-star'></i></div>
							<div><strong class='ml-1'>Rating:</strong> {$rating}</div>
						</div>

						<div class='d-flex mb-1'>
							<div style='width:20px;text-align:center;'><i class='fas fa-calendar-alt'></i></div>
							<div><strong class='ml-1'>Exam Date:</strong> {$exam_date}</div>
						</div>

						<div class='d-flex mb-1'>
							<div style='width:20px;text-align:center;'><i class='fas fa-map-marker-alt'></i></div>
							<div><strong class='ml-1'>Place:</strong> {$eligibility['exam_place']}</div>
						</div>

						<div class='d-flex mb-1'>
							<div style='width:20px;text-align:center;'><i class='fas fa-id-card'></i></div>
							<div><strong class='ml-1'>License No:</strong> {$license_number}</div>
						</div>

						<div class='d-flex'>
							<div style='width:20px;text-align:center;'><i class='fas fa-clock'></i></div>
							<div><strong class='ml-1'>Valid Until:</strong> {$valid_until}</div>
						</div>";

			echo "	<div class='d-flex'>
							<div style='width:20px;text-align:center;'><i class='fas fa-paperclip'></i></div>
							<div><strong class='ml-1'>Attachment/s: </strong>";


			echo "<button class='btn btn-info btn-sm mr-1'
											  type='button'
											  id='view_attachment'
											  data-datavalue='{$eligibility['attach_id']}'
											  data-openmodallabel='View Attachment'
											  >
											<i class='fa-solid fa-paperclip'></i> View
									  </button>";

			echo "			</div>
						</div>

					</div>
					<div class='card-footer'>
						<button class='btn btn-danger btn-block mt-1'
								data-datavalue='{$eligibility['eli_id']}'
								data-backendurl='backend/bk_profilemanagement.php'
								data-backendrequest='deleteeligibility'
								id='delete_eligibility'>
							<i class='fas fa-trash'></i> Delete
						</button>
					</div>

				</div>
			</div>
			";
		}

		echo "</div>";

		break;

	case "deleteeligibility":

		$eli_id = isset($_POST['datavalue']) ? intval($_POST['datavalue']) : 0;

		if ($eli_id <= 0) {
			echo json_encode([
				"status" => "error",
				"message" => "Invalid eligibility record."
			]);
			exit;
		}

		$delete = execsqlSRS("
			DELETE FROM [tbl_ProfEligibility]
			WHERE [eli_id] = :eli_id
		", "Delete", [":eli_id" => $eli_id]);

		echo json_encode([
			"status" => "success",
			"message" => "Eligibility record deleted successfully."
		]);
		break;

	case "addworkexperience":

		echo '

				<div class="card border border-info shadow">
					<div class="card-title ml-3 mt-3 mb-3 mr-3">
						<i class="fa-solid fa-circle-question text-info"></i>
							<span class="font-weight-bold text-info">Directions:</span>
							<span>Please fill in the required information <span class="text-danger">*</span> for your work experience details. File Attachments should not exceed 10MB in size. If your status of appointment type is not in the list, click on the <button class="btn btn-info btn-sm"><i class="fa-solid fa-circle-question"></i></button> button.</span>
					</div>
				</div>

		<div class="p-3 bg-light border rounded border-success shadow" id="workexperiencediv">

			<!-- Start Date -->
			<div class="form-group">
				<label for="work_start_date">Start Date <span class="text-danger">*</span></label>
				<input type="date" class="form-control border-success" id="work_start_date" name="work_start_date">
			</div>

			<!-- End Date -->
			<div class="form-group">
				<label for="work_end_date">End Date <span class="text-danger">*</span></label>
				<input type="date" class="form-control border-success" id="work_end_date" name="work_end_date">
			</div>

			<!-- Position -->
			<div class="form-group">
				<label for="work_position">Position <span class="text-danger">*</span></label>
				<input type="text" class="form-control border-success" id="work_position" name="work_position" placeholder="Enter position">
			</div>

			<!-- Department/Agency -->
			<div class="form-group">
				<label for="work_department">Department/Agency/Office/Company <span class="text-danger">*</span></label>
				<input type="text" class="form-control border-success" id="work_department" name="work_department" placeholder="Enter department/agency/office/company">
			</div>

			<!-- Status of Appointment with custom toggle -->
			<div class="form-group" id="status-group">
				<label for="work_status">
					Status of Appointment <span class="text-danger">*</span>
					<button class="btn btn-info btn-sm" id="toggleStatusBtn" data-tooltip="Status not in the list?">
						<i class="fas fa-question-circle"></i>
					</button>
				</label>

				<!-- Dropdown -->
				<select class="form-control border-success" id="work_status" name="work_status">
					<option value="">--Select Status of Appointment--</option><hr>';

		$appointment = execsqlSRS("
						SELECT [appoint_id],
							[appoint_desc],
							[IsActive]
						FROM [tbl_ProfExpAppoint]
						WHERE [IsActive] = 0
						ORDER BY [appoint_desc]
					", "Select", array());

		foreach ($appointment as $app) {
			echo '<option value="' . htmlspecialchars($app['appoint_id']) . '">' . htmlspecialchars($app['appoint_desc']) . '</option>';
		}

		echo '      </select>

				<!-- Hidden input for custom status -->
				<input type="text" class="form-control border-success" id="work_status_custom" name="work_status_custom" placeholder="Enter custom status" style="display:none;">
			</div>

			<!-- Is Government Service -->
			<div class="form-group">
				<label for="work_isgovernment">Is this Government Service? <span class="text-danger">*</span></label>
				<select class="form-control border-success" id="work_isgovernment" name="work_isgovernment">
					<option value="">--Select--</option><hr>
					<option value="0">Yes</option>
					<option value="1">No</option>
				</select>
			</div>

			<!-- Certificate of Employment -->
			<div class="form-group">
				<span class="text-danger font-weight-bold">Choose one or both.</span><br>
				<label for="certificate_employment">
					Certificate of Employment
					<span class="text-danger">*</span>
				</label>
				<input type="file" class="form-control-file" id="certificate_employment" name="certificate_employment" accept="image/jpeg,image/gif,image/png,application/pdf,image/x-eps">
			</div>

			<!-- Service Record -->
			<div class="form-group">
				<label for="service_record">
					Service Record
					<span class="text-danger">*</span>
				</label>
				<input type="file" class="form-control-file" id="service_record" name="service_record" accept="image/jpeg,image/gif,image/png,application/pdf,image/x-eps">
			</div>

			<!-- Save Button -->
			<div class="form-group mt-3 d-flex justify-content-center">
				<button type="button"
						class="btn btn-success"
						id="save_workexperiencedetails"
						>
					Save <i class="fa-solid fa-plus"></i>
				</button>
			</div>

		</div>

		<script>
			$(document).off("click", "#toggleStatusBtn").on("click", "#toggleStatusBtn", function(){
				var $group = $(this).closest("#status-group");
				var $select = $group.find("#work_status");
				var $input = $group.find("#work_status_custom");

				if($input.is(":visible")){
					$input.slideUp(200, function(){
						$input.val("");
						$select.slideDown(200);
					});
				} else {
					$select.slideUp(200, function(){
						$select.val("");
						$input.slideDown(200, function(){ $input.focus(); });
					});
				}
			});
		</script>
		';

		break;

	case "saveworkexperience":

		$userid = isset($_POST['userid']) ? intval($_POST['userid']) : 0;

		$start_date  = isset($_POST['work_start_date']) ? $_POST['work_start_date'] : '';
		$end_date    = isset($_POST['work_end_date']) ? $_POST['work_end_date'] : '';
		$position    = isset($_POST['work_position']) ? trim($_POST['work_position']) : '';
		$department  = isset($_POST['work_department']) ? trim($_POST['work_department']) : '';

		$appoint_id     = isset($_POST['work_status']) ? trim($_POST['work_status']) : '';
		$appoint_custom = isset($_POST['work_status_custom']) ? trim($_POST['work_status_custom']) : '';

		$gov_service = isset($_POST['work_isgovernment']) ? intval($_POST['work_isgovernment']) : null;

		if ($userid <= 0) {
			echo json_encode(["status" => "error", "message" => "Invalid user."]);
			exit;
		}

		if (!empty($appoint_id) && !empty($appoint_custom)) {
			echo json_encode(["status" => "error", "message" => "Choose only one appointment status input type."]);
			exit;
		}
		if (empty($appoint_id) && empty($appoint_custom)) {
			echo json_encode(["status" => "error", "message" => "Please select or enter appointment status."]);
			exit;
		}

		if (empty($start_date) || empty($end_date) || empty($position) || empty($department) || $gov_service === null) {
			echo json_encode(["status" => "error", "message" => "Please fill in all required fields."]);
			exit;
		}

		$hasCert = isset($_FILES['certificate_employment']) && $_FILES['certificate_employment']['error'] == 0;
		$hasService = isset($_FILES['service_record']) && $_FILES['service_record']['error'] == 0;

		if (!$hasCert && !$hasService) {
			echo json_encode([
				"status" => "error",
				"message" => "Please upload at least one supporting document (Certificate of Employment or Service Record)."
			]);
			exit;
		}

		if ($hasCert) {
			if ($_FILES['certificate_employment']['size'] > 10 * 1024 * 1024) {
				echo json_encode([
					"status" => "error",
					"message" => "Certificate of Employment must not exceed 10MB."
				]);
				exit;
			}
		}

		if ($hasService) {
			if ($_FILES['service_record']['size'] > 10 * 1024 * 1024) {
				echo json_encode([
					"status" => "error",
					"message" => "Service Record must not exceed 10MB."
				]);
				exit;
			}
		}

		$final_appoint_id     = !empty($appoint_id) ? $appoint_id : null;
		$final_appoint_custom = !empty($appoint_custom) ? $appoint_custom : null;

		$cert_emp_id = null;
		$service_rec_id = null;

		function uploadAttachment($fileInputName, $userid)
		{
			if (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] == 0) {

				$uploadDir = "../uploads/work_experience/";
				if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

				$originalName = $_FILES[$fileInputName]['name'];
				$tmpName = $_FILES[$fileInputName]['tmp_name'];
				$newFileName = date("YmdHis") . "_" . preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
				$filePath = $uploadDir . $newFileName;

				if (move_uploaded_file($tmpName, $filePath)) {
					$currentdt = date("Y-m-d H:i:s");
					execsqlSRS(
						"INSERT INTO tbl_Attachment ([att_filename], [att_filepath], [att_dt], [UserID], [IsActive]) VALUES (?, ?, ?, ?, 0)",
						"Insert",
						array($newFileName, $filePath, $currentdt, $userid)
					);

					$getID = execsqlSRS(
						"SELECT TOP 1 [attach_id] FROM tbl_Attachment WHERE [UserID] = ? ORDER BY [attach_id] DESC",
						"Select",
						array($userid)
					);

					if (!empty($getID)) return $getID[0]['attach_id'];
				}
			}

			return null;
		}

		$cert_emp_id     = uploadAttachment('certificate_employment', $userid);
		$service_rec_id  = uploadAttachment('service_record', $userid);

		$insertQuery = "
			INSERT INTO [tbl_ProfExp]
			([start_date], [end_date], [position], [department], [appoint_id], [appoint_custom], [gov_service], [cert_emp], [service_rec], [UserID], [IsActive])
			VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)
		";

		$params = array(
			$start_date,
			$end_date,
			$position,
			$department,
			$final_appoint_id,
			$final_appoint_custom,
			$gov_service,
			$cert_emp_id,
			$service_rec_id,
			$userid
		);

		$result = execsqlSRS($insertQuery, "Insert", $params);

		echo json_encode([
			"status" => "success",
			"message" => "Work experience record successfully saved."
		]);

		break;

	case "viewworkexperience":

		$userid = isset($_POST['userid']) ? intval($_POST['userid']) : 0;

		$experiences = execsqlSRS("
			SELECT exp.[experience_id]
				,exp.[start_date]
				,exp.[end_date]
				,exp.[position]
				,exp.[department]
				,exp.[appoint_id]
				,app.[appoint_desc]
				,exp.[appoint_custom]
				,exp.[gov_service]
				,exp.[cert_emp]
				,exp.[service_rec]
				,exp.[IsActive]
			FROM [tbl_ProfExp] exp

			LEFT JOIN [tbl_ProfExpAppoint] app
				ON app.appoint_id = exp.appoint_id

			WHERE exp.[UserID] = ?
			ORDER BY exp.start_date DESC
		", "Select", array($userid));

		if (!$experiences || count($experiences) == 0) {
			echo "<div class='p-3 text-danger font-weight-bold'>No work experience records found.</div>";
			break;
		}

		echo "<div class='row'>";

		foreach ($experiences as $exp) {

			$position = !empty($exp['position']) ? $exp['position'] : 'N/A';
			$title = !empty($exp['appoint_custom']) ? $exp['appoint_custom'] : $exp['appoint_desc'];
			$start_date = !empty($exp['start_date']) ? date("M d, Y", strtotime($exp['start_date'])) : 'N/A';
			$end_date   = !empty($exp['end_date']) ? date("M d, Y", strtotime($exp['end_date'])) : 'N/A';
			$department = !empty($exp['department']) ? $exp['department'] : 'N/A';
			$gov_service = $exp['gov_service'] === 0 ? 'No' : 'Yes';

			echo "
			<div class='col-lg-4 col-md-6 col-sm-12 fade-in mb-4'>
				<div class='card h-100'>

					<!-- Header now shows Position -->
					<div class='card-header d-flex align-items-center justify-content-center pl-2 pr-2 pt-3 pb-2 bg-success'>
						<span class='h5 text-center font-weight-bold text-white'>{$position}</span>
					</div>

					<div class='card-body d-flex flex-column'>
						<!-- Appointment Title with icon -->
						<div class='d-flex mb-1'>
							<div style='width:20px;text-align:center;'><i class='fas fa-id-badge'></i></div>
							<div><strong class='ml-1'>Appointment:</strong> {$title}</div>
						</div>

						<div class='d-flex mb-1'>
							<div style='width:20px;text-align:center;'><i class='fas fa-calendar-alt'></i></div>
							<div><strong class='ml-1'>Period:</strong> {$start_date} - {$end_date}</div>
						</div>

						<div class='d-flex mb-1'>
							<div style='width:20px;text-align:center;'><i class='fas fa-building'></i></div>
							<div><strong class='ml-1'>Department/Company:</strong> {$department}</div>
						</div>

						<div class='d-flex'>
							<div style='width:20px;text-align:center;'><i class='fas fa-university'></i></div>
							<div><strong class='ml-1'>Government Service:</strong> {$gov_service}</div>
						</div>";

			echo "	<div class='d-flex'>
							<div style='width:20px;text-align:center;'><i class='fas fa-paperclip'></i></div>
							<div><strong class='ml-1'>Attachment/s: </strong>";

			if ($exp['cert_emp']) {

				echo "<button class='btn btn-info btn-sm mr-1'
											  type='button'
											  id='view_attachment'
											  data-datavalue='{$exp['cert_emp']}'
											  data-openmodallabel='View Attachment'
											  >
											<i class='fa-solid fa-paperclip'></i> View
									  </button>";
			}

			if ($exp['service_rec']) {

				echo "<button class='btn btn-info btn-sm mr-1'
											  type='button'
											  id='view_attachment'
											  data-datavalue='{$exp['service_rec']}'
											  data-openmodallabel='View Attachment'
											  >
											<i class='fa-solid fa-paperclip'></i> View
									  </button>";
			}



			echo "			</div>
						</div>

					</div>

					<div class='card-footer'>
						<button class='btn btn-danger btn-block mt-1'
								data-datavalue='{$exp['experience_id']}'
								data-backendurl='backend/bk_profilemanagement.php'
								data-backendrequest='deleteworkexperience'
								id='delete_workexperience'>
							<i class='fas fa-trash'></i> Delete
						</button>
					</div>

				</div>
			</div>
			";
		}

		echo "</div>";

		break;

	case "deleteworkexperience":

		$experience_id = isset($_POST['datavalue']) ? intval($_POST['datavalue']) : 0;

		if ($experience_id <= 0) {
			echo json_encode([
				"status" => "error",
				"message" => "Invalid work experience record."
			]);
			exit;
		}

		$delete = execsqlSRS("
			DELETE FROM [tbl_ProfExp]
			WHERE [experience_id] = :experience_id
		", "Delete", [":experience_id" => $experience_id]);

		echo json_encode([
			"status" => "success",
			"message" => "Work experience record deleted successfully."
		]);

		break;

	case "addvoluntarywork":

		echo '

				<div class="card border border-info shadow">
					<div class="card-title ml-3 mt-3 mb-3 mr-3">
						<i class="fa-solid fa-circle-question text-info"></i>
							<span class="font-weight-bold text-info">Directions:</span>
							<span>Please fill in the required information <span class="text-danger">*</span> for your voluntary work details. File Attachments should not exceed 10MB in size.</span>
					</div>
				</div>

		<div class="p-3 bg-light border rounded border-success shadow" id="voluntaryworkdiv">

			<!-- Name of Organization -->
			<div class="form-group">
				<label for="org_name">Name of Organization <span class="text-danger">*</span></label>
				<input type="text" class="form-control border-success" id="org_name" name="org_name" placeholder="Enter name of organization">
			</div>

			<!-- Organization Address -->
			<div class="form-group">
				<label for="org_address">Organization Address <span class="text-danger">*</span></label>
				<input type="text" class="form-control border-success" id="org_address" name="org_address" placeholder="Enter organization address">
			</div>

			<!-- Start Date -->
			<div class="form-group">
				<label for="org_start_date">Start Date <span class="text-danger">*</span></label>
				<input type="date" class="form-control border-success" id="org_start_date" name="org_start_date" placeholder="Enter start date">
			</div>

			<!-- End Date -->
			<div class="form-group">
				<label for="org_end_date">End Date <span class="text-danger">*</span></label>
				<input type="date" class="form-control border-success" id="org_end_date" name="org_end_date" placeholder="Enter end date">
			</div>

			<!-- Number of Hours -->
			<div class="form-group">
				<label for="org_hours">Number of Hours <span class="text-danger">* (n/a if not applicable)</span></label>
				<input type="text" class="form-control border-success" id="org_hours" name="org_hours" placeholder="Enter number of hours">
			</div>

			<!-- Position/Nature of Work -->
			<div class="form-group">
				<label for="org_position">Position/Nature of Work <span class="text-danger">*</span></label>
				<input type="text" class="form-control border-success" id="org_position" name="org_position" placeholder="Enter position/nature of work">
			</div>

			<!-- Certificate of Employment -->
			<div class="form-group">
				<label for="org_document">
					Supporting Document
					<span class="text-danger">*</span>
				</label>
				<input type="file" class="form-control-file" id="org_document" name="org_document" accept="image/jpeg,image/gif,image/png,application/pdf,image/x-eps">
			</div>

			<!-- Save Button -->
			<div class="form-group mt-3 d-flex justify-content-center">
				<button type="button"
						class="btn btn-success"
						id="save_voluntaryworkdetails"
						>
					Save <i class="fa-solid fa-plus"></i>
				</button>
			</div>

		</div>

		';

		break;

	case "savevoluntarywork":

		$userid = isset($_POST['userid']) ? intval($_POST['userid']) : 0;

		$org_name      = isset($_POST['org_name']) ? trim($_POST['org_name']) : '';
		$org_address   = isset($_POST['org_address']) ? trim($_POST['org_address']) : '';
		$start_date    = isset($_POST['org_start_date']) ? $_POST['org_start_date'] : '';
		$end_date      = isset($_POST['org_end_date']) ? $_POST['org_end_date'] : '';
		$num_hours     = isset($_POST['org_hours']) ? trim($_POST['org_hours']) : null;
		$position      = isset($_POST['org_position']) ? trim($_POST['org_position']) : '';

		if ($userid <= 0) {
			echo json_encode(["status" => "error", "message" => "Invalid user."]);
			exit;
		}

		if (empty($org_name) || empty($org_address) || empty($start_date) || empty($end_date) || empty($position)) {
			echo json_encode(["status" => "error", "message" => "Please fill in all required fields."]);
			exit;
		}

		if (!isset($_FILES['org_document']) || $_FILES['org_document']['error'] != 0) {
			echo json_encode([
				"status" => "error",
				"message" => "Supporting document is required."
			]);
			exit;
		}

		$attach_id = null;

		$uploadDir = "../uploads/voluntary_work/";
		if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

		$originalName = $_FILES['org_document']['name'];
		$tmpName      = $_FILES['org_document']['tmp_name'];
		$fileSize     = $_FILES['org_document']['size'];

		if ($fileSize > 10 * 1024 * 1024) {
			echo json_encode([
				"status" => "error",
				"message" => "File size must not exceed 10MB."
			]);
			exit;
		}

		$newFileName = date("YmdHis") . "_" . preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
		$filePath = $uploadDir . $newFileName;

		if (move_uploaded_file($tmpName, $filePath)) {

			$currentdt = date("Y-m-d H:i:s");

			execsqlSRS(
				"INSERT INTO tbl_Attachment ([att_filename], [att_filepath], [att_dt], [UserID], [IsActive])
				VALUES (?, ?, ?, ?, 0)",
				"Insert",
				array($newFileName, $filePath, $currentdt, $userid)
			);

			$getID = execsqlSRS(
				"SELECT TOP 1 [attach_id]
				FROM [tbl_Attachment]
				WHERE [UserID] = ?
				ORDER BY [attach_id] DESC",
				"Select",
				array($userid)
			);

			if (!empty($getID)) {
				$attach_id = $getID[0]['attach_id'];
			}
		}

		$insertQuery = "
			INSERT INTO [tbl_ProfVolWork]
			([org_name], [org_address], [start_date], [end_date], [num_hours], [position], [attach_id], [UserID], [IsActive])
			VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)
		";

		$params = array(
			$org_name,
			$org_address,
			$start_date,
			$end_date,
			$num_hours,
			$position,
			$attach_id,
			$userid
		);

		execsqlSRS($insertQuery, "Insert", $params);

		echo json_encode([
			"status" => "success",
			"message" => "Voluntary work record successfully saved."
		]);

		break;

	case "viewvoluntarywork":

		$userid = isset($_POST['userid']) ? intval($_POST['userid']) : 0;

		$records = execsqlSRS("
			SELECT vw.[volwork_id]
				,vw.[org_name]
				,vw.[org_address]
				,vw.[start_date]
				,vw.[end_date]
				,vw.[num_hours]
				,vw.[position]
				,vw.[attach_id]
			FROM [tbl_ProfVolWork] vw

			WHERE vw.[UserID] = ?
			ORDER BY vw.[start_date] DESC
		", "Select", array($userid));

		if (!$records || count($records) == 0) {
			echo "<div class='p-3 text-danger font-weight-bold'>No voluntary work records found.</div>";
			break;
		}

		echo "<div class='row'>";

		foreach ($records as $row) {

			$org_name    = !empty($row['org_name']) ? $row['org_name'] : 'N/A';
			$org_address = !empty($row['org_address']) ? $row['org_address'] : 'N/A';
			$start_date  = !empty($row['start_date']) ? date("M d, Y", strtotime($row['start_date'])) : 'N/A';
			$end_date    = !empty($row['end_date']) ? date("M d, Y", strtotime($row['end_date'])) : 'N/A';
			$num_hours   = !empty($row['num_hours']) ? $row['num_hours'] : 'N/A';
			$position    = !empty($row['position']) ? $row['position'] : 'N/A';

			$attach_id   = $row['attach_id'];

			echo "
			<div class='col-lg-4 col-md-6 col-sm-12 fade-in mb-4'>
				<div class='card h-100'>

					<div class='card-header d-flex align-items-center justify-content-center pl-2 pr-2 pt-3 pb-2 bg-success'>
						<span class='h5 text-center font-weight-bold text-white'>{$position}</span>
					</div>

					<div class='card-body d-flex flex-column'>

						<div class='d-flex mb-1'>
							<div style='width:20px;text-align:center;'><i class='fas fa-building'></i></div>
							<div><strong class='ml-1'>Organization:</strong> {$org_name}</div>
						</div>

						<div class='d-flex mb-1'>
							<div style='width:20px;text-align:center;'><i class='fas fa-map-marker-alt'></i></div>
							<div><strong class='ml-1'>Address:</strong> {$org_address}</div>
						</div>

						<div class='d-flex mb-1'>
							<div style='width:20px;text-align:center;'><i class='fas fa-calendar-alt'></i></div>
							<div><strong class='ml-1'>Period:</strong> {$start_date} - {$end_date}</div>
						</div>

						<div class='d-flex'>
							<div style='width:20px;text-align:center;'><i class='fas fa-clock'></i></div>
							<div><strong class='ml-1'>Hours:</strong> {$num_hours}</div>
						</div>";

			echo "	<div class='d-flex'>
							<div style='width:20px;text-align:center;'><i class='fas fa-paperclip'></i></div>
							<div><strong class='ml-1'>Attachment/s: </strong>";

			echo "<button class='btn btn-info btn-sm mr-1'
											  type='button'
											  id='view_attachment'
											  data-datavalue='{$attach_id}'
											  data-openmodallabel='View Attachment'
											  >
											<i class='fa-solid fa-paperclip'></i> View
									  </button>";

			echo "			</div>
						</div>

					</div>

					<div class='card-footer'>
						<button class='btn btn-danger btn-block mt-1'
								data-datavalue='{$row['volwork_id']}'
								data-backendurl='backend/bk_profilemanagement.php'
								data-backendrequest='deletevoluntarywork'
								id='delete_voluntarywork'>
							<i class='fas fa-trash'></i> Delete
						</button>
					</div>

				</div>
			</div>
			";
		}

		echo "</div>";

		break;

	case "deletevoluntarywork":

		$volwork_id = isset($_POST['datavalue']) ? intval($_POST['datavalue']) : 0;

		if ($volwork_id <= 0) {
			echo json_encode([
				"status" => "error",
				"message" => "Invalid voluntary work record."
			]);
			exit;
		}

		$delete = execsqlSRS("
			DELETE FROM [tbl_ProfVolWork]
			WHERE [volwork_id] = :volwork_id
		", "Delete", [":volwork_id" => $volwork_id]);

		echo json_encode([
			"status" => "success",
			"message" => "Voluntary work record deleted successfully."
		]);

		break;

	case "addlearningdevelopment":

		echo '

				<div class="card border border-info shadow">
					<div class="card-title ml-3 mt-3 mb-3 mr-3">
						<i class="fa-solid fa-circle-question text-info"></i>
							<span class="font-weight-bold text-info">Directions:</span>
							<span>Please fill in the required information <span class="text-danger">*</span> for your L&D details. File Attachments should not exceed 10MB in size. If your L&D type is not in the list, click on the <button class="btn btn-info btn-sm"><i class="fa-solid fa-circle-question"></i></button> button.</span>
					</div>
				</div>

		<div class="p-3 bg-light border rounded shadow-sm" id="learningdevelopmentdiv">

			<!-- Title of Learning & Development -->
			<div class="form-group">
				<label for="ld_title">Title of Learning & Development <span class="text-danger">*</span></label>
				<input type="text" class="form-control border-success" id="ld_title" name="ld_title" placeholder="Enter training/seminar title">
			</div>

			<!-- Start Date -->
			<div class="form-group">
				<label for="ld_start_date">Start Date <span class="text-danger">*</span></label>
				<input type="date" class="form-control border-success" id="ld_start_date" name="ld_start_date">
			</div>

			<!-- End Date -->
			<div class="form-group">
				<label for="ld_end_date">End Date <span class="text-danger">*</span></label>
				<input type="date" class="form-control border-success" id="ld_end_date" name="ld_end_date">
			</div>

			<!-- Number of Hours -->
			<div class="form-group">
				<label for="ld_num_hours">Number of Hours <span class="text-danger">*</span></label>
				<input type="number" class="form-control border-success" id="ld_num_hours" name="ld_num_hours" placeholder="Enter number of hours">
			</div>

			<!-- Type of L&D with toggle -->
			<div class="form-group" id="ldtype-group">
				<label for="ldtype_id">
					Type of L&D <span class="text-danger">*</span>
					<button class="btn btn-info btn-sm" id="toggleLDTypeBtn" data-tooltip="Type not in the list?">
						<i class="fas fa-question-circle"></i>
					</button>
				</label>

				<!-- Dropdown -->
				<select class="form-control border-success" id="ldtype_id" name="ldtype_id">
					<option value="">--Select Type of L&D--</option><hr>';

		$ldtypes = execsqlSRS("
						SELECT [ldtype_id],
							[ldtype_desc],
							[IsActive]
						FROM [tbl_ProfLDType]
						WHERE [IsActive] = 0
						ORDER BY [ldtype_desc]
					", "Select", array());

		foreach ($ldtypes as $ld) {
			echo '<option value="' . htmlspecialchars($ld['ldtype_id']) . '">' . htmlspecialchars($ld['ldtype_desc']) . '</option>';
		}

		echo '	</select>

				<!-- Hidden custom input -->
				<input type="text" class="form-control border-success" id="ldtype_custom" name="ldtype_custom" placeholder="Enter custom L&D type" style="display:none;">
			</div>

			<!-- Conducted / Sponsored By -->
			<div class="form-group">
				<label for="ld_sponsor">Conducted / Sponsored By <span class="text-danger">*</span></label>
				<input type="text" class="form-control border-success" id="ld_sponsor" name="ld_sponsor" placeholder="Enter sponsor/organization">
			</div>

			<!-- Certificate of Attendance/Participation/Completion -->
			<div class="form-group">
				<label for="ld_attachmentattendance">
					Certificate of Attendance/Participation/Completion/etc.
					<span class="text-danger">*</span>
				</label>
				<input type="file" class="form-control-file" id="ld_attachmentattendance" name="ld_attachmentattendance" accept="image/jpeg,image/gif,image/png,application/pdf,image/x-eps">
			</div>

			<!-- Certificate of Attendance/Participation/Completion -->
			<div class="form-group">
				<label for="ld_attachmentskills">
					Skills Certificate
					<span class="text-danger">(optional)</span>
				</label>
				<input type="file" class="form-control-file" id="ld_attachmentskills" name="ld_attachmentskills" accept="image/jpeg,image/gif,image/png,application/pdf,image/x-eps">
			</div>

			<!-- Save Button -->
			<div class="form-group mt-3 d-flex justify-content-center">
				<button type="button"
						class="btn btn-success"
						id="save_learningdevelopmentdetails"
						>
					Save <i class="fa-solid fa-plus"></i>
				</button>
			</div>

		</div>

		<script>
			$(document).off("click", "#toggleLDTypeBtn").on("click", "#toggleLDTypeBtn", function(){
				var $group = $(this).closest("#ldtype-group");
				var $select = $group.find("#ldtype_id");
				var $input = $group.find("#ldtype_custom");

				if($input.is(":visible")){
					$input.slideUp(200, function(){
						$input.val("");
						$select.slideDown(200);
					});
				} else {
					$select.slideUp(200, function(){
						$select.val("");
						$input.slideDown(200, function(){ $input.focus(); });
					});
				}
			});
		</script>
		';

		break;

	case "savelearningdevelopment":

		$userid = isset($_POST['userid']) ? intval($_POST['userid']) : 0;

		$ld_title   = isset($_POST['ld_title']) ? trim($_POST['ld_title']) : '';
		$start_date = isset($_POST['ld_start_date']) ? $_POST['ld_start_date'] : '';
		$end_date   = isset($_POST['ld_end_date']) ? $_POST['ld_end_date'] : '';
		$num_hours  = isset($_POST['ld_num_hours']) ? intval($_POST['ld_num_hours']) : 0;

		$ldtype_id     = isset($_POST['ldtype_id']) ? trim($_POST['ldtype_id']) : '';
		$ldtype_custom = isset($_POST['ldtype_custom']) ? trim($_POST['ldtype_custom']) : '';

		$sponsor = isset($_POST['ld_sponsor']) ? trim($_POST['ld_sponsor']) : '';

		if ($userid <= 0) {
			echo json_encode(["status" => "error", "message" => "Invalid user."]);
			exit;
		}

		if (!empty($ldtype_id) && !empty($ldtype_custom)) {
			echo json_encode(["status" => "error", "message" => "Choose only one type of L&D input."]);
			exit;
		}
		if (empty($ldtype_id) && empty($ldtype_custom)) {
			echo json_encode(["status" => "error", "message" => "Please select or enter type of L&D."]);
			exit;
		}

		if (empty($ld_title) || empty($start_date) || empty($end_date) || $num_hours <= 0 || empty($sponsor)) {
			echo json_encode(["status" => "error", "message" => "Please fill in all required fields."]);
			exit;
		}

		$final_ldtype_id     = !empty($ldtype_id) ? $ldtype_id : null;
		$final_ldtype_custom = !empty($ldtype_custom) ? $ldtype_custom : null;

		function uploadAttachment($fileInputName, $userid, $required = false, $friendlyName = "Supporting document")
		{
			if (!isset($_FILES[$fileInputName]) || $_FILES[$fileInputName]['error'] != 0) {
				if ($required) {
					echo json_encode([
						"status" => "error",
						"message" => "{$friendlyName} is required."
					]);
					exit;
				} else {
					return null;
				}
			}

			$fileSize = $_FILES[$fileInputName]['size'];
			if ($fileSize > 10 * 1024 * 1024) {
				echo json_encode([
					"status" => "error",
					"message" => "{$friendlyName} must not exceed 10MB."
				]);
				exit;
			}

			$uploadDir = "../uploads/learning_development/";
			if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

			$originalName = $_FILES[$fileInputName]['name'];
			$tmpName = $_FILES[$fileInputName]['tmp_name'];
			$newFileName = date("YmdHis") . "_" . preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
			$filePath = $uploadDir . $newFileName;

			if (move_uploaded_file($tmpName, $filePath)) {
				$currentdt = date("Y-m-d H:i:s");
				execsqlSRS(
					"INSERT INTO tbl_Attachment ([att_filename], [att_filepath], [att_dt], [UserID], [IsActive]) VALUES (?, ?, ?, ?, 0)",
					"Insert",
					array($newFileName, $filePath, $currentdt, $userid)
				);

				$getID = execsqlSRS(
					"SELECT TOP 1 [attach_id] FROM tbl_Attachment WHERE [UserID] = ? ORDER BY [attach_id] DESC",
					"Select",
					array($userid)
				);

				if (!empty($getID)) return $getID[0]['attach_id'];
			}

			return null;
		}

		$cert_attendance_id = uploadAttachment('ld_attachmentattendance', $userid, true, "Certificate of Attendance");
		$skills_certificate_id = uploadAttachment('ld_attachmentskills', $userid, false, "Skills Certificate");

		$insertQuery = "
			INSERT INTO [tbl_ProfLD]
			([ld_title], [start_date], [end_date], [num_hours], [ldtype_id], [ldtype_custom], [sponsor], [UserID], [cert_attendance], [skills_certificate], [IsActive])
			VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)
		";

		$params = array(
			$ld_title,
			$start_date,
			$end_date,
			$num_hours,
			$final_ldtype_id,
			$final_ldtype_custom,
			$sponsor,
			$userid,
			$cert_attendance_id,
			$skills_certificate_id
		);

		execsqlSRS($insertQuery, "Insert", $params);

		echo json_encode([
			"status" => "success",
			"message" => "Learning & Development record successfully saved."
		]);

		break;

	case "viewlearningdevelopment":

		$userid = isset($_POST['userid']) ? intval($_POST['userid']) : 0;

		$ld_records = execsqlSRS("
			SELECT ld.[ld_id],
				ld.[ld_title],
				ld.[start_date],
				ld.[end_date],
				ld.[num_hours],
				ld.[ldtype_id],
				ldt.[ldtype_desc],
				ld.[ldtype_custom],
				ld.[sponsor],
				ld.[cert_attendance],
				ld.[skills_certificate],
				ld.[IsActive]
			FROM [tbl_ProfLD] ld
			LEFT JOIN [tbl_ProfLDType] ldt
				ON ldt.ldtype_id = ld.ldtype_id
			WHERE ld.UserID = ?
			ORDER BY ld.start_date DESC
		", "Select", array($userid));

		if (!$ld_records || count($ld_records) == 0) {
			echo "<div class='p-3 text-danger font-weight-bold'>No Learning & Development records found.</div>";
			break;
		}

		echo "<div class='row'>";

		foreach ($ld_records as $ld) {

			$title = !empty($ld['ld_title']) ? $ld['ld_title'] : 'N/A';
			$ldtype = !empty($ld['ldtype_custom']) ? $ld['ldtype_custom'] : (!empty($ld['ldtype_desc']) ? $ld['ldtype_desc'] : 'N/A');
			$start_date = !empty($ld['start_date']) ? date("M d, Y", strtotime($ld['start_date'])) : 'N/A';
			$end_date   = !empty($ld['end_date']) ? date("M d, Y", strtotime($ld['end_date'])) : 'N/A';
			$num_hours  = !empty($ld['num_hours']) ? $ld['num_hours'] : 'N/A';
			$sponsor    = !empty($ld['sponsor']) ? $ld['sponsor'] : 'N/A';

			echo "
			<div class='col-lg-4 col-md-6 col-sm-12 fade-in mb-4'>
				<div class='card h-100'>

					<!-- Header: L&D Title -->
					<div class='card-header d-flex align-items-center justify-content-center pl-2 pr-2 pt-3 pb-2 bg-success'>
						<span class='h5 text-center font-weight-bold text-white'>{$title}</span>
					</div>

					<div class='card-body d-flex flex-column'>
						<div class='d-flex mb-1'>
							<div style='width:20px;text-align:center;'><i class='fas fa-list'></i></div>
							<div><strong class='ml-1'>Type:</strong> {$ldtype}</div>
						</div>

						<div class='d-flex mb-1'>
							<div style='width:20px;text-align:center;'><i class='fas fa-calendar-alt'></i></div>
							<div><strong class='ml-1'>Period:</strong> {$start_date} - {$end_date}</div>
						</div>

						<div class='d-flex mb-1'>
							<div style='width:20px;text-align:center;'><i class='fas fa-clock'></i></div>
							<div><strong class='ml-1'>Hours:</strong> {$num_hours}</div>
						</div>

						<div class='d-flex mb-1'>
							<div style='width:20px;text-align:center;'><i class='fas fa-handshake'></i></div>
							<div><strong class='ml-1'>Sponsor:</strong> {$sponsor}</div>
						</div>";

			echo "	<div class='d-flex'>
							<div style='width:20px;text-align:center;'><i class='fas fa-paperclip'></i></div>
							<div><strong class='ml-1'>Attachment/s: </strong>";

			echo "<button class='btn btn-info btn-sm mr-1'
											  type='button'
											  id='view_attachment'
											  data-datavalue='{$ld['cert_attendance']}'
											  data-openmodallabel='View Attachment'
											  >
											<i class='fa-solid fa-paperclip'></i> View
									  </button>";

			if ($ld['skills_certificate']) {

				echo "<button class='btn btn-info btn-sm mr-1'
											  type='button'
											  id='view_attachment'
											  data-datavalue='{$ld['skills_certificate']}'
											  data-openmodallabel='View Attachment'
											  >
											<i class='fa-solid fa-paperclip'></i> View
									  </button>";
			}

			echo "			</div>
						</div>

					</div>

					<div class='card-footer'>
						<button class='btn btn-danger btn-block mt-1'
								data-datavalue='{$ld['ld_id']}'
								data-backendurl='backend/bk_profilemanagement.php'
								data-backendrequest='deletelearningdevelopment'
								id='delete_learningdevelopment'>
							<i class='fas fa-trash'></i> Delete
						</button>
					</div>

				</div>
			</div>
			";
		}

		echo "</div>";

		break;

	case "deletelearningdevelopment":

		$ld_id = isset($_POST['datavalue']) ? intval($_POST['datavalue']) : 0;

		if ($ld_id <= 0) {
			echo json_encode([
				"status" => "error",
				"message" => "Invalid Learning & Development record."
			]);
			exit;
		}

		$delete = execsqlSRS("
			DELETE FROM [tbl_ProfLD]
			WHERE [ld_id] = :ld_id
		", "Delete", [":ld_id" => $ld_id]);

		echo json_encode([
			"status" => "success",
			"message" => "Learning & Development record deleted successfully."
		]);
		break;

	case "viewskills":
		$userid = isset($_POST['userid']) ? intval($_POST['userid']) : 0;

		if ($userid <= 0) {
			echo json_encode([]);
			exit;
		}

		$skills = execsqlSRS("
			SELECT [skills_id], [skills_desc]
			FROM [tbl_ProfSkills]
			WHERE [UserID] = :userid AND [IsActive] = 0
			ORDER BY [skills_id]
		", "Select", [":userid" => $userid]);

		echo "<ul class='list-group mb-3' style='max-width: 500px;'>";

		foreach ($skills as $skill) {

			echo "
			<li class='list-group-item d-flex justify-content-between align-items-center fade-in'>
				<span>
					<i class='fas fa-circle text-success mr-2' style='font-size: 0.6rem;'></i>
					{$skill['skills_desc']}
				</span>
				<button class='btn btn-sm btn-danger'
						data-datavalue='{$skill['skills_id']}'
						data-backendurl='backend/bk_profilemanagement.php'
						data-backendrequest='deleteskill'
						id='delete_skill'>
					<span class='text-nowrap'><i class='fas fa-trash'></i> Delete</span>
				</button>
			</li>
			";
		}

		echo "</ul>";

		break;

	case "saveskill":

		$userid = isset($_POST['userid']) ? intval($_POST['userid']) : 0;
		$skill_desc = isset($_POST['skill_desc']) ? trim($_POST['skill_desc']) : '';

		if ($userid <= 0 || $skill_desc === '') {
			echo json_encode([
				"status" => "error",
				"message" => "Invalid input."
			]);
			exit;
		}

		if (!preg_match("/^[a-zA-Z\s\.\,\']+$/", $skill_desc)) {
			echo json_encode([
				"status" => "error",
				"message" => "Skill can only contain letters, spaces, dots, commas, and apostrophes."
			]);
			exit;
		}

		$insert = execsqlSRS("
			INSERT INTO [tbl_ProfSkills] ([skills_desc], [UserID], [IsActive])
			VALUES (:skill_desc, :userid, 0)
		", "Insert", [":skill_desc" => $skill_desc, ":userid" => $userid]);

		echo json_encode([
			"status" => "success",
			"message" => "Skill added successfully."
		]);

		break;

	case "deleteskill":

		$skill_id = isset($_POST['datavalue']) ? intval($_POST['datavalue']) : 0;

		if ($skill_id <= 0) {
			echo json_encode([
				"status" => "error",
				"message" => "Invalid skill record."
			]);
			exit;
		}

		$delete = execsqlSRS("
			DELETE FROM [tbl_ProfSkills]
			WHERE [skills_id] = :skill_id
		", "Delete", [":skill_id" => $skill_id]);

		echo json_encode([
			"status" => "success",
			"message" => "Skill deleted successfully."
		]);

		break;

	case "savenonacademic":

		$userid = isset($_POST['userid']) ? intval($_POST['userid']) : 0;
		$desc   = isset($_POST['ndr_input']) ? trim($_POST['ndr_input']) : '';

		if ($userid <= 0 || $desc === '') {
			echo json_encode(["status" => "error", "message" => "Input is required."]);
			exit;
		}

		if (!preg_match("/^[a-zA-Z0-9\s]+$/", $desc)) {
			echo json_encode(["status" => "error", "message" => "Only letters and numbers are allowed."]);
			exit;
		}

		if (!isset($_FILES['ndr_file']) || $_FILES['ndr_file']['error'] != 0) {
			echo json_encode([
				"status" => "error",
				"message" => "Supporting document is required."
			]);
			exit;
		}

		$attach_id = null;

		if (isset($_FILES['ndr_file']) && $_FILES['ndr_file']['error'] == 0) {

			$file = $_FILES['ndr_file'];

			if ($file['size'] > (10 * 1024 * 1024)) {
				echo json_encode(["status" => "error", "message" => "File must not exceed 10MB."]);
				exit;
			}

			$uploadDir = "../uploads/non_acads/";
			if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

			$originalName = preg_replace("/[^a-zA-Z0-9.\-_]/", "_", basename($file['name']));
			$timestamp    = date("Ymd_His");
			$unique       = uniqid();
			$filename     = $timestamp . "_" . $unique . "_" . $originalName;
			$filepath     = $uploadDir . $filename;

			if (!move_uploaded_file($file['tmp_name'], $filepath)) {
				echo json_encode(["status" => "error", "message" => "File upload failed."]);
				exit;
			}

			$currentdt = date("Y-m-d H:i:s");

			$insertfile = execsqlSRS("
				INSERT INTO [tbl_Attachment]
				([att_filename], [att_filepath], [att_dt], [UserID], [IsActive])
				VALUES (:fname, :fpath, :attdt, :userid, 0)
			", "Insert", [
				":fname"  => $filename,
				":fpath"  => $filepath,
				":attdt"  => $currentdt,
				":userid" => $userid
			]);

			$row = execsqlSRS("
				SELECT TOP 1 [attach_id]
				FROM [tbl_Attachment]
				WHERE [UserID] = :userid
				AND [att_filename] = :fname
				AND [att_dt] = :attdt
				AND [IsActive] = 0
				ORDER BY [attach_id] DESC
			", "SelectOne", [
				":userid" => $userid,
				":fname"  => $filename,
				":attdt"  => $currentdt
			]);

			if ($row) {
				$attach_id = $row[0]['attach_id'];
			}
		}

		execsqlSRS("
			INSERT INTO [tbl_ProfNonAcad]
			([nonacad_desc], [UserID], [attach_id], [IsActive])
			VALUES (:desc, :userid, :attach_id, 0)
		", "Insert", [
			":desc"      => $desc,
			":userid"    => $userid,
			":attach_id" => $attach_id
		]);

		echo json_encode(["status" => "success", "message" => "Saved successfully."]);

		break;

	case "viewndr":
		$userid = isset($_POST['userid']) ? intval($_POST['userid']) : 0;

		if ($userid <= 0) {
			echo json_encode([]);
			exit;
		}

		$ndrs = execsqlSRS("
			SELECT 	 [nonacad_id]
					,[nonacad_desc]
					,[UserID]
					,[attach_id]
					,[IsActive]
			FROM [tbl_ProfNonAcad]
			WHERE [UserID] = :userid AND [IsActive] = 0
			ORDER BY [nonacad_id]
		", "Select", [":userid" => $userid]);

		echo "<ul class='list-group mb-3' style='max-width: 500px;'>";

		foreach ($ndrs as $ndr) {

			echo "
				<li class='list-group-item d-flex justify-content-between align-items-center fade-in'>
					<span>
						<i class='fas fa-circle text-success mr-2' style='font-size: 0.6rem;'></i>
						{$ndr['nonacad_desc']}
					</span>
					<div class='btn-group btn-group-sm' role='group'>
						<button class='btn btn-info'
								data-datavalue='{$ndr['attach_id']}'
								id='view_attachment'>
							<i class='fas fa-paperclip'></i> View
						</button>
						<button class='btn btn-danger'
								data-datavalue='{$ndr['nonacad_id']}'
								data-backendurl='backend/bk_profilemanagement.php'
								data-backendrequest='deletendr'
								id='delete_ndr'>
							<i class='fas fa-trash'></i> Delete
						</button>
					</div>
				</li>
			";
		}

		echo "</ul>";

		break;

	case "deletendr":

		$ndr_id = isset($_POST['datavalue']) ? intval($_POST['datavalue']) : 0;

		if ($ndr_id <= 0) {
			echo json_encode([
				"status" => "error",
				"message" => "Invalid record."
			]);
			exit;
		}

		$delete = execsqlSRS("
			DELETE FROM [tbl_ProfNonAcad]
			WHERE [nonacad_id] = :ndr_id
		", "Delete", [":ndr_id" => $ndr_id]);

		echo json_encode([
			"status" => "success",
			"message" => "Record deleted successfully."
		]);
		break;

	case "savemembership":

		$userid = isset($_POST['userid']) ? intval($_POST['userid']) : 0;
		$desc   = isset($_POST['membership_input']) ? trim($_POST['membership_input']) : '';

		if ($userid <= 0 || $desc === '') {
			echo json_encode([
				"status" => "error",
				"message" => "Membership input is required."
			]);
			exit;
		}

		if (!isset($_FILES['membership_file']) || $_FILES['membership_file']['error'] != 0) {
			echo json_encode([
				"status" => "error",
				"message" => "Supporting document is required."
			]);
			exit;
		}

		$file = $_FILES['membership_file'];

		if ($file['size'] > 10 * 1024 * 1024) {
			echo json_encode([
				"status" => "error",
				"message" => "File must not exceed 10MB."
			]);
			exit;
		}

		$uploadDir = "../uploads/assoc_org/";
		if (!is_dir($uploadDir)) {
			mkdir($uploadDir, 0777, true);
		}

		$originalName = preg_replace("/[^a-zA-Z0-9.\-_]/", "_", basename($file['name']));
		$timestamp    = date("Ymd_His");
		$unique       = uniqid();
		$filename     = $timestamp . "_" . $unique . "_" . $originalName;
		$filepath     = $uploadDir . $filename;

		if (!move_uploaded_file($file['tmp_name'], $filepath)) {
			echo json_encode([
				"status" => "error",
				"message" => "File upload failed."
			]);
			exit;
		}

		$currentdt = date("Y-m-d H:i:s");

		execsqlSRS("
			INSERT INTO [tbl_Attachment]
			([att_filename], [att_filepath], [att_dt], [UserID], [IsActive])
			VALUES (:fname, :fpath, :attdt, :userid, 0)
		", "Insert", [
			":fname"  => $filename,
			":fpath"  => $filepath,
			":attdt"  => $currentdt,
			":userid" => $userid
		]);

		$row = execsqlSRS("
			SELECT TOP 1 [attach_id]
			FROM [tbl_Attachment]
			WHERE [UserID] = :userid
			AND [att_filename] = :fname
			AND [att_dt] = :attdt
			AND [IsActive] = 0
			ORDER BY [attach_id] DESC
		", "SelectOne", [
			":userid" => $userid,
			":fname"  => $filename,
			":attdt"  => $currentdt
		]);

		$attach_id = $row ? $row[0]['attach_id'] : null;

		execsqlSRS("
			INSERT INTO [tbl_ProfOrgAssoc]
			([orgassoc_desc], [UserID], [attach_id], [IsActive])
			VALUES (:desc, :userid, :attach_id, 0)
		", "Insert", [
			":desc"      => $desc,
			":userid"    => $userid,
			":attach_id" => $attach_id
		]);

		echo json_encode([
			"status" => "success",
			"message" => "Membership saved successfully."
		]);

		break;

	case "viewmembership":
		$userid = isset($_POST['userid']) ? intval($_POST['userid']) : 0;

		if ($userid <= 0) {
			echo json_encode([]);
			exit;
		}

		$memberships = execsqlSRS("
			SELECT orgassoc_id, orgassoc_desc, attach_id
			FROM tbl_ProfOrgAssoc
			WHERE UserID = :userid AND IsActive = 0
			ORDER BY orgassoc_id
		", "Select", [":userid" => $userid]);

		echo "<ul class='list-group mb-3' style='max-width: 500px;'>";

		foreach ($memberships as $m) {

			echo "
				<li class='list-group-item d-flex justify-content-between align-items-center fade-in'>
					<span>
						<i class='fas fa-circle text-success mr-2' style='font-size: 0.6rem;'></i>
						{$m['orgassoc_desc']}
					</span>
					<div class='btn-group btn-group-sm' role='group'>
						<button class='btn btn-info'
								data-datavalue='{$m['attach_id']}'
								id='view_attachment'>
							<i class='fas fa-paperclip'></i> View
						</button>
						<button class='btn btn-danger'
								data-datavalue='{$m['orgassoc_id']}'
								data-backendurl='backend/bk_profilemanagement.php'
								data-backendrequest='deletemembership'
								id='delete_membership'>
							<i class='fas fa-trash'></i> Delete
						</button>
					</div>
				</li>
			";
		}

		echo "</ul>";

		break;

	case "deletemembership":

		$orgassoc_id = isset($_POST['datavalue']) ? intval($_POST['datavalue']) : 0;

		if ($orgassoc_id <= 0) {
			echo json_encode([
				"status" => "error",
				"message" => "Invalid membership record."
			]);
			exit;
		}

		$delete = execsqlSRS("
			DELETE FROM [tbl_ProfOrgAssoc]
			WHERE [orgassoc_id] = :orgassoc_id
		", "Delete", [":orgassoc_id" => $orgassoc_id]);

		echo json_encode([
			"status" => "success",
			"message" => "Membership record deleted successfully."
		]);

		break;

	case "savepdsfiles":

		$perf_rating = isset($_POST["perf_rating"]) ? $_POST["perf_rating"] : "";
		$adj_rating = isset($_POST["adj_rating"]) ? $_POST["adj_rating"] : "";

		$userid = isset($_POST['userid']) ? intval($_POST['userid']) : 0;
		if ($userid <= 0) {
			echo json_encode(['status' => 'error', 'message' => 'Invalid user ID.']);
			break;
		}

		$currentdt = date('Y-m-d H:i:s');

		$fileMap = [
			'pds_file'     => 'Personal Data Sheet',
			'workexp_file' => 'Work Experience Sheet',
			'perf_file'    => 'Performance Rating'
		];

		$uploadedFileKey = null;
		$fileDesc = null;

		foreach ($fileMap as $key => $desc) {
			if (isset($_FILES[$key])) {
				$uploadedFileKey = $key;
				$fileDesc = $desc;

				switch ($_FILES[$key]['error']) {
					case UPLOAD_ERR_OK:

						$maxSize = 10 * 1024 * 1024;
						if ($_FILES[$key]['size'] > $maxSize) {
							echo json_encode([
								'status' => 'error',
								'message' => "$fileDesc exceeds 10MB."
							]);
							exit;
						}
						break;

					case UPLOAD_ERR_INI_SIZE:
					case UPLOAD_ERR_FORM_SIZE:
						echo json_encode([
							'status' => 'error',
							'message' => "$fileDesc exceeds 10MB."
						]);
						exit;

					case UPLOAD_ERR_NO_FILE:
						echo json_encode([
							'status' => 'error',
							'message' => "$fileDesc is required."
						]);
						exit;

					default:
						echo json_encode([
							'status' => 'error',
							'message' => "Failed to upload $fileDesc."
						]);
						exit;
				}

				break;
			}
		}

		if (!$uploadedFileKey) {
			echo json_encode([
				'status' => 'error',
				'message' => 'No file uploaded.'
			]);
			exit;
		}

		$uploadDir = "../uploads/pdsfiles/";
		$originalName = basename($_FILES[$uploadedFileKey]['name']);
		$uniqueName = time() . '_' . $uploadedFileKey . '_' . uniqid() . '_' . $originalName;
		$filePath = $uploadDir . $uniqueName;

		if (!move_uploaded_file($_FILES[$uploadedFileKey]['tmp_name'], $filePath)) {
			echo json_encode(['status' => 'error', 'message' => "Failed to upload {$fileDesc}."]);
			exit;
		}

		$insertAttach = execsqlSRS("
			INSERT INTO [tbl_Attachment]
			([att_filename],[att_filepath],[att_dt],[UserID],[IsActive])
			VALUES
			(:filename,:filepath,:att_dt,:userid,0)
		", "Insert", [
			":filename" => $uniqueName,
			":filepath" => $filePath,
			":att_dt"   => $currentdt,
			":userid"   => $userid
		]);

		$latestAttach = execsqlSRS("
			SELECT TOP 1 attach_id
			FROM [tbl_Attachment]
			WHERE att_filename = :filename AND att_dt = :att_dt AND UserID = :userid
			ORDER BY attach_id DESC
		", "Select", [
			":filename" => $uniqueName,
			":att_dt"   => $currentdt,
			":userid"   => $userid
		]);

		if (empty($latestAttach)) {
			echo json_encode(['status' => 'error', 'message' => "Failed to get attachment ID."]);
			exit;
		}

		$attachID = $latestAttach[0]['attach_id'];

		$existingRow = execsqlSRS("
			SELECT [pds_file], [workexp_file], [perf_file]
			FROM [tbl_ProfUserDetails]
			WHERE UserID = :userid
		", "Select", [":userid" => $userid]);

		if (!empty($existingRow)) {

			$updateQuery = "UPDATE [tbl_ProfUserDetails] SET [$uploadedFileKey] = :attach_id";

			$updateParams = [
				":attach_id" => $attachID,
				":userid"    => $userid
			];

			if ($uploadedFileKey === "perf_file") {
				$updateQuery .= ", perf_rating = :perf_rating, adj_rating = :adj_rating";

				$updateParams[":perf_rating"] = $perf_rating;
				$updateParams[":adj_rating"] = $adj_rating;
			}

			$updateQuery .= " WHERE UserID = :userid";

			$update = execsqlSRS($updateQuery, "Update", $updateParams);
		} else {

			$columns = [$uploadedFileKey, 'UserID', 'IsActive'];
			$values  = [':attach_id', ':userid', 0];
			$params  = [
				":attach_id" => $attachID,
				":userid"    => $userid
			];

			if ($uploadedFileKey === "perf_file") {
				$columns[] = "perf_rating";
				$columns[] = "adj_rating";

				$values[] = ":perf_rating";
				$values[] = ":adj_rating";

				$params[":perf_rating"] = $perf_rating;
				$params[":adj_rating"] = $adj_rating;
			}

			$insertQuery = "INSERT INTO [tbl_ProfUserDetails] (" . implode(',', $columns) . ") VALUES (" . implode(',', $values) . ")";
			$insert = execsqlSRS($insertQuery, "Insert", $params);
		}

		echo json_encode([
			'status' => 'success',
			'message' => "$fileDesc saved successfully.",
			'attachid' => $latestAttach[0]['attach_id']
		]);
		break;

	case "saveanswers":

		$userid = intval($_POST['userid'] ?? 0);

		if ($userid <= 0) {
			echo json_encode([
				'status' => 'error',
				'message' => 'Invalid user.'
			]);
			exit;
		}

		$questions = array_filter(array_keys($_POST), function ($k) {
			return preg_match('/^q\d+[a-e]?$/', $k);
		});

		try {

			foreach ($questions as $q) {

				$answer = $_POST[$q] ?? null;

				if (!$answer) {
					throw new Exception("Some questions are not answered.");
				}

				$answerLower = strtolower(trim($answer));

				// =========================
				// q35b SPECIAL CASE
				// =========================
				if ($q === 'q35b') {

					$date   = trim($_POST['q35b_date'] ?? '');
					$status = trim($_POST['q35b_status'] ?? '');

					if ($answerLower !== 'no') {
						if ($date === '' || $status === '') {
							throw new Exception("Some required details are missing.");
						}

						$details = json_encode([
							'date_filed' => $date,
							'status' => $status
						]);
					} else {
						$details = null;
					}
				} else {

					$details = trim($_POST[$q . '_details'] ?? '');

					// =========================
					// ONLY EXCEPTION: q40d / q40e
					// =========================
					if (in_array($q, ['q40d', 'q40e'])) {

						// allow YES even if details empty
						if ($details === '') {
							$details = null;
						}
					} else {

						// NORMAL RULE FOR ALL OTHERS
						if ($answerLower !== 'no') {
							if ($details === '') {
								throw new Exception("Some required details are missing.");
							}
						} else {
							$details = null;
						}
					}
				}

				// =========================
				// CHECK EXISTING RECORD
				// =========================
				$existing = execsqlSRS("
                SELECT [answer_id]
                FROM [tbl_ProfAnswers]
                WHERE [UserID] = :userid
                AND [question_code] = :qcode
                AND [IsActive] = 0
            ", "Select", [
					":userid" => $userid,
					":qcode" => $q
				]);

				if (!empty($existing)) {

					execsqlSRS("
                    UPDATE [tbl_ProfAnswers]
                    SET [answer] = :answer,
                        [answer_details] = :details
                    WHERE [answer_id] = :aid
                ", "Update", [
						":answer" => $answer,
						":details" => $details,
						":aid" => $existing[0]['answer_id']
					]);
				} else {

					execsqlSRS("
                    INSERT INTO [tbl_ProfAnswers]
                        ([UserID], [question_code], [answer], [answer_details], [IsActive])
                    VALUES
                        (:userid, :qcode, :answer, :details, 0)
                ", "Insert", [
						":userid" => $userid,
						":qcode" => $q,
						":answer" => $answer,
						":details" => $details
					]);
				}
			}

			echo json_encode([
				'status' => 'success',
				'message' => 'Answers saved successfully.'
			]);
		} catch (Exception $e) {

			echo json_encode([
				'status' => 'error',
				'message' => $e->getMessage()
			]);
		}

		break;

	case "viewcomp":

		$userid = isset($_POST['userid']) ? intval($_POST['userid']) : 0;

		if ($userid <= 0) {
			echo json_encode([]);
			exit;
		}

		$comps = execsqlSRS("
			SELECT [comp_id]
      				,[comp_desc]
			FROM [tbl_ProfComp]
			WHERE [UserID] = :userid AND [IsActive] = 0
			ORDER BY [comp_id]
		", "Select", [":userid" => $userid]);

		echo "<ul class='list-group mb-3' style='max-width: 500px;'>";

		foreach ($comps as $comp) {

			echo "
			<li class='list-group-item d-flex justify-content-between align-items-center fade-in'>
				<span>
					<i class='fas fa-circle text-success mr-2' style='font-size: 0.6rem;'></i>
					{$comp['comp_desc']}
				</span>
				<button class='btn btn-sm btn-danger'
						data-datavalue='{$comp['comp_id']}'
						data-backendurl='backend/bk_profilemanagement.php'
						data-backendrequest='deletecomp'
						id='delete_comp'>
					<span class='text-nowrap'><i class='fas fa-trash'></i> Delete</span>
				</button>
			</li>
			";
		}

		echo "</ul>";

		break;

	case "savecomp":

		$userid = isset($_POST['userid']) ? intval($_POST['userid']) : 0;
		$comp_desc = isset($_POST['comp_desc']) ? trim($_POST['comp_desc']) : '';

		if ($userid <= 0 || $comp_desc === '') {
			echo json_encode([
				"status" => "error",
				"message" => "Invalid input."
			]);
			exit;
		}

		if (!preg_match("/^[a-zA-Z\s\.\,\']+$/", $comp_desc)) {
			echo json_encode([
				"status" => "error",
				"message" => "Competency can only contain letters, spaces, dots, commas, and apostrophes."
			]);
			exit;
		}

		$insert = execsqlSRS("
			INSERT INTO [tbl_ProfComp] ([comp_desc], [UserID], [IsActive])
			VALUES (:comp_desc, :userid, 0)
		", "Insert", [":comp_desc" => $comp_desc, ":userid" => $userid]);

		echo json_encode([
			"status" => "success",
			"message" => "Competency added successfully."
		]);

		break;

	case "deletecomp":

		$comp_id = isset($_POST['datavalue']) ? intval($_POST['datavalue']) : 0;

		if ($comp_id <= 0) {
			echo json_encode([
				"status" => "error",
				"message" => "Invalid skill record."
			]);
			exit;
		}

		$delete = execsqlSRS("
			DELETE FROM [tbl_ProfComp]
			WHERE [comp_id] = :comp_id
		", "Delete", [":comp_id" => $comp_id]);

		echo json_encode([
			"status" => "success",
			"message" => "Competency deleted successfully."
		]);

		break;
}
