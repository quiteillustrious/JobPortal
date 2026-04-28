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

	case "viewappointmentstatus":

		$viewappointmentstatus = execsqlSRS("
		SELECT 	 [appoint_id]
				,[appoint_desc]
				,[IsActive]
		FROM [tbl_ProfExpAppoint]

		ORDER BY [appoint_desc]",
		"Search",
		array()
		);

			foreach ($viewappointmentstatus as $appointmentstatus) {
				echo "<tr>";
				echo "<td>" . htmlspecialchars($appointmentstatus["appoint_id"]) . "</td>";
				echo "<td>" . htmlspecialchars($appointmentstatus["appoint_desc"]) . "</td>";
				
				if (htmlspecialchars($appointmentstatus["IsActive"])){
					$togglevar = 'off';
					$textcolor = 'danger';
				}
				else{
					$togglevar = 'on' ;
					$textcolor = 'success';
				}
				
				echo "<td><i class='fa-solid fa-toggle-{$togglevar} fa-2x text-{$textcolor}'
							 data-statuscontent='". htmlspecialchars($appointmentstatus["IsActive"]) ."'
							 data-statusdata='" . htmlspecialchars($appointmentstatus["appoint_id"]) . "'
							 id='statustrigger'
							 data-backendurl='backend/bk_statustrigger.php'
							 data-backendmethod='POST'
							 data-backendrequest='statustoggle'
							 data-databasedir='tbl_ProfExpAppoint'
							 data-databaseparam='appoint_id'
							 data-tableid='#tblviewappointmentstatus'
							 data-tablebackendurl='backend/bk_hrappointmentstatus.php'
							 data-tablerequest='viewappointmentstatus'
							 ></i></td>";

				echo "<td>
						<button type='button' 
								class='btn btn-warning m-1' 
								id='edit_data' 
								data-tooltip='View/Edit'
								data-openmodal='#addeditmodal'
								data-openmodallabel='Edit Appointment Status'
								data-openmodalbody='#addeditcontent'
								data-backendurl='backend/bk_hrappointmentstatus.php'
								data-backendrequest='editappointmentstatus'
								data-datavalue='" . htmlspecialchars($appointmentstatus["appoint_id"]) . "'>
							<i class='fa-solid fa-pencil'></i>
						</button>
					  </td>";

				echo "</tr>";
			}
	break;

	case "addappointmentstatus":

		echo '
		<div class="p-3">
		  <div class="form-group">
			<label for="">Description</label>
			<input type="text" class="form-control field-input" id="field1" placeholder="e.g. Primary...">
		  </div>

		  <div class="form-group">
			<label for="">Status (0/1)</label>
			<input type="text" class="form-control field-input" id="field2" placeholder="0...">
		  </div>

		  <div class="form-group pt-2 d-flex justify-content-center">
			<button type="submit" 
					id="save_data"
					data-openmodal="#addeditmodal"
					data-operator="add"
					data-backendrequest="saveappointmentstatus"
					data-backendurl="backend/bk_hrappointmentstatus.php"
					data-tableid="#tblviewappointmentstatus"
					data-tablerequest="viewappointmentstatus"
					class="btn btn-success">Add Appointment Status</button>
		  </div>
		</div>';

	break;
		
	case "editappointmentstatus":

		$queryedit = execsqlSRS("
			SELECT [appoint_id]
				  ,[appoint_desc]
				  ,[IsActive]
			FROM [tbl_ProfExpAppoint]
			WHERE appoint_id = :appoint_id", 
			"Select", [
						":appoint_id" => $datavalue
					  ]); 
					  
			foreach ($queryedit as $edit) {

			echo "
				<div class='p-3'>
				<div class='form-group'>
					<label for=''>Description</label>
                    <input type='text' class='form-control field-input' id='field1' value='" . htmlspecialchars($edit["appoint_desc"]) . "'>
				  </div>";
			echo "<div class='form-group'>
					<label for=''>Status</label>
                    <input type='text' class='form-control field-input' id='field2' value='" . htmlspecialchars($edit["IsActive"]) . "'>
				  </div>";

				echo    "<div class='form-group d-flex justify-content-center pt-2'>
				<button 
						type='submit' 
						class='btn btn-success' 
						id='save_data'
						data-openmodal='#addeditmodal'
						data-operator='edit'
						data-backendrequest='saveappointmentstatus'
						data-backendurl='backend/bk_hrappointmentstatus.php'
						data-tableid='#tblviewappointmentstatus'
						data-tablerequest='viewappointmentstatus'
						data-datavalue='" . htmlspecialchars($edit["appoint_id"]) . "'>
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

	case "saveappointmentstatus":

		if ($operator == "edit") {
			
			$querysave = execsqlSRS("
				UPDATE tbl_ProfExpAppoint
				SET appoint_desc = :desc, IsActive = :status
				WHERE appoint_id = :datavalue", 
				"Update", [
							":desc" => $fields["field1"], 
							":status" => intval($fields["field2"]), 
							":datavalue" => intval($datavalue)
						  ]);
						  
			echo json_encode (['status' => 'success', 'message' => 'System: Changes have been Saved.']);

		}

		else if ($operator == "add") {

			$querysave = execsqlSRS("
					INSERT INTO [tbl_ProfExpAppoint] ([appoint_desc], [IsActive])
					VALUES (:desc, :status)", 
					"Insert", [
								":desc" => $fields["field1"], 
								":status" => intval($fields["field2"])
							  ]);

			echo json_encode (['status' => 'success', 'message' => 'System: Appointment Status Added!']);
		}
		
		else {
			echo json_encode (['status' => 'error', 'message' => 'System: Error Fetching. No Operator']);
			return;
		}

	break;

}

?>