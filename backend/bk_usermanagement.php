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

	case "viewusers":

		$queryViewUsers = execsqlSRS("
		SELECT ua.UserID, ua.Username, ua.Password, ua.EmailAddress, ua.RID, ua.IsActive, r.Role
		FROM Sys_UserAccount ua

		LEFT JOIN Sys_Role r 
		ON ua.RID = r.RID

		ORDER BY ua.UserID",
		"Search",
		array()
		);

			foreach ($queryViewUsers as $user) {
				echo "<tr>";
				echo "<td>" . htmlspecialchars($user["UserID"]) . "</td>";
				echo "<td>" . htmlspecialchars($user["Username"]) . "</td>";

				$password = htmlspecialchars($user["Password"], ENT_QUOTES);

				echo "<td>
						<div class='d-flex align-items-center'>
							<input type='password' 
								   class='form-control-plaintext mb-0' 
								   value='********' 
								   data-password=\"" . $password . "\" 
								   id='password_" . htmlspecialchars($user["UserID"]) . "' 
								   readonly 
								   style='width: 120px; color: responsive' />

							<button type='button' 
									class='btn btn-sm btn-outline-secondary ml-2' 
									onclick='passwordToggler.toggle(" . $user["UserID"] . ")' 
									id='btn_" . $user["UserID"] . "' 
									data-toggle='tooltip' 
									data-placement='top' 
									title='Show Password'>
								<i class='fas fa-eye' id='eye_" . $user["UserID"] . "'></i>
							</button>
						</div>
					  </td>";

				echo "<td>" . htmlspecialchars($user["EmailAddress"]) . "</td>";
				echo "<td>" . htmlspecialchars($user["Role"]) . "</td>";
				
				if (htmlspecialchars($user["IsActive"]) == 0) {
					echo "<td><i class='fa-solid fa-toggle-on fa-2x text-success'
								 data-statuscontent='". htmlspecialchars($user["IsActive"]) ."'
								 data-statusdata='" . htmlspecialchars($user["UserID"]) . "'
								 id='statustrigger'
								 data-backendurl='backend/bk_statustrigger.php'
								 data-backendmethod='POST'
								 data-backendrequest='statustoggle'
								 data-databasedir='Sys_UserAccount'
								 data-databaseparam='UserID'
								 data-tableid='#tblviewusers'
								 data-tablebackendurl='backend/bk_usermanagement.php'
								 data-tablerequest='viewusers'
								 ></i></td>";
				}

				else if (htmlspecialchars($user["IsActive"]) == 1) {
					echo "<td><i class='fa-solid fa-toggle-off fa-2x text-danger'
								 data-statuscontent='". htmlspecialchars($user["IsActive"]) ."'
								 data-statusdata='" . htmlspecialchars($user["UserID"]) . "'
								 id='statustrigger'
								 data-backendurl='backend/bk_statustrigger.php'
								 data-backendmethod='POST'
								 data-backendrequest='statustoggle'
								 data-databasedir='Sys_UserAccount'
								 data-databaseparam='UserID'
								 data-tableid='#tblviewusers'
								 data-tablebackendurl='backend/bk_usermanagement.php'
								 data-tablerequest='viewusers'
								 ></i></td>";
				}

				echo "<td>
						<button type='button' 
								class='btn btn-warning m-1' 
								id='edit_data' 
								data-tooltip='View/Edit'
								data-openmodal='#addeditmodal'
								data-openmodallabel='Edit User'
								data-openmodalbody='#addeditcontent'
								data-backendurl='backend/bk_usermanagement.php'
								data-backendrequest='edituser'
								data-datavalue='" . htmlspecialchars($user["UserID"]) . "'>
							<i class='fa-solid fa-pencil'></i>
						</button>
						<button type='button' 
								class='btn btn-danger m-1' 
								id='delete_data' 
								data-tooltip='Delete'
								data-openmodal='#addeditmodal'
								data-openmodallabel='Delete User'
								data-openmodalbody='#addeditcontent'
								data-backendurl='backend/bk_usermanagement.php'
								data-backendrequest='deleteuser'
								data-datavalue='" . htmlspecialchars($user["UserID"]) . "'>
							<i class='fa-solid fa-trash'></i>
					  </td>";

				echo "</tr>";
			}
	break;

	case "adduser":

		echo '
		<div class="p-3">
		  <div class="form-group">
			<label for="">Username</label>
			<input type="text" class="form-control field-input" id="field1" placeholder="Username...">
		  </div>

		  <div class="form-group">
			<label for="">Password</label>
			<input type="text" class="form-control field-input" id="field2" placeholder="Password...">
		  </div>
		  
		  <div class="dropdown">
			<label for="" class="pr-2">Select Role:</label>
			<select class="form-select form-control field-input" aria-label="Default select example" id="field3">
			<option value="">Select Role...</option>
			<hr>';
			
			$roles = execsqlSRS("
			  SELECT RID, Role, IsActive
			  FROM Sys_Role
			", "Select", array());

			foreach ($roles as $role) {
			  $id = htmlspecialchars($role['RID']);
			  $desc = htmlspecialchars($role['Role']);
			  $status = htmlspecialchars($role['IsActive']);
			  if ($status == 0) {
				echo "<option value='$id'>$desc</option>";
			  }
			  else if ($status == 1) {
				echo "<option class='bg-danger' value='$id'>$desc</option>";
			  }
			}

		echo '
			</select>
		  </div>
		  
		  <div class="form-group mt-3">
			<label for="">Status</label>
			<input type="text" class="form-control field-input" id="field4" placeholder="Status...">
		  </div>

		  <div class="form-group">
			<label for="">Email Address</label>
			<input type="text" class="form-control field-input" id="field5" placeholder="Email...">
		  </div>

		  <div class="form-group pt-2 d-flex justify-content-center">
			<button type="submit" 
					id="save_data"
					data-openmodal="#addeditmodal"
					data-operator="add"
					data-backendrequest="saveuser"
					data-backendurl="backend/bk_usermanagement.php"
					data-tableid="#tblviewusers"
					data-tablerequest="viewusers"
					class="btn btn-success">Add User</button>
		  </div>
		</div>';

	break;
		
	case "edituser":
	
		$selectrole = execsqlSRS("
			SELECT r.Role
			FROM Sys_UserAccount u

			LEFT JOIN Sys_Role r
			ON r.RID = u.RID

			WHERE u.UserID = :UserID", 
			"Select", [
						":UserID" => $datavalue, 
					  ]); 
		$rolename = isset($selectrole[0])?$selectrole[0]["Role"]:"";

		$queryedituser = execsqlSRS("
			SELECT Username, Password, RID, IsActive, EmailAddress, UserID
			FROM [Sys_UserAccount]
			WHERE UserID = :UserID", 
			"Select", [
						":UserID" => $datavalue
					  ]); 
					  
			foreach ($queryedituser as $edit) {

			echo "
				<div class='p-3'>
				<div class='form-group'>
					<label for=''>Username</label>
                    <input type='text' class='form-control field-input' id='field1' value='" . htmlspecialchars($edit["Username"]) . "'>
				  </div>";
			echo "<div class='form-group'>
					<label for=''>Password</label>
                    <input type='text' class='form-control field-input' id='field2' value='" . htmlspecialchars($edit["Password"]) . "'>
				  </div>";

				echo 
					"<div class='form-group'>
					<div class='dropdown'>
						<label for='' class='pr-2'>Role:</label>
							<select class='form-select form-control field-input' aria-label='Default select example' id='field3'>
								<option value='" . htmlspecialchars($edit["RID"]) . "'>" . htmlspecialchars($rolename) . "</option>
								<hr>";

										$q1 = execsqlSRS("
										SELECT RID, Role, IsActive
										FROM Sys_Role
										ORDER BY RID
										", "Select", array());
										foreach ($q1 as $role) {
										  $id = htmlspecialchars($role['RID']);
										  $desc = htmlspecialchars($role['Role']);
										  $status = htmlspecialchars($role['IsActive']);
										  if ($status == 0) {
											echo "<option value='$id'>$desc</option>";
										  }
										  else if ($status == 1) {
											echo "<option class='bg-danger' value='$id'>$desc</option>";
										  }
										}
										
				echo    "</select>
					</div>
					</div>";

			echo "<div class='form-group'>
					<label for=''>Status</label>
                    <input type='text' class='form-control field-input' id='field4' value='" . htmlspecialchars($edit["IsActive"]) . "'>
				  </div>";
				  
			echo "<div class='form-group'>
					<label for=''>Email</label>
                    <input type='text' class='form-control field-input' id='field5' value='" . htmlspecialchars($edit["EmailAddress"]) . "'>
				  </div>";

				echo    "<div class='form-group d-flex justify-content-center pt-2'>
				<button 
						type='submit' 
						class='btn btn-success' 
						id='save_data'
						data-openmodal='#addeditmodal'
						data-operator='edit'
						data-backendrequest='saveuser'
						data-backendurl='backend/bk_usermanagement.php'
						data-tableid='#tblviewusers'
						data-tablerequest='viewusers'
						data-datavalue='" . htmlspecialchars($edit["UserID"]) . "'>
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

	case "saveuser":
	
	if ($operator == "delete") {
		
		$getolddata = execsqlSRS("
				SELECT *
				FROM Sys_UserAccount
				WHERE UserID = :datavalue", 
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
				DELETE FROM Sys_UserAccount
				WHERE UserID = :datavalue", 
				"Delete", [
							":datavalue" => $datavalue
						  ]); 

		echo json_encode (['status' => 'success', 'message' => 'System: User has been Moved to the Logs.']);

		break;
	}

	if ( empty($fields["field1"]) || empty($fields["field2"]) || empty($fields["field3"]) || empty($fields["field5"]) ){
		echo json_encode (['status' => 'error', 'message' => 'System: Some fields are empty.']);
		return;
	}
	
	if (!is_numeric($fields["field4"])){
		echo json_encode (['status' => 'error', 'message' => 'System: Status should either be 0 or 1.']);
		return;
	}

		if ($operator == "edit") {
			
			$querysave = execsqlSRS("
				UPDATE Sys_UserAccount
				SET Username = :username, Password = :password, RID = :role, IsActive = :status, EmailAddress = :emailaddress
				WHERE UserID = :datavalue", 
				"Update", [
							":username" => $fields["field1"], 
							":password" => $fields["field2"], 
							":role" => intval($fields["field3"]), 
							":status" => intval($fields["field4"]), 
							":emailaddress" => $fields["field5"], 
							":datavalue" => intval($datavalue)
						  ]);
						  
			echo json_encode (['status' => 'success', 'message' => 'System: Changes have been Saved.']);

		}

		else if ($operator == "add") {

			$querysave = execsqlSRS("
					INSERT INTO [Sys_UserAccount] (Username, Password, RID, IsActive, AccountRegDate, EmailAddress)
					VALUES (:username, :password, :role, :status, :dt, :emailaddress)", 
					"Insert", [
								":username" => $fields["field1"], 
								":password" => $fields["field2"], 
								":role" => intval($fields["field3"]), 
								":status" => intval($fields["field4"]), 
								":dt" => $currentdt, 
								":emailaddress" => $fields["field5"]
							  ]);

			echo json_encode (['status' => 'success', 'message' => 'System: User Added!']);
		}
		
		else {
			echo json_encode (['status' => 'error', 'message' => 'System: Error Fetching. No Operator']);
			return;
		}

	break;

}

?>