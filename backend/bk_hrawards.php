<?php
date_default_timezone_set('Asia/Manila');
include "../db/dbconnection.php";

$request = isset($_POST["request"]) ? $_POST["request"] : "";
$fields = isset($_POST["fields"]) ? $_POST["fields"] : "";
$operator = isset($_POST["operator"]) ? $_POST["operator"] : "";
$datavalue = isset($_POST["datavalue"]) ? $_POST["datavalue"] : "";
$logslocation = isset($_POST["logslocation"]) ? $_POST["logslocation"] : "";
$userid = isset($_POST["userid"]) ? $_POST["userid"] : "";

$currentdt = date("Y-m-d H:i:s");

switch ($request) {

	case "viewawards":
	
		$arr = array_map('trim', explode(',', $datavalue));
		$awardsId = isset($arr[0]) ? $arr[0] : null;
		$eduId    = isset($arr[1]) ? $arr[1] : $arr[0];

		$view = execsqlSRS("
		SELECT [awards_id]
			  ,[awards_desc]
			  ,[edu_id]
			  ,[IsActive]
		FROM [tbl_ProfAwards]
		WHERE [edu_id] = :datavalue",
		"Search",
		[
			":datavalue" => $eduId
		]
		);

			foreach ($view as $v) {
				echo "<tr>";
				echo "<td>" . htmlspecialchars($v["awards_id"]) . "</td>";
				echo "<td>" . htmlspecialchars($v["awards_desc"]) . "</td>";
				
				if (htmlspecialchars($v["IsActive"])){
					$togglevar = 'off';
					$textcolor = 'danger';
				}
				else{
					$togglevar = 'on' ;
					$textcolor = 'success';
				}
				
				echo "<td><i class='fa-solid fa-toggle-{$togglevar} fa-2x text-{$textcolor}'
							 data-statuscontent='". htmlspecialchars($v["IsActive"]) ."'
							 data-statusdata='" . htmlspecialchars($v["awards_id"]) . "'
							 data-datavalue='" . htmlspecialchars($v["edu_id"]) . "'
							 id='statustrigger'
							 data-backendurl='backend/bk_statustrigger.php'
							 data-backendmethod='POST'
							 data-backendrequest='statustoggle'
							 data-databasedir='tbl_ProfAwards'
							 data-databaseparam='awards_id'
							 data-tableid='#tblviewhrawards'
							 data-tablebackendurl='backend/bk_hrawards.php'
							 data-tablerequest='viewawards'
							 ></i></td>";

				echo "<td>
						<button type='button' 
								class='btn btn-warning m-1' 
								id='edit_data' 
								data-tooltip='View/Edit'
								data-openmodal='#addeditmodal'
								data-openmodallabel='Edit Award'
								data-openmodalbody='#addeditcontent'
								data-backendurl='backend/bk_hrawards.php'
								data-backendrequest='editaward'
								data-datavalue='" . htmlspecialchars($v["awards_id"]) . "'>
							<i class='fa-solid fa-pencil'></i>
						</button>
					  </td>";

				echo "</tr>";
			}
	break;

	case "addaward":

		echo '
		<div class="p-3">
		  <div class="form-group">
			<label for="">Description</label>
			<input type="text" class="form-control field-input" id="field1" placeholder="e.g. Primary...">
		  </div>';

				echo 
					"<div class='form-group'>
					<div class='dropdown'>
						<label for='' class='pr-2'>Education Level:</label>
							<select class='form-select form-control field-input' aria-label='Default select example' id='field2'>";

										$q1 = execsqlSRS("
										SELECT [edu_id]
											  ,[edu_desc]
											  ,[IsActive]
										FROM [tbl_ProfEducationLevels]
										ORDER BY [edu_id]
										", "Select", array());
										foreach ($q1 as $award) {
										  if ($award["IsActive"] == 0) {
											echo "<option value='".htmlspecialchars($award["edu_id"])."'>".htmlspecialchars($award["edu_desc"])."</option>";
										  }
										  else if ($award["IsActive"] == 1) {
											echo "<option class='bg-danger' value='".htmlspecialchars($award["edu_id"])."'>".htmlspecialchars($award["edu_desc"])."</option>";
										  }
										}
										
				echo    "</select>
					</div>
					</div>";

echo '
		  <div class="form-group">
			<label for="">Status (0/1)</label>
			<input type="text" class="form-control field-input" id="field3" placeholder="0...">
		  </div>

		  <div class="form-group pt-2 d-flex justify-content-center">
			<button type="button" 
					id="save_data"
					data-openmodal="#addeditmodal"
					data-operator="add"
					data-backendrequest="saveaward"
					data-backendurl="backend/bk_hrawards.php"
					data-tableid="#tblviewawards"
					data-tablerequest="viewawards"
					class="btn btn-success">Add Award</button>
		  </div>
		</div>';

	break;
		
	case "editaward":

		$queryedit = execsqlSRS("
			SELECT a.[awards_id]
				  ,a.[awards_desc]
				  ,a.[edu_id]
				  ,e.[edu_desc]
				  ,a.[IsActive]
			FROM [tbl_ProfAwards] a
			
			LEFT JOIN [tbl_ProfEducationLevels] e
			ON e.[edu_id] = a.[edu_id]
			
			WHERE awards_id = :awards_id", 
			"Select", [
						":awards_id" => $datavalue
					  ]); 
					  
			foreach ($queryedit as $edit) {

			echo "
				<div class='p-3'>
				<div class='form-group'>
					<label for=''>Description</label>
                    <input type='text' class='form-control field-input' id='field1' value='" . htmlspecialchars($edit["awards_desc"]) . "'>
				  </div>";
				  
				echo 
					"<div class='form-group'>
					<div class='dropdown'>
						<label for='' class='pr-2'>Education Level:</label>
							<select class='form-select form-control field-input' aria-label='Default select example' id='field2'>
										<option value='".htmlspecialchars($edit["edu_id"])."'>".htmlspecialchars($edit["edu_desc"])."</option>
										<hr>";

										$q1 = execsqlSRS("
										SELECT [edu_id]
											  ,[edu_desc]
											  ,[IsActive]
										FROM [tbl_ProfEducationLevels]
										ORDER BY [edu_id]
										", "Select", array());
										foreach ($q1 as $award) {
										  if ($award["IsActive"] == 0) {
											echo "<option value='".htmlspecialchars($award["edu_id"])."'>".htmlspecialchars($award["edu_desc"])."</option>";
										  }
										  else if ($award["IsActive"] == 1) {
											echo "<option class='bg-danger' value='".htmlspecialchars($award["edu_id"])."'>".htmlspecialchars($award["edu_desc"])."</option>";
										  }
										}
										
				echo    "</select>
					</div>
					</div>";
				  
			echo "<div class='form-group'>
					<label for=''>Status</label>
                    <input type='text' class='form-control field-input' id='field3' value='" . htmlspecialchars($edit["IsActive"]) ."'>
				  </div>";

				echo    "<div class='form-group d-flex justify-content-center pt-2'>
				<button 
						type='submit' 
						class='btn btn-success' 
						id='save_data'
						data-openmodal='#addeditmodal'
						data-operator='edit'
						data-backendrequest='saveaward'
						data-backendurl='backend/bk_hrawards.php'
						data-tableid='#tblviewhrawards'
						data-tablerequest='viewawards'
						data-datavalue='" . htmlspecialchars($edit["awards_id"]) .  "," .htmlspecialchars($edit["edu_id"])."'>
						Save Changes
				</button>
              </div>
			  </div>";
			}

	break;

	case "deleteuser":

	echo "
			<div class='d-flex justify-content-center pt-3'>
					<div class='card border border-danger ml-2 shadow' style='max-width:40rem;'>
						<div class='card-title ml-3 mt-3 mb-3 mr-3'>
							<i class='fa-solid fa-triangle-exclamation text-danger'></i>
								<span class='font-weight-bold text-danger'>Warning:</span>
								<span>Deleting will remove this data from its main Table. It will be transferred to the Logs.</span>
						</div>
					</div>
			</div>
			<div class='d-flex justify-content-center'>
				<div>
				<button type='button' 
						class='btn btn-danger' 
						id='save_data' 
						data-openmodal='#addeditmodal'
						data-operator='delete'
						data-backendrequest='saveuser'
						data-backendurl='backend/bk_usermanagement.php'
						data-logslocation='User Management'
						data-tableid='#tblviewusers'
						data-tablerequest='viewusers'
						data-datavalue='" . htmlspecialchars($datavalue) . "'>
					Confirm Deletion
				</button>
				</div>
			</div>
	";

	break;

	case "saveaward":

		if ($operator == "edit") {
			
			$arr = array_map('trim', explode(',', $datavalue));
			$awardsId = isset($arr[0]) ? $arr[0] : null;
			$eduId    = isset($arr[1]) ? $arr[1] : null;
			
			$querysave = execsqlSRS("
				UPDATE [tbl_ProfAwards]
				SET [awards_desc] = :desc, [edu_id] = :eduid, [IsActive] = :status
				WHERE [awards_id] = :datavalue", 
				"Update", [
							":desc" => $fields["field1"], 
							":eduid" => intval($fields["field2"]), 
							":status" => intval($fields["field3"]), 
							":datavalue" => intval($awardsId)
						  ]);
						  
			echo json_encode (['status' => 'success', 'message' => 'System: Changes have been Saved.']);

		}

		else if ($operator == "add") {

			$querysave = execsqlSRS("
					INSERT INTO [tbl_ProfAwards] ([awards_desc], [edu_id], [IsActive])
					VALUES (:desc, :edu, :status)", 
					"Insert", [
								":desc" => $fields["field1"], 
								":edu" => intval($fields["field2"]), 
								":status" => intval($fields["field3"])
							  ]);

			echo json_encode (['status' => 'success', 'message' => 'System: Award Title Added!']);
		}
		
		else {
			echo json_encode (['status' => 'error', 'message' => 'System: Error Fetching. No Operator']);
			return;
		}

	break;

}

?>