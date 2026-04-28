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

	case "viewcolors":

		$queryviewcolors = execsqlSRS("
			SELECT *
			FROM tbl_Colors

			ORDER BY color_id", 
			"Select",  
			array()
		);

			foreach ($queryviewcolors as $colors) {
				
				echo "<tr>";
				echo "<td>" . htmlspecialchars($colors["color_id"]) . "</td>";
				echo "<td>" . htmlspecialchars($colors["color_desc"]) . "</td>";
				echo "<td>" . htmlspecialchars($colors["color_hex"]) . "</td>";
				echo "<td class='border bg-" . htmlspecialchars($colors["color_desc"]) . "'></td>";

				if (htmlspecialchars($colors["IsActive"]) == 0) {
					echo "<td><i class='fa-solid fa-toggle-on fa-2x text-success'
								 data-statuscontent='". htmlspecialchars($colors["IsActive"]) ."'
								 data-statusdata='" . htmlspecialchars($colors["color_id"]) . "'
								 id='statustrigger'
								 data-backendurl='backend/bk_statustrigger.php'
								 data-backendmethod='POST'
								 data-backendrequest='statustoggle'
								 data-databasedir='tbl_Colors'
								 data-databaseparam='color_id'
								 data-tableid='#tblviewcolors'
								 data-tablebackendurl='backend/bk_colormanagement.php'
								 data-tablerequest='viewcolors'
								 ></i></td>";
				}

				else if (htmlspecialchars($colors["IsActive"]) == 1) {
					echo "<td><i class='fa-solid fa-toggle-off fa-2x text-danger'
								 data-statuscontent='". htmlspecialchars($colors["IsActive"]) ."'
								 data-statusdata='" . htmlspecialchars($colors["color_id"]) . "'
								 id='statustrigger'
								 data-backendurl='backend/bk_statustrigger.php'
								 data-backendmethod='POST'
								 data-backendrequest='statustoggle'
								 data-databasedir='tbl_Colors'
								 data-databaseparam='color_id'
								 data-tableid='#tblviewcolors'
								 data-tablebackendurl='backend/bk_colormanagement.php'
								 data-tablerequest='viewcolors'
								 ></i></td>";
				}

				echo "<td>
						<button type='button' 
								class='btn btn-warning m-1' 
								id='edit_data' 
								data-tooltip='View/Edit'
								data-openmodal='#addeditmodal'
								data-openmodallabel='Edit Color'
								data-openmodalbody='#addeditcontent'
								data-backendurl='backend/bk_colormanagement.php'
								data-backendrequest='editcolor'
								data-datavalue='" . htmlspecialchars($colors["color_id"]) . "'>
							<i class='fa-solid fa-pencil'></i>
						</button>
						
						<button type='button' 
								class='btn btn-danger m-1' 
								id='delete_data' 
								data-tooltip='Delete'
								data-openmodal='#addeditmodal'
								data-openmodallabel='Delete Color'
								data-openmodalbody='#addeditcontent'
								data-backendurl='backend/bk_colormanagement.php'
								data-backendrequest='deletecolor'
								data-datavalue='" . htmlspecialchars($colors["color_id"]) . "'>
							<i class='fa-solid fa-trash'></i>
					  </td>";
				echo "</tr>";
			}

	break;
	
	case "addcolor":

		echo '
		<div class="p-3">
		  <div class="form-group">
			<label for="">Color Description</label>
			<input type="text" class="form-control field-input" id="field1" placeholder="info/success...">
		  </div>

		  <div class="form-group">
			<label for="">Color Hex</label>
			<input type="text" class="form-control field-input" id="field2" placeholder="#000000...">
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
					data-backendrequest="savecolor"
					data-backendurl="backend/bk_colormanagement.php"
					data-tableid="#tblviewcolors"
					data-tablerequest="viewcolors"
					class="btn btn-success">Add Color</button>
		  </div>
		</div>';

	break;

	case "editcolor":

		$queryeditcolor = execsqlSRS("
			SELECT color_id, color_desc, color_hex, IsActive
			FROM tbl_Colors
			
			WHERE color_id = :datavalue", 
			"Select", [
						":datavalue" => $datavalue
					  ]); 

			foreach ($queryeditcolor as $edit) {

			echo "
				<div class='p-3'>
				<div class='form-group'>
					<label for=''>Color Description</label>
                    <input type='text' class='form-control field-input' id='field1' value='" . htmlspecialchars($edit["color_desc"]) . "'>
				  </div>";

			echo "<div class='form-group'>
					<label for=''>Color Hex</label>
                    <input type='text' class='form-control field-input' id='field2' value='" . htmlspecialchars($edit["color_hex"]) . "'>
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
						data-backendrequest='savecolor'
						data-backendurl='backend/bk_colormanagement.php'
						data-tableid='#tblviewcolors'
						data-tablerequest='viewcolors'
						data-datavalue='" . htmlspecialchars($edit["color_id"]) . "'>
						Save Changes
				</button>
              </div>
			  </div>";
			}

	break;
	
	case "deletecolor":
	
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
						data-backendrequest='savecolor'
						data-backendurl='backend/bk_colormanagement.php'
						data-logslocation='Color Management'
						data-tableid='#tblviewcolors'
						data-tablerequest='viewcolors'
						data-datavalue='" . htmlspecialchars($datavalue) . "'>
					Confirm Deletion
				</button>
				</div>
			</div>
	";

	break;

	case "savecolor":

	if ($operator == "delete") {
		
		$getolddata = execsqlSRS("
				SELECT *
				FROM tbl_Colors
				WHERE color_id = :datavalue", 
				"Select", [
							":datavalue" => $datavalue
						  ]); 
						  
		$string = implode (' | ', $getolddata[0]);

		$querysave = execsqlSRS("
				INSERT INTO tbl_Logs (logs_details, logs_dt, logs_location, logs_operation, logs_doneby, IsActive)
				VALUES (:string, :currentdt, :logslocation, :operator, :userid, '0')", 
				"Insert", [
							":string" => $string, 
							":currentdt" => $currentdt, 
							":logslocation" => $logslocation, 
							":operator" => $operator, 
							":userid" => $userid
						  ]); 
						  
		$querydelete = execsqlSRS("
				DELETE FROM tbl_Colors
				WHERE color_id = :datavalue", 
				"Delete", [
							":datavalue" => $datavalue
						  ]); 

		echo json_encode (['status' => 'success', 'message' => 'System: Macro has been Moved to the Logs.']);

		break;
	}

	if (!is_numeric($fields["field3"]) ){
		echo json_encode (['status' => 'error', 'message' => 'System: Status should either be 0 or 1.']);
		return;
	}

		if ($operator == "edit") {
			
			$querysave = execsqlSRS("
				UPDATE tbl_Colors
				SET color_desc = :desc, color_hex = :hex, IsActive = :status
				WHERE color_id = :datavalue", 
				"Update", [
							":desc" => $fields["field1"], 
							":hex" => $fields["field2"], 
							":status" => intval($fields["field3"]), 
							":datavalue" => intval($datavalue)
						  ]);

			echo json_encode (['status' => 'success', 'message' => 'System: Changes have been saved.']);
			
		}

		else if ($operator == "add") {

			$querysave = execsqlSRS("
					INSERT INTO tbl_Colors (color_desc, color_hex, IsActive)
					VALUES (:desc, :hex, :status)", 
					"Insert", [
								":desc" => $fields["field1"], 
								":hex" => $fields["field2"], 
								":status" => intval($fields["field3"])
							  ]); 

			echo json_encode (['status' => 'success', 'message' => 'System: Color Added!']);
		}

		else {
			echo json_encode (['status' => 'error', 'message' => 'System: Error Fetching. No Operator']);
			return;
		}

	break;
}
?>
