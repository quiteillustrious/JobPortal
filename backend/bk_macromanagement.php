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
	case "viewmacros":

		$queryviewmacros = execsqlSRS("
			SELECT m.macro_id, m.macro_title, m.macro_desc, c.color_desc, m.macro_icon, mm.Menu, m.IsActive
			FROM tbl_Macros m
			
			LEFT JOIN Sys_Menu mm
			ON mm.MenID = m.MenID
			
			LEFT JOIN tbl_Colors c
			ON c.color_id = m.color_id
			
			ORDER BY macro_id", 
			"Search",  
			array()
		);

			foreach ($queryviewmacros as $macros) {

				echo "<tr>";
				echo "<td>" . htmlspecialchars($macros["macro_id"]) . "</td>";
				echo "<td>" . htmlspecialchars($macros["macro_title"]) . "</td>";
				echo "<td>" . htmlspecialchars($macros["macro_desc"]) . "</td>";
				echo "<td class='bg-".htmlspecialchars($macros["color_desc"])."'>" . htmlspecialchars($macros["color_desc"]) . "</td>";
				echo "<td><i class='" . htmlspecialchars($macros["macro_icon"]) . " fa-2x text-".htmlspecialchars($macros["color_desc"])."'></i></td>";
				echo "<td>" . htmlspecialchars($macros["Menu"]) . "</td>";

				if (htmlspecialchars($macros["IsActive"]) == 0) {
					echo "<td><i class='fa-solid fa-toggle-on fa-2x text-success'
								 data-statuscontent='". htmlspecialchars($macros["IsActive"]) ."'
								 data-statusdata='" . htmlspecialchars($macros["macro_id"]) . "'
								 id='statustrigger'
								 data-backendurl='backend/bk_statustrigger.php'
								 data-backendmethod='POST'
								 data-backendrequest='statustoggle'
								 data-databasedir='tbl_Macros'
								 data-databaseparam='macro_id'
								 data-tableid='#tblviewmacros'
								 data-tablebackendurl='backend/bk_macromanagement.php'
								 data-tablerequest='viewmacros'
								 ></i></td>";
				}

				else if (htmlspecialchars($macros["IsActive"]) == 1) {
					echo "<td><i class='fa-solid fa-toggle-off fa-2x text-danger'
								 data-statuscontent='". htmlspecialchars($macros["IsActive"]) ."'
								 data-statusdata='" . htmlspecialchars($macros["macro_id"]) . "'
								 id='statustrigger'
								 data-backendurl='backend/bk_statustrigger.php'
								 data-backendmethod='POST'
								 data-backendrequest='statustoggle'
								 data-databasedir='tbl_Macros'
								 data-databaseparam='macro_id'
								 data-tableid='#tblviewmacros'
								 data-tablebackendurl='backend/bk_macromanagement.php'
								 data-tablerequest='viewmacros'
								 ></i></td>";
				}

				echo "<td>
						<button type='button' 
								class='btn btn-warning m-1' 
								id='edit_data' 
								data-tooltip='View/Edit'
								data-openmodal='#addeditmodal'
								data-openmodallabel='Edit Macro'
								data-openmodalbody='#addeditcontent'
								data-backendurl='backend/bk_macromanagement.php'
								data-backendrequest='editmacro'
								data-datavalue='" . htmlspecialchars($macros["macro_id"]) . "'>
							<i class='fa-solid fa-pencil'></i>
						</button>
						
						<button type='button' 
								class='btn btn-danger m-1' 
								id='delete_data' 
								data-tooltip='Delete'
								data-openmodal='#addeditmodal'
								data-openmodallabel='Delete Macro'
								data-openmodalbody='#addeditcontent'
								data-backendurl='backend/bk_macromanagement.php'
								data-backendrequest='deletemacro'
								data-datavalue='" . htmlspecialchars($macros["macro_id"]) . "'>
							<i class='fa-solid fa-trash'></i>
					  </td>";
				echo "</tr>";
			}

	break;
	
	case "addmacro":

		echo '
		<div class="p-3">
		  <div class="form-group">
			<label for="">Title</label>
			<input type="text" class="form-control field-input" id="field1" placeholder="Title...">
		  </div>

		  <div class="form-group">
			<label for="">Description</label>
			<textarea class="form-control field-input" id="field2" placeholder="Description..."></textarea>
		  </div>

		  <div class="dropdown">
			<label for="" class="pr-2">Color:</label>
			<select class="form-select form-control field-input" aria-label="Default select example" id="field3">
			<option value="">Select Color...</option>
			<hr>';

			$colors = execsqlSRS("
			  SELECT color_id, color_desc
			  FROM tbl_Colors
			", "Select", array());

			foreach ($colors as $color) {
			  $id = htmlspecialchars($color['color_id']);
			  $desc = htmlspecialchars($color['color_desc']);

				echo "<option class='bg-$desc' value='$id'>$desc</option>";
			}

		echo '
			</select>
		  </div>

		  <div class="form-group mt-3">
			<label for="">Icon</label>
			<input type="text" class="form-control field-input" id="field4" placeholder="example: fa-solid fa-circle-question...">
		  </div>

		  <div class="dropdown">
			<label for="" class="pr-2">Target:</label>
			<select class="form-select form-control field-input" aria-label="Default select example" id="field5">
			<option value="">Select Target...</option>
			<hr>';

			$targets = execsqlSRS("
			  SELECT MenID, Menu, IsActive
			  FROM Sys_Menu
			", "Select", array());

			foreach ($targets as $target) {
			  $id = htmlspecialchars($target['MenID']);
			  $desc = htmlspecialchars($target['Menu']);
			  $status = htmlspecialchars($target['IsActive']);
			  
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

		  <div class="form-group">
			<label for="">Status</label>
			<input type="text" class="form-control field-input" id="field6" placeholder="Status...">
		  </div>

		  <div class="form-group pt-2 d-flex justify-content-center">
			<button type="submit" 
					id="save_data"
					data-openmodal="#addeditmodal"
					data-operator="add"
					data-backendrequest="savemacro"
					data-backendurl="backend/bk_macromanagement.php"
					data-tableid="#tblviewmacros"
					data-tablerequest="viewmacros"
					class="btn btn-success">Add Macro</button>
		  </div>
		</div>';

	break;

	case "editmacro":

		$queryeditmacro = execsqlSRS("
			SELECT m.macro_id, m.macro_title, m.macro_desc, c.color_id, c.color_desc, m.macro_icon, mm.MenID, mm.Menu, m.IsActive
			FROM tbl_Macros m

			LEFT JOIN Sys_Menu mm
			ON mm.MenID = m.MenID

			LEFT JOIN tbl_Colors c
			ON c.color_id = m.color_id
			
			WHERE macro_id = :datavalue", 
			"Select", [
						":datavalue" => $datavalue
					  ]); 

			foreach ($queryeditmacro as $edit) {

			echo "
				<div class='p-3'>
				<div class='form-group'>
					<label for=''>Title</label>
                    <input type='text' class='form-control field-input' id='field1' value='" . htmlspecialchars($edit["macro_title"]) . "'>
				  </div>";

			echo "<div class='form-group'>
					<label for=''>Description</label>
                    <input type='text' class='form-control field-input' id='field2' value='" . htmlspecialchars($edit["macro_desc"]) . "'>
				  </div>";

		  echo "<div class='dropdown'>
			<label for='' class='pr-2'>Color:</label>
			<select class='form-select form-control field-input' aria-label='Default select example' id='field3'>
			<option value='".htmlspecialchars($edit["color_id"])."'>".htmlspecialchars($edit["color_desc"])."</option>
			<hr>";

			$colors = execsqlSRS('
			  SELECT color_id, color_desc, IsActive
			  FROM tbl_Colors
			', 'Select', array());

			foreach ($colors as $color) {
			  $id = htmlspecialchars($color["color_id"]);
			  $desc = htmlspecialchars($color["color_desc"]);
			  $status = htmlspecialchars($color["IsActive"]);
			  
			  if ($status == 0) {
				echo '<option value="' . $id . '">' . $desc . '</option>';
			  }
			  else if ($status == 1) {
				echo '<option class="bg-danger" value="' . $id . '">' . $desc . '</option>';
			  }
			}

		echo "
			</select>
		  </div>";
				  
			echo "<div class='form-group mt-3'>
					<label for=''>Icon</label>
                    <input type='text' class='form-control field-input' id='field4' value='" . htmlspecialchars($edit["macro_icon"]) . "'>
				  </div>";

		  echo "<div class='dropdown'>
			<label for='' class='pr-2'>Target:</label>
			<select class='form-select form-control field-input' aria-label='Default select example' id='field5'>
			<option value='".htmlspecialchars($edit["MenID"])."'>".htmlspecialchars($edit["Menu"])."</option>
			<hr>";

			$targets = execsqlSRS('
			  SELECT MenID, Menu, IsActive
			  FROM Sys_Menu
			', 'Select', array());

			foreach ($targets as $target) {
			  $id = htmlspecialchars($target["MenID"]);
			  $desc = htmlspecialchars($target["Menu"]);
			  $status = htmlspecialchars($target["IsActive"]);
			  
			  if ($status == 0) {
				echo '<option value="' . $id . '">' . $desc . '</option>';
			  }
			  else if ($status == 1) {
				echo '<option class="bg-danger" value="' . $id . '">' . $desc . '</option>';
			  }
			}

		echo "
			</select>
		  </div>";
				  
			echo "<div class='form-group'>
					<label for=''>Status</label>
                    <input type='text' class='form-control field-input' id='field6' value='" . htmlspecialchars($edit["IsActive"]) . "'>
				  </div>";
				  

				echo    "<div class='form-group d-flex justify-content-center pt-2'>
				<button 
						type='submit' 
						class='btn btn-success' 
						id='save_data'
						data-openmodal='#addeditmodal'
						data-operator='edit'
						data-backendrequest='savemacro'
						data-backendurl='backend/bk_macromanagement.php'
						data-tableid='#tblviewmacros'
						data-tablerequest='viewmacros'
						data-datavalue='" . htmlspecialchars($edit["macro_id"]) . "'>
						Save Changes
				</button>
              </div>
			  </div>";
			}

	break;
	
	case "deletemacro":
	
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
						data-backendrequest='savemacro'
						data-backendurl='backend/bk_macromanagement.php'
						data-logslocation='Macro Management'
						data-tableid='#tblviewmacros'
						data-tablerequest='viewmacros'
						data-datavalue='" . htmlspecialchars($datavalue) . "'>
					Confirm Deletion
				</button>
				</div>
			</div>
	";

	break;

	case "savemacro":

	if ($operator == "delete") {
		
		$getolddata = execsqlSRS("
				SELECT *
				FROM tbl_Macros
				WHERE macro_id = :datavalue", 
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
				DELETE FROM tbl_Macros
				WHERE macro_id = :datavalue", 
				"Delete", [
							":datavalue" => $datavalue
						  ]); 

		echo json_encode (['status' => 'success', 'message' => 'System: Macro has been Moved to the Logs.']);

		break;
	}

	if ( empty($fields["field1"]) || empty($fields["field2"]) || empty($fields["field3"]) || empty($fields["field4"]) || empty($fields["field5"]) ){
		echo json_encode (['status' => 'error', 'message' => 'System: Some Fields are empty.']);
		return;
	}

	if (!is_numeric($fields["field6"]) ){
		echo json_encode (['status' => 'error', 'message' => 'System: Status should either be 0 or 1.']);
		return;
	}

		if ($operator == "edit") {
			
			$querysave = execsqlSRS("
				UPDATE tbl_Macros
				SET macro_title = :title, macro_desc = :desc, color_id = :color, macro_icon = :icon, MenID = :target, IsActive = :status
				WHERE macro_id = :datavalue", 
				"Update", [
							":title" => $fields["field1"], 
							":desc" => $fields["field2"], 
							":color" => intval($fields["field3"]), 
							":icon" => $fields["field4"], 
							":target" => intval($fields["field5"]), 
							":status" => intval($fields["field6"]), 
							":datavalue" => intval($datavalue)
						  ]);

			echo json_encode (['status' => 'success', 'message' => 'System: Changes have been saved.']);
			
		}

		else if ($operator == "add") {

			$querysave = execsqlSRS("
					INSERT INTO tbl_Macros (macro_title, macro_desc, color_id, macro_icon, MenID, IsActive)
					VALUES (:title, :desc, :color, :icon, :target, :status)", 
					"Insert", [
								":title" => $fields["field1"], 
								":desc" => $fields["field2"], 
								":color" => intval($fields["field3"]), 
								":icon" => $fields["field4"], 
								":target" => intval($fields["field5"]), 
								":status" => intval($fields["field6"]), 
							  ]); 

			echo json_encode (['status' => 'success', 'message' => 'System: Macro Added!']);
		}

		else {
			echo json_encode (['status' => 'error', 'message' => 'System: Error Fetching. No Operator']);
			return;
		}

	break;
}
?>
