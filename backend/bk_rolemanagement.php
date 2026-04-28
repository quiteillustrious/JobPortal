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

	case "viewroles":
		$queryViewRoles = execsqlSRS("
			SELECT RID, Role, Rolecode, IsActive 
			FROM Sys_Role 
			ORDER BY RID", 
			"Search", 
			array()
		);

		foreach ($queryViewRoles as $roles) {
			echo "<tr>";
			echo "<td>" . htmlspecialchars($roles["RID"]) . "</td>";
			echo "<td>" . htmlspecialchars($roles["Role"]) . "</td>";
			echo "<td>" . htmlspecialchars($roles["Rolecode"]) . "</td>";
			
				if (htmlspecialchars($roles["IsActive"]) == 0) {
					echo "<td><i class='fa-solid fa-toggle-on fa-2x text-success'
								 data-statuscontent='". htmlspecialchars($roles["IsActive"]) ."'
								 data-statusdata='" . htmlspecialchars($roles["RID"]) . "'
								 id='statustrigger'
								 data-backendurl='backend/bk_statustrigger.php'
								 data-backendmethod='POST'
								 data-backendrequest='statustoggle'
								 data-databasedir='Sys_Role'
								 data-databaseparam='RID'
								 data-tableid='#tblviewroles'
								 data-tablebackendurl='backend/bk_rolemanagement.php'
								 data-tablerequest='viewroles'
								 ></i></td>";
				}

				else if (htmlspecialchars($roles["IsActive"]) == 1) {
					echo "<td><i class='fa-solid fa-toggle-off fa-2x text-danger'
								 data-statuscontent='". htmlspecialchars($roles["IsActive"]) ."'
								 data-statusdata='" . htmlspecialchars($roles["RID"]) . "'
								 id='statustrigger'
								 data-backendurl='backend/bk_statustrigger.php'
								 data-backendmethod='POST'
								 data-backendrequest='statustoggle'
								 data-databasedir='Sys_Role'
								 data-databaseparam='RID'
								 data-tableid='#tblviewroles'
								 data-tablebackendurl='backend/bk_rolemanagement.php'
								 data-tablerequest='viewroles'
								 ></i></td>";
				}
			
			echo "<td>
					<button type='button' 
							class='btn btn-warning m-1' 
							id='edit_data' 
							data-tooltip='View/Edit'
							data-openmodal='#addeditmodal'
							data-openmodallabel='Edit Role'
							data-openmodalbody='#addeditcontent'
							data-backendurl='backend/bk_rolemanagement.php'
							data-backendrequest='editrole'
							data-datavalue='" . htmlspecialchars($roles["RID"]) . "'>
						<i class='fa-solid fa-pencil'></i>
					</button>
					<button type='button' 
							class='btn btn-danger m-1' 
							id='delete_data' 
							data-tooltip='Delete'
							data-openmodal='#addeditmodal'
							data-openmodallabel='Delete Role'
							data-openmodalbody='#addeditcontent'
							data-backendurl='backend/bk_rolemanagement.php'
							data-backendrequest='deleterole'
							data-datavalue='" . htmlspecialchars($roles["RID"]) . "'>
						<i class='fa-solid fa-trash'></i>
					</button>
				  </td>";
			echo "</tr>";
		}
	break;
	
	case "addrole":

		echo '
		<div class="p-3">
		  <div class="form-group">
			<label for="">Role</label>
			<input type="text" class="form-control field-input" id="field1" placeholder="Details...">
		  </div>

		  <div class="form-group">
			<label for="">Rolecode</label>
			<input type="text" class="form-control field-input" id="field2" placeholder="Rolecode...">
		  </div>
		  
		  <div class="form-group">
			<label for="">Status</label>
			<input type="text" class="form-control field-input" id="field3" placeholder="Status...">
		  </div>

		  <div class="form-group pt-2 d-flex justify-content-center">
			<button type="submit" 
					id="save_data"
					data-openmodal="#addeditmodal"
					data-operator="add"
					data-backendrequest="saverole"
					data-backendurl="backend/bk_rolemanagement.php"
					data-tableid="#tblviewroles"
					data-tablerequest="viewroles"
					class="btn btn-success">Add Role</button>
		  </div>
		</div>';

	break;

	case "editrole":

		$queryeditrole = execsqlSRS("
			SELECT RID, Role, Rolecode, IsActive
			FROM [Sys_Role]
			WHERE RID = :datavalue", 
			"Select", [
						":datavalue" => $datavalue
					  ]); 
					  
			foreach ($queryeditrole as $edit) {

			echo "
				<div class='p-3'>
				  <div class='form-group'>
					<label for=''>Role</label>
                    <input type='text' class='form-control field-input' id='field1' value='" . htmlspecialchars($edit["Role"]) . "'>
				  </div>";
			echo "<div class='form-group'>
					<label for=''>Rolecode</label>
                    <input type='text' class='form-control field-input' id='field2' value='" . htmlspecialchars($edit["Rolecode"]) . "'>
				  </div>";

			echo "<div class='form-group'>
					<label for=''>Status</label>
                    <input type='text' class='form-control field-input' id='field3' value='" . htmlspecialchars($edit["IsActive"]) . "'>
				  </div>";

				echo    "<div class='form-group d-flex justify-content-center pt-2'>
				<button 
						type='submit' 
						class='btn btn-success' 
						id='save_data'
						data-openmodal='#addeditmodal'
						data-operator='edit'
						data-backendrequest='saverole'
						data-backendurl='backend/bk_rolemanagement.php'
						data-tableid='#tblviewroles'
						data-tablerequest='viewroles'
						data-datavalue='" . htmlspecialchars($edit["RID"]) . "'>
						Save Changes
				</button>
              </div>
			  </div>";
			}

	break;
	
	case "deleterole":
	
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
						data-backendrequest='saverole'
						data-backendurl='backend/bk_rolemanagement.php'
						data-logslocation='Role Management'
						data-tableid='#tblviewroles'
						data-tablerequest='viewroles'
						data-datavalue='" . htmlspecialchars($datavalue) . "'>
					Confirm Deletion
				</button>
				</div>
			</div>
	";

	break;
	
	case "saverole":
	
	if ($operator == "delete") {
		
		$getolddata = execsqlSRS("
				SELECT *
				FROM Sys_Role
				WHERE RID = :datavalue", 
				"Select", [
							":datavalue" => $datavalue
						  ]); 
						  
		$string = implode (' | ', $getolddata[0]);

		$querysave = execsqlSRS("
				INSERT INTO [tbl_Logs] (logs_details, logs_dt, logs_location, logs_operation, logs_doneby, IsActive)
				VALUES (:string, :currentdt, :logslocation, :operator, :userid, '0')", 
				"Insert", [
							":string" => $string, 
							":currentdt" => $currentdt, 
							":logslocation" => $logslocation, 
							":operator" => $operator, 
							":userid" => $userid
						  ]); 
	  
		$querydelete = execsqlSRS("
				DELETE FROM Sys_Role
				WHERE RID = :datavalue", 
				"Delete", [
							":datavalue" => $datavalue
						  ]); 

		echo json_encode (['status' => 'success', 'message' => 'System: Role has been Moved to the Logs.']);
		
		break;
	}

	if ( empty($fields["field1"]) || empty($fields["field2"]) ){
		echo json_encode (['status' => 'error', 'message' => 'System: Some fields are empty.']);
		return;
	}
	
	if (!is_numeric($fields["field3"])){
		echo json_encode (['status' => 'error', 'message' => 'System: Status should either be 0 or 1.']);
		return;
	}

		if ($operator == "edit") {
			
			$querysave = execsqlSRS("
				UPDATE Sys_Role
				SET Role = :role, Rolecode = :rolecode, IsActive = :status
				WHERE RID = :datavalue", 
				"Update", [
							":role" => $fields["field1"], 
							":rolecode" => $fields["field2"], 
							":status" => intval($fields["field3"]), 
							":datavalue" => intval($datavalue)
						  ]);

			echo json_encode (['status' => 'success', 'message' => 'System: Changes have been saved.']);
			
		}

		else if ($operator == "add") {

			$querysave = execsqlSRS("
					INSERT INTO Sys_Role (Role, Rolecode, IsActive)
					VALUES (:role, :rolecode, :status)", 
					"Insert", [
								":role" => $fields["field1"], 
								":rolecode" => $fields["field2"], 
								":status" => intval($fields["field3"])
							  ]); 

				$getrole = execsqlSRS("
						SELECT TOP 1 RID
						FROM Sys_Role
						WHERE (
								(Role = :role AND Rolecode = :rolecode AND IsActive = :status)
						      )
						ORDER BY RID DESC", 
						"Select", [
									":role" => $fields["field1"], 
									":rolecode" => $fields["field2"], 
									":status" => intval($fields["field3"])
								  ]); 
								  
				$rid = isset($getrole[0]["RID"]) ? $getrole[0]["RID"] : "";

 			if ($rid){
				
				$getmenus = execsqlSRS("
						SELECT MenID
						FROM Sys_Menu
						", 
						"Select", []); 
						
				foreach ($getmenus as $menus) {
					
					$queryloop = execsqlSRS("
							INSERT INTO Sys_RoleMenu (RID, MenID, IsActive)
							VALUES (:rid, :menid, '1')", 
							"Insert", [
										":rid" => $rid, 
										":menid" => $menus["MenID"]
									  ]); 
									  
					
				}
				
				echo json_encode (['status' => 'success', 'message' => 'System: Role Added and Looped.']);
				
			}
			
			else {
				echo json_encode (['status' => 'error', 'message' => 'System: Error Fetching. Query Failed.']);
				return;
			} 
							 
			
		}
		
		else {
			echo json_encode (['status' => 'error', 'message' => 'System: Error Fetching. No Operator.']);
			return;
		}

	break;

}
?>
